<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Azerbaijani mobile number. Accepts what the IMask input produces
 * ("+994 55 123 45 67") as well as bare forms ("0551234567", "551234567",
 * "994551234567"); anything with letters or a wrong length fails.
 * `format()` returns the canonical "+994 55 123 45 67" for storage.
 */
class AzMobilePhone implements ValidationRule
{
    /** Mobile operator prefixes (Azercell, Bakcell, Nar, Naxtel). */
    public const PREFIXES = ['10', '50', '51', '55', '60', '70', '77', '99'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || self::digits($value) === null) {
            $fail('Mobil nömrə düzgün deyil. Format: +994 55 123 45 67');
        }
    }

    /**
     * The 9 national digits ("551234567") or null when the input is not a
     * well-formed Azerbaijani mobile number.
     */
    public static function digits(string $value): ?string
    {
        // Only digits, "+", spaces and the mask's "_" placeholder may appear.
        if (preg_match('/[^\d\s+_\-()]/u', $value)) {
            return null;
        }

        $d = preg_replace('/\D/', '', $value);

        if (strlen($d) === 12 && str_starts_with($d, '994')) {
            $d = substr($d, 3);
        } elseif (strlen($d) === 10 && str_starts_with($d, '0')) {
            $d = substr($d, 1);
        }

        if (strlen($d) !== 9 || ! in_array(substr($d, 0, 2), self::PREFIXES, true)) {
            return null;
        }

        return $d;
    }

    /** Canonical display/storage form: "+994 55 123 45 67". */
    public static function format(string $value): string
    {
        $d = self::digits($value) ?? preg_replace('/\D/', '', $value);

        return '+994 ' . substr($d, 0, 2) . ' ' . substr($d, 2, 3) . ' ' . substr($d, 5, 2) . ' ' . substr($d, 7, 2);
    }
}
