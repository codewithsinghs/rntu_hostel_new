<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;


class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    
    public function handle($request, Closure $next)
    {
        $publicKey = $request->header('X-Public-Key');
    
        if (!$publicKey) {
            return response()->json(['error' => 'API key missing'], 401);
        }
    
        $apiKey = ApiKey::where('public_key', $publicKey)->where('active', true)->first();
    
        if (!$apiKey) {
            return response()->json(['error' => 'Invalid or inactive API key'], 403);
        }
    
        // Attach the API key info to the request for further checks
        $request->merge(['api_key_owner' => $apiKey->owner, 'api_key_id' => $apiKey->id]);
    
        return $next($request);
    }
    
}
