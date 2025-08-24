<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TokenExpiryCheck
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $token->created_at->addMinutes(10)->isPast()) {
            $token->delete(); // Optional: invalidate expired token
            return response()->json(['message' => 'Token has expired. Please login again.'], 401);
        }

        return $next($request);
    }
}
