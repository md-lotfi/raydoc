<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppActivated
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ FIX: Exclude Livewire internal routes and the activation page itself
        if ($request->routeIs('activation.*') || $request->is('livewire/*')) {
            return $next($request);
        }

        // Inject Service
        $licenseService = app(LicenseService::class);

        if (! $licenseService->check()) {
            return redirect()->route('activation.form');
        }

        return $next($request);
    }
}
