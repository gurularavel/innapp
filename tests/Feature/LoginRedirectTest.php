<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A stale "intended" URL left behind by another account (e.g. a promoter
 * logged out, the browser re-opened /promoter/... and the guest redirect
 * remembered it) must never send the next login into a foreign panel.
 */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, ?Clinic $clinic = null): User
    {
        static $n = 0;
        $n++;

        return User::create([
            'clinic_id' => $clinic?->id,
            'name'      => 'Ad' . $n,
            'surname'   => 'Soyad' . $n,
            'email'     => "user{$n}@test.local",
            'password'  => 'secret123',
            'role'      => $role,
            'is_active' => true,
        ]);
    }

    private function clinicMember(string $role = 'doctor'): User
    {
        $clinic = Clinic::create(['name' => 'Test', 'is_active' => true]);

        return $this->user($role, $clinic);
    }

    public function test_guest_visit_to_promoter_page_does_not_hijack_a_clinic_members_login(): void
    {
        $doctor = $this->clinicMember();

        // Guest hits a promoter URL → auth middleware stores it as intended.
        $this->get('/promoter/dashboard')->assertRedirect(route('login'));
        $this->assertSame(url('/promoter/dashboard'), session('url.intended'));

        $this->post('/login', ['email' => $doctor->email, 'password' => 'secret123'])
            ->assertRedirect(route('panel.dashboard'));

        $this->assertNull(session('url.intended'));
    }

    public function test_intended_url_inside_own_panel_is_still_honoured(): void
    {
        $doctor = $this->clinicMember();

        $this->get('/panel/calendar')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $doctor->email, 'password' => 'secret123'])
            ->assertRedirect(url('/panel/calendar'));
    }

    public function test_promoter_logs_in_to_promoter_panel_regardless_of_stale_panel_url(): void
    {
        $promoter = $this->user('promoter');

        $this->get('/panel/dashboard')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $promoter->email, 'password' => 'secret123'])
            ->assertRedirect(route('promoter.dashboard'));
    }

    public function test_signed_in_user_opening_a_foreign_panel_is_sent_home(): void
    {
        $doctor = $this->clinicMember();

        $this->actingAs($doctor)->get('/promoter/dashboard')
            ->assertRedirect(route('panel.dashboard'))
            ->assertSessionHas('warning');
    }
}
