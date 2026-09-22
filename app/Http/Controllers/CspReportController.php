<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Collects the violation reports browsers send for the Content Security Policy.
 *
 * This is what makes "report only" mode worth running: without somewhere for
 * the reports to land, the mode blocks nothing and tells nobody. Only the few
 * fields that identify a real problem are kept — the body is attacker-
 * influenced, so nothing from it is trusted or echoed back.
 */
class CspReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $report = $request->json('csp-report') ?? $request->input('csp-report') ?? [];

        if (is_array($report) && $report !== []) {
            Log::channel('csp')->warning('CSP violation', [
                'directive' => $this->field($report, 'violated-directive') ?: $this->field($report, 'effective-directive'),
                'blocked'   => $this->field($report, 'blocked-uri'),
                'document'  => $this->field($report, 'document-uri'),
                'sample'    => $this->field($report, 'script-sample', 120),
                'line'      => $this->field($report, 'line-number', 12),
            ]);
        }

        return response()->noContent();
    }

    private function field(array $report, string $key, int $limit = 300): string
    {
        $value = $report[$key] ?? '';

        return is_scalar($value) ? mb_substr((string) $value, 0, $limit) : '';
    }
}
