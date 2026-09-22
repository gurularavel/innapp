<?php

namespace Tests\Feature;

use App\Http\Controllers\Doctor\PatientController;
use App\Models\Clinic;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientVisitFile;
use App\Models\SmsLog;
use App\Models\User;
use App\Rules\MapUrl;
use App\Services\SmsService;
use App\Support\PatientFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Regression tests for the security audit fixes: private patient files,
 * demo clinics never reaching a gateway, deactivated accounts being logged
 * out, and the map short link refusing to become an open redirector.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    private function clinicWithOwner(array $userOverrides = []): array
    {
        $this->n++;

        $clinic = Clinic::create(['name' => "Klinika {$this->n}", 'is_active' => true]);

        $owner = User::create(array_merge([
            'clinic_id'          => $clinic->id,
            'name'               => 'Sahib',
            'surname'            => "N{$this->n}",
            'email'              => "owner{$this->n}@test.local",
            'password'           => 'secret123',
            'role'               => 'owner',
            'takes_appointments' => true,
            'is_active'          => true,
        ], $userOverrides));

        $clinic->update(['owner_id' => $owner->id]);

        $package = Package::firstOrCreate(
            ['name' => 'Standart'],
            ['price_per_seat' => 20, 'min_seats' => 1, 'duration_days' => 30, 'is_active' => true]
        );

        DoctorSubscription::create([
            'clinic_id'      => $clinic->id,
            'doctor_id'      => $owner->id,
            'package_id'     => $package->id,
            'seats'          => 5,
            'price_per_seat' => 20,
            'starts_at'      => now()->subDay()->toDateString(),
            'expires_at'     => now()->addMonth()->toDateString(),
            'is_active'      => true,
        ]);

        return [$clinic, $owner];
    }

    private function patientFor(Clinic $clinic, User $doctor): Patient
    {
        return Patient::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'name'      => 'Xəstə',
            'surname'   => 'Test',
            'phone'     => '+994 55 000 00 0' . $this->n,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private patient files
    // -------------------------------------------------------------------------

    public function test_visit_file_goes_to_the_private_disk_and_is_served_only_to_the_owning_clinic(): void
    {
        Storage::fake(PatientFiles::DISK);
        Storage::fake(PatientFiles::LEGACY_DISK);

        [$clinic, $owner] = $this->clinicWithOwner();
        $patient = $this->patientFor($clinic, $owner);

        $this->actingAs($owner)
            ->post(route('panel.patients.visits.store', $patient), [
                'visited_at' => now()->toDateString(),
                'files'      => [UploadedFile::fake()->image('xray.jpg')],
            ])
            ->assertRedirect();

        $file = PatientVisitFile::firstOrFail();

        Storage::disk(PatientFiles::DISK)->assertExists($file->file_path);
        Storage::disk(PatientFiles::LEGACY_DISK)->assertMissing($file->file_path);
        $this->assertStringStartsWith('/panel/files/visits/', parse_url($file->url, PHP_URL_PATH));

        $this->actingAs($owner)->get($file->url)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        [, $stranger] = $this->clinicWithOwner();

        $this->flushSession();
        $this->actingAs($stranger)->get($file->url)->assertNotFound();

        $this->flushSession();
        auth()->logout();
        $this->get($file->url)->assertRedirect(route('login'));
    }

    public function test_custom_field_upload_rules_reject_scripts_and_markup(): void
    {
        // The sqlite test schema predates the `file` field type, so the rule set
        // is exercised directly — it is what the controller applies verbatim.
        // name => the type fileinfo sniffs from the real content (a fake file
        // would otherwise report the type its name suggests).
        $bad = [
            'shell.php' => 'text/x-php',
            'page.html' => 'text/html',
            'img.svg'   => 'image/svg+xml',
            'fake.pdf'  => 'text/x-php',   // a script renamed to .pdf
            'real.pdf'  => 'text/html',    // markup renamed to .pdf
        ];

        foreach ($bad as $name => $sniffed) {
            $validator = Validator::make(
                ['f' => UploadedFile::fake()->create($name, 1)->mimeType($sniffed)],
                ['f' => PatientController::CUSTOM_FILE_RULES]
            );

            $this->assertTrue($validator->fails(), "{$name} should be rejected");
        }

        // Right content, wrong extension is refused too (extensions rule).
        $renamed = Validator::make(
            ['f' => UploadedFile::fake()->create('scan.exe', 1)->mimeType('image/jpeg')],
            ['f' => PatientController::CUSTOM_FILE_RULES]
        );
        $this->assertTrue($renamed->fails());

        $ok = Validator::make(
            ['f' => UploadedFile::fake()->image('scan.jpg')],
            ['f' => PatientController::CUSTOM_FILE_RULES]
        );

        $this->assertFalse($ok->fails());
    }

    // -------------------------------------------------------------------------
    // Demo clinics never reach a gateway
    // -------------------------------------------------------------------------

    public function test_sms_for_a_demo_clinic_is_logged_but_never_sent(): void
    {
        config(['services.sms.driver' => 'poctgoyercini', 'services.sms.api_url' => 'https://sms.test']);
        Http::fake();

        [$clinic, $owner] = $this->clinicWithOwner(['is_demo' => true, 'demo_expires_at' => now()->addHour()]);

        // Resolve after the driver config so the service picks up the live driver.
        $sent = app(SmsService::class)->send('+994501234501', 'Salam', $owner->id, 'custom', null, $clinic->id);

        $this->assertTrue($sent);
        Http::assertNothingSent();
        $this->assertSame('log', SmsLog::firstOrFail()->response_body['driver']);
    }

    public function test_reminders_skip_demo_clinics(): void
    {
        [$clinic, $owner] = $this->clinicWithOwner(['is_demo' => true, 'demo_expires_at' => now()->addHour()]);
        $patient = $this->patientFor($clinic, $owner);

        \App\Models\Appointment::create([
            'clinic_id'        => $clinic->id,
            'doctor_id'        => $owner->id,
            'patient_id'       => $patient->id,
            'scheduled_at'     => now()->addMinutes(120),
            'duration_minutes' => 30,
            'status'           => 'confirmed',
            'reminder_sent'    => false,
        ]);

        $this->artisan('reminders:send')->assertSuccessful();

        $this->assertSame(0, SmsLog::count());
        $this->assertFalse(\App\Models\Appointment::firstOrFail()->reminder_sent);
    }

    // -------------------------------------------------------------------------
    // Deactivation ends the session
    // -------------------------------------------------------------------------

    public function test_a_deactivated_account_is_logged_out_on_its_next_request(): void
    {
        [, $owner] = $this->clinicWithOwner();

        $this->actingAs($owner)->get(route('panel.dashboard'))->assertOk();

        $owner->update(['is_active' => false]);

        $this->get(route('panel.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_a_closed_clinic_locks_its_members_out(): void
    {
        [$clinic, $owner] = $this->clinicWithOwner();

        $clinic->update(['is_active' => false]);

        $this->actingAs($owner)->get(route('panel.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_owner_deactivating_staff_revokes_their_remember_token(): void
    {
        [$clinic, $owner] = $this->clinicWithOwner();

        $staff = User::create([
            'clinic_id' => $clinic->id, 'name' => 'İşçi', 'surname' => 'Test',
            'email' => 'staff@test.local', 'password' => 'secret123',
            'role' => 'doctor', 'is_active' => true,
        ]);
        $staff->forceFill(['remember_token' => 'old-token'])->save();

        $this->actingAs($owner)->patch(route('panel.staff.toggle-status', $staff))->assertRedirect();

        $this->assertFalse($staff->fresh()->is_active);
        $this->assertNotSame('old-token', $staff->fresh()->remember_token);
    }

    // -------------------------------------------------------------------------
    // Map short link
    // -------------------------------------------------------------------------

    public function test_map_link_only_redirects_to_known_map_hosts(): void
    {
        $this->assertTrue(MapUrl::allowed('https://maps.app.goo.gl/abc123'));
        $this->assertTrue(MapUrl::allowed('https://www.google.com/maps/place/x'));
        $this->assertTrue(MapUrl::allowed('https://yandex.az/maps/-/CCU'));
        $this->assertFalse(MapUrl::allowed('http://maps.google.com/x'));
        $this->assertFalse(MapUrl::allowed('https://evil.example/login'));
        $this->assertFalse(MapUrl::allowed('https://google.com.evil.example/'));
        $this->assertFalse(MapUrl::allowed('javascript:alert(1)'));

        [$clinic] = $this->clinicWithOwner();
        $clinic->update(['map_code' => 'abc1234', 'map_url' => 'https://evil.example/phish']);

        $this->get('/map/abc1234')->assertNotFound();

        $clinic->update(['map_url' => 'https://maps.app.goo.gl/abc']);

        $this->get('/map/abc1234')->assertRedirect('https://maps.app.goo.gl/abc');
    }

    // -------------------------------------------------------------------------
    // Cross-tenant references
    // -------------------------------------------------------------------------

    public function test_appointment_cannot_reference_another_clinics_service(): void
    {
        [$clinic, $owner] = $this->clinicWithOwner();
        [$other, $otherOwner] = $this->clinicWithOwner();
        $patient = $this->patientFor($clinic, $owner);

        $foreign = \App\Models\TreatmentType::create([
            'clinic_id' => $other->id, 'doctor_id' => $otherOwner->id,
            'name' => 'Gizli xidmət', 'duration_minutes' => 30, 'color' => '#000000',
        ]);

        $this->actingAs($owner)
            ->post(route('panel.appointments.store'), [
                'patient_id'        => $patient->id,
                'treatment_type_id' => $foreign->id,
                'scheduled_at'      => now()->addDay()->format('Y-m-d H:i:s'),
                'duration_minutes'  => 30,
                'status'            => 'pending',
            ])
            ->assertSessionHasErrors('treatment_type_id');
    }

    public function test_demo_is_not_created_by_a_get_request(): void
    {
        $this->get('/demo')->assertOk();

        $this->assertSame(0, User::where('is_demo', true)->count());
        $this->assertGuest();
    }
}
