<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->company) {
            return redirect()->route('login');
        }

        // Owners bypass subscription check when accessing billing
        if ($user->isOwner() && $request->routeIs('billing.*')) {
            return $next($request);
        }

        $subscription = $user->company->activeSubscription;

        if (!$subscription) {
            if ($user->isOwner()) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Langganan Anda tidak aktif. Hubungi support untuk bantuan.');
            }
            abort(402, 'Langganan perusahaan tidak aktif. Hubungi Owner.');
        }

        // Check if expired
        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            if ($user->isOwner()) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Masa langganan telah habis. Silakan perpanjang paket Anda.');
            }
            abort(402, 'Masa langganan perusahaan telah habis. Hubungi Owner untuk perpanjang.');
        }

        return $next($request);
    }
}
