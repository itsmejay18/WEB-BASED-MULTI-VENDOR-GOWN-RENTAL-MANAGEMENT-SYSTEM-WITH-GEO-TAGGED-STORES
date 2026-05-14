<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedProviderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = $request->user()?->provider;

        if (! $provider) {
            return response()->json(['message' => 'Provider profile not found.'], 403);
        }

        if ($provider->verification_status !== 'verified') {
            return response()->json(['message' => 'Verified provider access required.'], 403);
        }

        return $next($request);
    }
}
