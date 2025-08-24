<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $providedPublicKey = $request->header('X-PUBLIC-KEY');
        $providedPrivateKey = $request->header('X-PRIVATE-KEY');

        $apiKey = ApiKey::where('public_key', $providedPublicKey)->first();

        if (
            !$apiKey ||
            !$apiKey->active ||
            $apiKey->private_key !== $providedPrivateKey
        ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

