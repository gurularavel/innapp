<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name'      => 'Admin',
            'surname'   => 'Root',
            'email'     => 'admin@test.local',
            'password'  => 'secret123',
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_contact_form_stores_an_inquiry_and_redirects_to_its_anchor(): void
    {
        $response = $this->from('/')->post(route('inquiry.store'), [
            'type'    => 'contact',
            'name'    => 'Aygün Məmmədova',
            'email'   => 'aygun@example.com',
            'phone'   => '055 123 45 67',
            'message' => 'Kosmetologiya salonu üçün təqdimat istəyirik.',
        ]);

        $response->assertRedirect(route('home') . '#contact');
        $response->assertSessionHas('inquiry_sent', 'contact');

        $this->assertDatabaseHas('inquiries', [
            'type'   => 'contact',
            'name'   => 'Aygün Məmmədova',
            'email'  => 'aygun@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_needs_at_least_an_email_or_a_phone(): void
    {
        $response = $this->from('/')->post(route('inquiry.store'), [
            'type' => 'contact',
            'name' => 'Ad Soyad',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrorsIn('contact', ['email', 'phone']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_demo_form_only_needs_an_email_and_uses_its_own_error_bag(): void
    {
        $this->from('/')->post(route('inquiry.store'), ['type' => 'demo', 'email' => 'not-an-email'])
            ->assertSessionHasErrorsIn('demo', ['email'])
            ->assertSessionDoesntHaveErrors(['email'], null, 'contact');

        $this->post(route('inquiry.store'), ['type' => 'demo', 'email' => 'demo@example.com'])
            ->assertRedirect(route('home') . '#demo');

        $this->assertDatabaseHas('inquiries', ['type' => 'demo', 'email' => 'demo@example.com', 'name' => null]);
    }

    public function test_honeypot_rejects_bots(): void
    {
        $this->from('/')->post(route('inquiry.store'), [
            'type'    => 'contact',
            'name'    => 'Bot',
            'email'   => 'bot@example.com',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrorsIn('contact', ['website']);

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_admin_sees_inquiries_and_opening_one_marks_it_read(): void
    {
        $inquiry = Inquiry::create([
            'type'    => 'contact',
            'name'    => 'Rəşad Quliyev',
            'phone'   => '0501234567',
            'message' => 'Zəng edin.',
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.inquiries.index'))
            ->assertOk()
            ->assertSee('Rəşad Quliyev');

        $this->actingAs($admin)
            ->get(route('admin.inquiries.show', $inquiry))
            ->assertOk()
            ->assertSee('https://wa.me/994501234567', false);

        $this->assertNotNull($inquiry->fresh()->read_at);
    }

    public function test_admin_can_update_status_and_note_and_delete(): void
    {
        $admin = $this->admin();
        $inquiry = Inquiry::create(['type' => 'demo', 'email' => 'x@example.com']);

        $this->actingAs($admin)
            ->put(route('admin.inquiries.update', $inquiry), ['status' => 'done', 'admin_note' => 'Demo keçirildi'])
            ->assertRedirect(route('admin.inquiries.show', $inquiry));

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'done', 'admin_note' => 'Demo keçirildi']);

        $this->actingAs($admin)
            ->delete(route('admin.inquiries.destroy', $inquiry))
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    public function test_clinic_members_cannot_open_the_admin_inquiry_list(): void
    {
        $owner = User::create([
            'name' => 'O', 'surname' => 'W', 'email' => 'owner@test.local',
            'password' => 'secret123', 'role' => 'owner', 'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get(route('admin.inquiries.index'));

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertDatabaseCount('inquiries', 0);
    }
}
