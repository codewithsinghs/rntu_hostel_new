<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckValidReferer
{
    public function handle(Request $request, Closure $next): Response
    {
        $referer = $request->headers->get('referer');
        $validReferers = explode(',', env('VALID_REFERERS')); // Support comma-separated values

        if ($referer && $this->isRefererValid($referer, $validReferers)) {
            return $next($request);
        }

        return response()->json(['error' => 'Invalid referer'], 403);
    }

    private function isRefererValid($referer, $validReferers)
    {
        foreach ($validReferers as $valid) {
            if (str_starts_with($referer, trim($valid))) {
                return true;
            }
        }
        return false;
    }
}
