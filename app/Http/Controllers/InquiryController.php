<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\VerifiesCaptcha;
use App\Models\Inquiry;
use App\Rules\AzMobilePhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives the two lead forms on the public home page.
 *
 * Both forms live on the same page, so each one validates into its own error
 * bag and redirects back to its own anchor — a mistake in one never lights up
 * the other.
 */
class InquiryController extends Controller
{
    use VerifiesCaptcha;

    private const ANCHORS = [
        Inquiry::TYPE_CONTACT => '#contact',
        Inquiry::TYPE_DEMO    => '#demo',
    ];

    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type') === Inquiry::TYPE_DEMO ? Inquiry::TYPE_DEMO : Inquiry::TYPE_CONTACT;

        $rules = $type === Inquiry::TYPE_DEMO
            ? [
                'email' => ['required', 'email:rfc', 'max:150'],
            ]
            : [
                'name'    => ['required', 'string', 'max:120'],
                'email'   => ['nullable', 'email:rfc', 'max:150', 'required_without:phone'],
                'phone'   => ['nullable', 'string', new AzMobilePhone, 'required_without:email'],
                'message' => ['nullable', 'string', 'max:2000'],
            ];

        // Honeypot: real visitors never see this field, bots fill everything.
        $rules['website'] = ['prohibited'];

        $validated = $request->validateWithBag(
            $type,
            $rules + $this->captchaRules($request, 'inquiry'),
            [
                'name.required'          => 'Ad və soyadınızı yazın.',
                'email.required'         => 'E-poçt ünvanınızı yazın.',
                'email.email'            => 'E-poçt ünvanı düzgün deyil.',
                'email.required_without' => 'E-poçt və ya telefon nömrənizi yazın.',
                'phone.required_without' => 'E-poçt və ya telefon nömrənizi yazın.',
                'message.max'            => 'Mesaj 2000 simvoldan uzun ola bilməz.',
                'website.prohibited'     => 'Sorğu qəbul edilmədi.',
            ] + $this->captchaMessages()
        );

        Inquiry::create([
            'type'       => $type,
            'name'       => $validated['name'] ?? null,
            'email'      => $validated['email'] ?? null,
            'phone'      => $validated['phone'] ?? null,
            'message'    => $validated['message'] ?? null,
            'ip'         => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        Log::info('inquiry received', ['type' => $type, 'email' => $validated['email'] ?? null]);

        return redirect()
            ->to(route('home') . self::ANCHORS[$type])
            ->with('inquiry_sent', $type);
    }
}
