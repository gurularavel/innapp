<?php

namespace App\Services\Concerns;

trait NormalizesPhone
{
    /**
     * Normalize an Azerbaijani phone number to international MSISDN format.
     * Examples:
     *   055 123 45 67  →  994551234567
     *   +994551234567  →  994551234567
     *   994551234567   →  994551234567
     */
    protected function normalizePhone(string $phone): string
    {
        // Strip everything except digits
        $phone = preg_replace('/\D/', '', $phone);

        // 0... → 994...
        if (str_starts_with($phone, '0')) {
            $phone = '994' . substr($phone, 1);
        }

        // Bare 9-digit number (e.g. 551234567) → 994...
        if (strlen($phone) === 9) {
            $phone = '994' . $phone;
        }

        return $phone;
    }
}
