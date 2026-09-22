<?php

namespace App\Http\Middleware;

use App\Support\Csp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attaches the Content Security Policy to every HTML response.
 *
 * The nonce is resolved from the container, which is where the views read it
 * from too (`@cspNonce`), so the header and the page always agree.
 */
class ContentSecurityPolicy
{
    public function __construct(private Csp $csp) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Before anything renders: this request gets its own nonce.
        $this->csp->rotate();

        $response = $next($request);

        $header = $this->csp->headerName();

        // A response that set its own policy keeps it — streamed patient files
        // ship a far stricter one of their own.
        if ($header === null || $this->alreadyHasPolicy($response)) {
            return $response;
        }

        $response->headers->set($header, $this->csp->policy());
        $response->headers->set('Reporting-Endpoints', 'csp="' . Csp::REPORT_PATH . '"');

        return $response;
    }

    private function alreadyHasPolicy(Response $response): bool
    {
        return $response->headers->has('Content-Security-Policy')
            || $response->headers->has('Content-Security-Policy-Report-Only');
    }
}
