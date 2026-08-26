<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Shared validation for a holiday date, used by both the admin's platform-wide
 * calendar and a clinic's own dates.
 */
trait ValidatesHolidays
{
    /**
     * @return array<string, mixed>
     */
    protected function validatedHoliday(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:100'],
            'month'     => ['required', 'integer', 'min:1', 'max:12'],
            'day'       => ['required', 'integer', 'min:1', 'max:31'],
            'year'      => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'template'  => ['nullable', 'string', 'max:160'],
            'is_active' => ['boolean'],
        ], [], [
            'name'     => 'ad',
            'month'    => 'ay',
            'day'      => 'gün',
            'year'     => 'il',
            'template' => 'şablon',
        ]);

        $validator->after(function ($validator) use ($request) {
            // A recurring date is checked against a leap year so 29 February
            // stays legal; a date pinned to one year is checked against it.
            $year = (int) ($request->input('year') ?: 2024);

            if (! checkdate((int) $request->input('month'), (int) $request->input('day'), $year)) {
                $validator->errors()->add('day', 'Belə bir tarix yoxdur.');
            }
        });

        $data = $validator->validate();

        $data['year']      = $data['year'] ?: null;
        $data['template']  = trim((string) ($data['template'] ?? '')) ?: null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
