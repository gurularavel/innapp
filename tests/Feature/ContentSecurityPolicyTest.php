<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\TreatmentType;
use App\Models\User;
use App\Support\Csp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The policy is only worth having if every script the app ships carries the
 * nonce: one unmarked `<script>` and that page silently stops working the day
 * the policy is enforced. These sweep the real pages instead of trusting that
 * nobody forgot `@cspNonce`.
 */
class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $clinic = Clinic::create(['name' => 'Klinika', 'is_active' => true, 'dental_chart_enabled' => true]);

        $owner = User::create([
            'clinic_id'          => $clinic->id,
            'name'               => 'Sahib',
            'surname'            => 'Test',
            'email'              => 'owner@test.local',
            'password'           => 'secret123',
            'role'               => 'owner',
            'takes_appointments' => true,
            'is_active'          => true,
        ]);

        $clinic->update(['owner_id' => $owner->id]);

        $package = Package::create([
            'name' => 'Standart', 'price_per_seat' => 20,
            'min_seats' => 1, 'duration_days' => 30, 'is_active' => true,
        ]);

        DoctorSubscription::create([
            'clinic_id'  => $clinic->id, 'doctor_id' => $owner->id, 'package_id' => $package->id,
            'seats'      => 5, 'price_per_seat' => 20,
            'starts_at'  => now()->subDay()->toDateString(),
            'expires_at' => now()->addMonth()->toDateString(),
            'is_active'  => true,
        ]);

        Patient::create([
            'clinic_id' => $clinic->id, 'doctor_id' => $owner->id,
            'name' => 'Xəstə', 'surname' => 'Test', 'phone' => '+994 55 111 11 11',
        ]);

        TreatmentType::create([
            'clinic_id' => $clinic->id, 'doctor_id' => $owner->id,
            'name' => 'Müayinə', 'duration_minutes' => 30, 'color' => '#4a6fa5',
        ]);

        return $owner;
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Ad', 'surname' => 'Min', 'email' => 'admin@test.local',
            'password' => 'secret123', 'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function promoter(): User
    {
        return User::create([
            'name' => 'Pro', 'surname' => 'Motor', 'email' => 'promoter@test.local',
            'password' => 'secret123', 'role' => 'promoter', 'is_active' => true,
        ]);
    }

    /**
     * Every script tag on the page must carry the nonce from the header, and no
     * element may carry an inline `on*` handler — the policy blocks both.
     */
    private function assertPageIsPolicyClean(string $label, string $html, string $header): void
    {
        preg_match('/\'nonce-([A-Za-z0-9]+)\'/', $header, $m);
        $this->assertNotEmpty($m[1] ?? null, "{$label}: response carries no CSP nonce");
        $nonce = $m[1];

        preg_match_all('/<script\b([^>]*)>/i', $html, $tags);

        foreach ($tags[1] as $attrs) {
            $this->assertStringContainsString(
                'nonce="' . $nonce . '"',
                $attrs,
                "{$label}: a <script{$attrs}> tag is missing the request nonce"
            );
        }

        $this->assertGreaterThan(0, count($tags[1]), "{$label}: no script tags found, page probably did not render");

        // Attributes such as onclick="…" cannot be nonced, so they must be gone.
        preg_match_all('/\son(?:click|change|submit|input|load|error|focus|blur|keyup|keydown)\s*=\s*"/i', $html, $handlers);
        $this->assertSame([], $handlers[0], "{$label}: inline event handler(s) survive in the markup");
    }

    private function sweep(array $urls, ?User $user = null): void
    {
        foreach ($urls as $url) {
            $this->flushSession();

            $response = $user
                ? $this->actingAs($user)->get($url)
                : $this->get($url);

            $this->assertSame(200, $response->status(), "{$url} returned {$response->status()}");

            $this->assertPageIsPolicyClean(
                $url,
                $response->getContent(),
                $response->headers->get('Content-Security-Policy', '')
            );
        }
    }

    public function test_public_pages_carry_the_nonce_on_every_script(): void
    {
        $this->sweep(['/', '/login', '/register', '/promoter/register', '/demo', '/forgot-password']);
    }

    public function test_clinic_panel_pages_carry_the_nonce_on_every_script(): void
    {
        $owner   = $this->owner();
        $patient = Patient::firstOrFail();

        $this->sweep([
            '/panel/dashboard',
            '/panel/patients',
            '/panel/patients/create',
            "/panel/patients/{$patient->id}",
            "/panel/patients/{$patient->id}/edit",
            "/panel/patients/{$patient->id}/visits/create",
            '/panel/appointments',
            '/panel/appointments/create',
            '/panel/calendar',
            '/panel/treatment-types',
            '/panel/treatment-types/create',
            '/panel/staff',
            '/panel/staff/create',
            '/panel/profile',
            '/panel/profile/working-hours',
            '/panel/sms-templates',
            '/panel/whatsapp',
            '/panel/greetings',
            '/panel/subscription',
        ], $owner);
    }

    public function test_admin_pages_carry_the_nonce_on_every_script(): void
    {
        $this->sweep([
            '/admin/dashboard',
            '/admin/users',
            '/admin/users/create',
            '/admin/admins',
            '/admin/specialties',
            '/admin/packages',
            '/admin/subscriptions',
            '/admin/payments',
            '/admin/promoters',
            '/admin/promo-codes',
            '/admin/payouts',
            '/admin/sms-logs',
            '/admin/inquiries',
            '/admin/holidays',
            '/admin/settings/sms-templates',
            '/admin/settings/greetings',
            '/admin/settings/whatsapp',
            '/admin/settings/smtp',
            '/admin/settings/terms',
            '/admin/settings/security',
            '/admin/settings/promo',
            '/admin/cron-log',
            '/admin/profile',
        ], $this->admin());
    }

    public function test_promoter_pages_carry_the_nonce_on_every_script(): void
    {
        $this->sweep([
            '/promoter/dashboard',
            '/promoter/codes',
            '/promoter/redemptions',
            '/promoter/payouts',
        ], $this->promoter());
    }

    public function test_each_request_gets_its_own_nonce(): void
    {
        $first  = $this->get('/login')->headers->get('Content-Security-Policy');
        $this->flushSession();
        $second = $this->get('/login')->headers->get('Content-Security-Policy');

        $this->assertNotSame($first, $second, 'the nonce must not repeat across requests');
    }

    public function test_the_policy_pins_the_directives_that_matter(): void
    {
        $policy = $this->get('/login')->headers->get('Content-Security-Policy');

        foreach (["object-src 'none'", "base-uri 'self'", "form-action 'self'", "frame-ancestors 'self'", "'strict-dynamic'"] as $directive) {
            $this->assertStringContainsString($directive, $policy);
        }

        // A nonce in style-src would switch 'unsafe-inline' off and strip the
        // layout, because the views carry hundreds of style="…" attributes.
        preg_match('/style-src[^;]*/', $policy, $style);
        $this->assertStringNotContainsString('nonce-', $style[0]);
        $this->assertStringContainsString("'unsafe-inline'", $style[0]);
    }

    public function test_report_only_mode_swaps_the_header(): void
    {
        Setting::set('csp_mode', Csp::MODE_REPORT);

        $response = $this->get('/login');

        $this->assertNull($response->headers->get('Content-Security-Policy'));
        $this->assertNotNull($response->headers->get('Content-Security-Policy-Report-Only'));
    }

    public function test_the_policy_can_be_switched_off_entirely(): void
    {
        Setting::set('csp_mode', Csp::MODE_OFF);

        $response = $this->get('/login');

        $this->assertNull($response->headers->get('Content-Security-Policy'));
        $this->assertNull($response->headers->get('Content-Security-Policy-Report-Only'));
    }

    public function test_violation_reports_are_logged_without_a_session(): void
    {
        $this->postJson(Csp::REPORT_PATH, [
            'csp-report' => [
                'document-uri'       => 'https://innapp.az/panel/patients',
                'violated-directive' => 'script-src-attr',
                'blocked-uri'        => 'inline',
            ],
        ])->assertNoContent();
    }

    public function test_an_admin_can_switch_the_mode(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put('/admin/settings/csp', ['csp_mode' => Csp::MODE_REPORT])
            ->assertRedirect();

        $this->assertSame(Csp::MODE_REPORT, app(Csp::class)->mode());

        $this->actingAs($admin)
            ->put('/admin/settings/csp', ['csp_mode' => 'nonsense'])
            ->assertSessionHasErrors('csp_mode');
    }
}
