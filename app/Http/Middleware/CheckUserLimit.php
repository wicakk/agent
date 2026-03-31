<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->company) {
            $company = $user->company;
            $subscription = $company->activeSubscription;

            if ($subscription) {
                $currentUsers = $company->users()
                    ->where('role', 'sales')
                    ->where('is_active', true)
                    ->count();

                // Share limit info via request
                $request->merge([
                    'user_limit' => $subscription->plan->max_users,
                    'user_count' => $currentUsers,
                    'can_add_user' => $currentUsers < $subscription->plan->max_users,
                ]);
            }
        }

        return $next($request);
    }
}
