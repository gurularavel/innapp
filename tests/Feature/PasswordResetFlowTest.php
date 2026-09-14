<?php

namespace Tests\Feature;

use App\Mail\SmtpTestMail;
use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * End-to-end password reset: request link → Azerbaijani e-mail → set new
 * password → log in with it. Plus the admin's SMTP "send test mail" button.
 */
class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'owner', array $overrides = []): User
    {
        static $n = 0;
        $n++;

        $clinic = $role === 'super_admin' || $role === 'promoter'
            ? null
            : Clinic::create(['name' => 'Test', 'is_active' => true]);

        return User::create(array_merge([
            'clinic_id' => $clinic?->id,
            'name'      => 'Ad' . $n,
            'surname'   => 'Soyad' . $n,
            'email'     => "user{$n}@test.local",
            'password'  => 'old-secret',
            'role'      => $role,
            'is_active' => true,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // Request link
    // ---------------------------------------------------------------------

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Sıfırlama linki göndər');
    }

    public function test_reset_link_is_sent_with_azerbaijani_mail(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'Şifrə sıfırlama linki e-poçt ünvanınıza göndərildi. Gələn qutunu (və spam qovluğunu) yoxlayın.');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $mail = $n->toMail($user);

            $this->assertSame('Şifrə sıfırlama — ' . config('app.name'), $mail->subject);
            $this->assertSame('emails.password-reset', $mail->view);
            $this->assertStringContainsString('/reset-password/' . $n->token, $mail->viewData['url']);
            $this->assertStringContainsString('email=' . urlencode($user->email), $mail->viewData['url']);

            // The template itself must render without error.
            $html = view($mail->view, $mail->viewData)->render();
            $this->assertStringContainsString('Şifrəni sıfırla', $html);
            $this->assertStringContainsString($mail->viewData['url'], $html);

            return true;
        });
    }

    public function test_unknown_email_gets_azerbaijani_error(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nobody@test.local'])
            ->assertSessionHasErrors(['email' => 'Bu e-poçt ünvanı ilə qeydiyyatdan keçmiş istifadəçi tapılmadı.']);

        Notification::assertNothingSent();
    }

    // ---------------------------------------------------------------------
    // Set new password
    // ---------------------------------------------------------------------

    public function test_password_can_be_reset_and_used_to_log_in(): void
    {
        $user  = $this->user();
        $token = Password::createToken($user);

        $this->get("/reset-password/{$token}?email={$user->email}")
            ->assertOk()
            ->assertSee('Yeni şifrə');

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Şifrəniz uğurla dəyişdirildi. İndi yeni şifrə ilə daxil ola bilərsiniz.');

        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));

        $this->post('/login', ['email' => $user->email, 'password' => 'new-secret-123'])
            ->assertRedirect(route('panel.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = $this->user();

        $this->post('/reset-password', [
            'token'                 => 'bogus',
            'email'                 => $user->email,
            'password'              => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertSessionHasErrors(['email' => 'Bu sıfırlama linki etibarsızdır və ya vaxtı bitib. Yenidən link tələb edin.']);

        $this->assertTrue(Hash::check('old-secret', $user->fresh()->password));
    }

    public function test_wrong_login_password_gets_azerbaijani_error(): void
    {
        $user = $this->user();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'E-poçt və ya şifrə yanlışdır.']);
    }

    // ---------------------------------------------------------------------
    // Admin » SMTP » test mail
    // ---------------------------------------------------------------------

    public function test_admin_can_send_a_test_mail(): void
    {
        Mail::fake();
        $admin = $this->user('super_admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.smtp.test'), ['to' => 'check@test.local'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(SmtpTestMail::class, fn (SmtpTestMail $mail) => $mail->hasTo('check@test.local')
            && $mail->hasSubject(config('app.name') . ' — SMTP test məktubu'));

        // And the message body actually renders.
        $this->assertStringContainsString('SMTP ayarlarının yoxlanması', (new SmtpTestMail)->render());
    }

    public function test_test_mail_failure_is_reported_not_thrown(): void
    {
        $admin = $this->user('super_admin');

        // Point the smtp mailer at a closed port so the transport really fails.
        config([
            'mail.default'            => 'smtp',
            'mail.mailers.smtp.host'  => '127.0.0.1',
            'mail.mailers.smtp.port'  => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.settings.smtp.test'), ['to' => 'check@test.local'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_smtp_page_shows_effective_mailer_and_test_form(): void
    {
        $admin = $this->user('super_admin');
        Setting::set('smtp_host', 'smtp.example.com');

        $this->actingAs($admin)
            ->get(route('admin.settings.smtp'))
            ->assertOk()
            ->assertSee('Test məktubu göndər')
            ->assertSee($admin->email);
    }

    public function test_non_admin_cannot_send_test_mail(): void
    {
        Mail::fake();
        $owner = $this->user('owner');

        $this->actingAs($owner)
            ->post(route('admin.settings.smtp.test'), ['to' => 'check@test.local'])
            ->assertForbidden();

        Mail::assertNothingSent();
    }
}
