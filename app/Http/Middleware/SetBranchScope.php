<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBranchScope
{
    /**
     * Inject branch scope into the request so controllers can use it.
     * Owner: branch_id = null (sees ALL branches)
     * Admin/Sales: branch_id = their own branch
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            // Share globally via the request
            $request->merge([
                '_branch_id'       => $user->branchScopeId(),
                '_is_owner'        => $user->isOwner(),
                '_is_branch_admin' => $user->isAdmin() && !is_null($user->branch_id),
            ]);

            // Also share with all Blade views
            view()->share('currentBranch', $user->branch);
            view()->share('isOwner', $user->isOwner());
            view()->share('branchScopeId', $user->branchScopeId());

            // If admin/sales but has no branch assigned yet - warn
            if (!$user->isOwner() && is_null($user->branch_id) && $user->company_id) {
                view()->share('noBranchWarning', true);
            }
        }

        return $next($request);
    }
}
