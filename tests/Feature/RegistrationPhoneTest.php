<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\AzMobilePhone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationPhoneTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Ad',
            'surname'               => 'Soyad',
            'email'                 => 'new@test.local',
            'phone'                 => '+994 55 123 45 67',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'terms'                 => '1',
        ], $overrides);
    }

    public function test_phone_is_required_on_registration(): void
    {
        $this->post(route('register'), $this->payload(['phone' => '']))
            ->assertSessionHasErrors(['phone' => 'Mobil nömrə vacibdir.']);

        $this->assertNull(User::where('email', 'new@test.local')->first());
    }

    public function test_phone_with_letters_is_rejected(): void
    {
        $this->post(route('register'), $this->payload(['phone' => '+994 55 abc 45 67']))
            ->assertSessionHasErrors('phone');
    }

    public function test_incomplete_masked_phone_is_rejected(): void
    {
        // What the mask holds when the user stops typing halfway.
        $this->post(route('register'), $this->payload(['phone' => '+994 55 12_ __ __']))
            ->assertSessionHasErrors('phone');
    }

    public function test_landline_prefix_is_rejected(): void
    {
        $this->post(route('register'), $this->payload(['phone' => '+994 12 123 45 67']))
            ->assertSessionHasErrors('phone');
    }

    public function test_phone_is_stored_in_canonical_format(): void
    {
        foreach (['0501234567', '501234567', '994501234567', '+994 50 123 45 67', '+994501234567'] as $i => $input) {
            $email = "u{$i}@test.local";

            $this->post(route('register'), $this->payload(['phone' => $input, 'email' => $email]))
                ->assertSessionHasNoErrors();

            $this->assertSame('+994 50 123 45 67', User::where('email', $email)->value('phone'), "input: {$input}");
            auth()->logout();
        }
    }

    public function test_promoter_registration_requires_a_valid_mobile(): void
    {
        $this->post(route('promoter.register'), $this->payload(['phone' => '']))
            ->assertSessionHasErrors(['phone' => 'Mobil nömrə vacibdir.']);

        $this->post(route('promoter.register'), $this->payload(['phone' => '55x1234567']))
            ->assertSessionHasErrors('phone');

        $this->post(route('promoter.register'), $this->payload(['phone' => '0771234567']))
            ->assertSessionHasNoErrors();

        $this->assertSame('+994 77 123 45 67', User::where('email', 'new@test.local')->value('phone'));
    }

    public function test_register_form_has_masked_phone_input(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="phone"', false)
            ->assertSee('data-phone-mask', false)
            ->assertSee('imask', false);
    }

    public function test_rule_helpers(): void
    {
        $this->assertSame('551234567', AzMobilePhone::digits('+994 55 123 45 67'));
        $this->assertSame('551234567', AzMobilePhone::digits('0551234567'));
        $this->assertNull(AzMobilePhone::digits('55123456'));       // too short
        $this->assertNull(AzMobilePhone::digits('+994 55 123 45 6a'));
        $this->assertNull(AzMobilePhone::digits('+994 55 123 45 __'));
        $this->assertSame('+994 10 123 45 67', AzMobilePhone::format('101234567'));
    }
}
