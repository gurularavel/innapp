<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicSeatTest extends TestCase
{
    use RefreshDatabase;

    private function package(float $perSeat = 20.00): Package
    {
        return Package::create([
            'name'           => 'Standart',
            'price_per_seat' => $perSeat,
            'min_seats'      => 1,
            'max_seats'      => null,
            'patient_limit'  => null,
            'duration_days'  => 30,
            'is_active'      => true,
        ]);
    }

    private function clinic(string $name = 'Test Klinika'): Clinic
    {
        return Clinic::create(['name' => $name, 'is_active' => true]);
    }

    private function member(Clinic $clinic, string $role = 'doctor', array $overrides = []): User
    {
        static $n = 0;
        $n++;

        $user = User::create(array_merge([
            'clinic_id'          => $clinic->id,
            'name'               => 'Ad' . $n,
            'surname'            => 'Soyad' . $n,
            'email'              => "user{$n}@test.local",
            'password'           => 'secret123',
            'role'               => $role,
            'takes_appointments' => $role !== 'receptionist',
            'is_active'          => true,
        ], $overrides));

        if ($role === 'owner' && ! $clinic->owner_id) {
            $clinic->update(['owner_id' => $user->id]);
        }

        return $user;
    }

    private function subscribe(Clinic $clinic, int $seats, ?Package $package = null): DoctorSubscription
    {
        $package ??= $this->package();

        return DoctorSubscription::create([
            'clinic_id'      => $clinic->id,
            'package_id'     => $package->id,
            'seats'          => $seats,
            'price_per_seat' => $package->price_per_seat,
            'starts_at'      => now()->toDateString(),
            'expires_at'     => now()->addDays(30)->toDateString(),
            'patients_used'  => 0,
            'is_active'      => true,
        ]);
    }

    // ---------------------------------------------------------------------
    // Pricing
    // ---------------------------------------------------------------------

    public function test_one_staff_member_costs_twenty(): void
    {
        $this->assertSame(20.00, $this->package()->priceFor(1));
    }

    public function test_price_scales_linearly_with_staff_count(): void
    {
        $package = $this->package();

        $this->assertSame(60.00, $package->priceFor(3));
        $this->assertSame(200.00, $package->priceFor(10));
    }

    public function test_price_never_falls_below_the_minimum_seats(): void
    {
        $package = $this->package();
        $package->update(['min_seats' => 2]);

        $this->assertSame(40.00, $package->priceFor(1));
    }

    public function test_subscription_total_reflects_seats(): void
    {
        $clinic = $this->clinic();
        $sub    = $this->subscribe($clinic, 4);

        $this->assertSame(80.00, $sub->total_price);
    }

    // ---------------------------------------------------------------------
    // Seats
    // ---------------------------------------------------------------------

    public function test_every_account_counts_as_a_seat(): void
    {
        $clinic = $this->clinic();
        $this->member($clinic, 'owner');
        $this->member($clinic, 'doctor');
        $this->member($clinic, 'receptionist');

        // Owner and receptionist take no appointments but still occupy a seat.
        $this->assertSame(3, $clinic->usedSeats());
    }

    public function test_deactivated_accounts_free_their_seat(): void
    {
        $clinic = $this->clinic();
        $this->member($clinic, 'owner');
        $extra = $this->member($clinic, 'doctor');

        $this->assertSame(2, $clinic->usedSeats());

        $extra->update(['is_active' => false]);

        $this->assertSame(1, $clinic->fresh()->usedSeats());
    }

    public function test_clinic_cannot_add_member_without_a_free_seat(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $this->subscribe($clinic, 1);

        $this->assertFalse($clinic->fresh()->canAddMember());

        $this->actingAs($owner)
            ->get(route('panel.staff.create'))
            ->assertRedirect(route('panel.staff.index'))
            ->assertSessionHas('error');
    }

    public function test_owner_can_add_member_when_a_seat_is_paid_for(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $this->subscribe($clinic, 3);

        $this->actingAs($owner)
            ->post(route('panel.staff.store'), [
                'name'     => 'Yeni',
                'surname'  => 'Mütəxəssis',
                'email'    => 'yeni@test.local',
                'role'     => 'doctor',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'takes_appointments' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('panel.staff.index'));

        $this->assertSame(2, $clinic->fresh()->usedSeats());
    }

    public function test_receptionist_never_gets_a_calendar(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $this->subscribe($clinic, 5);

        $this->actingAs($owner)->post(route('panel.staff.store'), [
            'name'     => 'Resep',
            'surname'  => 'Siyonist',
            'email'    => 'resep@test.local',
            'role'     => 'receptionist',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'takes_appointments' => '1', // ignored on purpose
        ])->assertSessionHasNoErrors();

        $this->assertFalse(User::where('email', 'resep@test.local')->first()->takes_appointments);
    }

    public function test_only_the_owner_manages_staff(): void
    {
        $clinic = $this->clinic();
        $this->member($clinic, 'owner');
        $doctor = $this->member($clinic, 'doctor');
        $this->subscribe($clinic, 3);

        $this->actingAs($doctor)->get(route('panel.staff.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // Tenant isolation and shared data
    // ---------------------------------------------------------------------

    public function test_patient_base_is_shared_inside_the_clinic(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $other  = $this->member($clinic, 'doctor');

        Patient::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $owner->id,
            'name'      => 'Ortaq',
            'surname'   => 'Müştəri',
            'phone'     => '0551112233',
        ]);

        // Registered by the owner, still visible to a colleague.
        $this->assertSame(1, $other->patients()->count());
    }

    public function test_another_clinic_cannot_see_the_patients(): void
    {
        $clinicA = $this->clinic('A');
        $ownerA  = $this->member($clinicA, 'owner');

        Patient::create([
            'clinic_id' => $clinicA->id,
            'doctor_id' => $ownerA->id,
            'name'      => 'Gizli',
            'surname'   => 'Müştəri',
            'phone'     => '0554445566',
        ]);

        $clinicB = $this->clinic('B');
        $ownerB  = $this->member($clinicB, 'owner');

        $this->assertSame(0, $ownerB->patients()->count());
    }

    public function test_specialist_only_sees_their_own_appointments(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $doctor = $this->member($clinic, 'doctor');

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $owner->id,
            'name'      => 'Test',
            'surname'   => 'Müştəri',
            'phone'     => '0557778899',
        ]);

        foreach ([$owner, $doctor] as $staff) {
            Appointment::create([
                'clinic_id'        => $clinic->id,
                'doctor_id'        => $staff->id,
                'patient_id'       => $patient->id,
                'scheduled_at'     => now()->addDay(),
                'duration_minutes' => 30,
                'status'           => 'confirmed',
            ]);
        }

        $this->assertSame(1, $doctor->appointments()->count());
        $this->assertSame(2, $doctor->clinicAppointments()->count());
    }

    // ---------------------------------------------------------------------
    // Registration
    // ---------------------------------------------------------------------

    public function test_registration_creates_a_one_person_clinic(): void
    {
        $this->post(route('register'), [
            'name'     => 'Tək',
            'surname'  => 'Mütəxəssis',
            'email'    => 'solo@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms'    => '1',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'solo@test.local')->first();

        $this->assertNotNull($user->clinic_id);
        $this->assertSame('owner', $user->role);
        $this->assertSame('Tək Mütəxəssis', $user->clinic->name);
        $this->assertSame($user->id, $user->clinic->owner_id);
        $this->assertSame(1, $user->clinic->usedSeats());
    }

    public function test_registration_as_a_clinic_uses_the_clinic_name(): void
    {
        $this->post(route('register'), [
            'name'         => 'Sahib',
            'surname'      => 'Adı',
            'email'        => 'clinic@test.local',
            'password'     => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'clinic',
            'clinic_name'  => 'Şəfa Klinikası',
            'terms'        => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            'Şəfa Klinikası',
            User::where('email', 'clinic@test.local')->first()->clinic->name
        );
    }

    // ---------------------------------------------------------------------
    // Subscription guard
    // ---------------------------------------------------------------------

    public function test_panel_is_blocked_when_staff_exceed_paid_seats(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $this->member($clinic, 'doctor');
        $this->subscribe($clinic, 1); // two accounts, one paid seat

        $this->actingAs($owner)
            ->get(route('panel.patients.index'))
            ->assertRedirect(route('panel.subscription.index'))
            ->assertSessionHas('warning');
    }

    public function test_panel_is_open_when_seats_cover_the_staff(): void
    {
        $clinic = $this->clinic();
        $owner  = $this->member($clinic, 'owner');
        $this->member($clinic, 'doctor');
        $this->subscribe($clinic, 2);

        $this->actingAs($owner)->get(route('panel.patients.index'))->assertOk();
    }
}
