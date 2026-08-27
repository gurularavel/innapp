<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientVisitTooth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentalChartTest extends TestCase
{
    use RefreshDatabase;

    private function clinic(bool $dentalChart = true): Clinic
    {
        return Clinic::create([
            'name'                 => 'Test Klinika',
            'is_active'            => true,
            'dental_chart_enabled' => $dentalChart,
        ]);
    }

    private function owner(Clinic $clinic): User
    {
        static $n = 0;
        $n++;

        $user = User::create([
            'clinic_id'          => $clinic->id,
            'name'               => 'Sahib',
            'surname'            => 'Test',
            'email'              => "owner{$n}@test.local",
            'password'           => 'secret123',
            'role'               => 'owner',
            'takes_appointments' => true,
            'is_active'          => true,
        ]);

        $clinic->update(['owner_id' => $user->id]);

        return $user;
    }

    private function patient(Clinic $clinic, User $doctor): Patient
    {
        return Patient::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'name'      => 'Xəstə',
            'surname'   => 'Testov',
            'phone'     => '0551234567',
        ]);
    }

    public function test_visit_form_shows_the_chart_only_when_the_clinic_enabled_it(): void
    {
        $clinic  = $this->clinic(true);
        $owner   = $this->owner($clinic);
        $patient = $this->patient($clinic, $owner);

        $this->actingAs($owner)
            ->get(route('panel.patients.visits.create', $patient))
            ->assertOk()
            ->assertSee('Diş sxemi')
            ->assertSee('data-odo-tooth="18"', false);

        $clinic->update(['dental_chart_enabled' => false]);

        // actingAs keeps the same user instance between requests, so drop the
        // clinic it already loaded before asking again.
        $this->actingAs($owner->fresh())
            ->get(route('panel.patients.visits.create', $patient))
            ->assertOk()
            ->assertDontSee('data-odo-tooth', false);
    }

    public function test_storing_a_visit_records_the_marked_teeth(): void
    {
        $clinic  = $this->clinic();
        $owner   = $this->owner($clinic);
        $patient = $this->patient($clinic, $owner);

        $this->actingAs($owner)
            ->post(route('panel.patients.visits.store', $patient), [
                'visited_at' => now()->format('Y-m-d\TH:i'),
                'title'      => 'Müalicə',
                'teeth'      => [
                    16 => ['status' => 'caries', 'note' => 'dərin karies'],
                    36 => ['status' => 'filling', 'note' => ''],
                    // not on the chart — must be dropped rather than stored
                    99 => ['status' => 'caries', 'note' => ''],
                ],
            ])
            ->assertRedirect();

        $visit = PatientVisit::firstOrFail();

        $this->assertSame([16, 36], $visit->teeth->pluck('tooth_number')->all());
        $this->assertSame('caries', $visit->teeth->firstWhere('tooth_number', 16)->status);
        $this->assertSame('dərin karies', $visit->teeth->firstWhere('tooth_number', 16)->note);
        $this->assertNull($visit->teeth->firstWhere('tooth_number', 36)->note);
    }

    public function test_updating_a_visit_replaces_its_teeth(): void
    {
        $clinic  = $this->clinic();
        $owner   = $this->owner($clinic);
        $patient = $this->patient($clinic, $owner);

        $visit = PatientVisit::create([
            'clinic_id'  => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id'  => $owner->id,
            'visited_at' => now(),
        ]);

        PatientVisitTooth::create([
            'patient_visit_id' => $visit->id,
            'tooth_number'     => 16,
            'status'           => 'caries',
        ]);

        $this->actingAs($owner)
            ->patch(route('panel.patients.visits.update', [$patient, $visit]), [
                'visited_at' => now()->format('Y-m-d\TH:i'),
                'teeth'      => [
                    16 => ['status' => 'filling', 'note' => ''],
                    26 => ['status' => 'extraction', 'note' => ''],
                ],
            ])
            ->assertRedirect();

        $teeth = $visit->fresh()->teeth;

        $this->assertSame([16, 26], $teeth->pluck('tooth_number')->all());
        $this->assertSame('filling', $teeth->firstWhere('tooth_number', 16)->status);
    }

    public function test_posting_an_unknown_status_is_rejected(): void
    {
        $clinic  = $this->clinic();
        $owner   = $this->owner($clinic);
        $patient = $this->patient($clinic, $owner);

        $this->actingAs($owner)
            ->post(route('panel.patients.visits.store', $patient), [
                'visited_at' => now()->format('Y-m-d\TH:i'),
                'teeth'      => [16 => ['status' => 'lazer', 'note' => '']],
            ])
            ->assertSessionHasErrors('teeth.16.status');

        $this->assertSame(0, PatientVisitTooth::count());
    }

    public function test_deleting_a_visit_removes_its_teeth(): void
    {
        $clinic  = $this->clinic();
        $owner   = $this->owner($clinic);
        $patient = $this->patient($clinic, $owner);

        $visit = PatientVisit::create([
            'clinic_id'  => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id'  => $owner->id,
            'visited_at' => now(),
        ]);

        PatientVisitTooth::create([
            'patient_visit_id' => $visit->id,
            'tooth_number'     => 47,
            'status'           => 'root_canal',
        ]);

        $this->actingAs($owner)
            ->delete(route('panel.patients.visits.destroy', [$patient, $visit]))
            ->assertRedirect();

        $this->assertSame(0, PatientVisitTooth::count());
    }

    public function test_fdi_helpers_describe_each_tooth(): void
    {
        $this->assertTrue(PatientVisitTooth::isValidNumber(18));
        $this->assertTrue(PatientVisitTooth::isValidNumber(85));
        $this->assertFalse(PatientVisitTooth::isValidNumber(19));
        $this->assertFalse(PatientVisitTooth::isValidNumber(0));

        $this->assertSame('Yuxarı sağ', PatientVisitTooth::quadrantLabel(16));
        $this->assertSame('Aşağı sol', PatientVisitTooth::quadrantLabel(36));
        $this->assertSame('Yuxarı çənə', PatientVisitTooth::jawLabel(21));
        $this->assertSame('Aşağı çənə', PatientVisitTooth::jawLabel(41));

        $this->assertSame('molar', PatientVisitTooth::shape(17));
        $this->assertSame('premolar', PatientVisitTooth::shape(15));
        $this->assertSame('canine', PatientVisitTooth::shape(13));
        $this->assertSame('incisor', PatientVisitTooth::shape(11));
        // the primary set has no premolars — 54 is already a molar
        $this->assertSame('molar', PatientVisitTooth::shape(54));

        $this->assertCount(52, PatientVisitTooth::allNumbers());
    }
}
