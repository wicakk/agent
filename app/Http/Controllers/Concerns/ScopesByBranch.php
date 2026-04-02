<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopesByBranch
{
    /**
     * Owner → sees all company data (no branch filter)
     * Admin/Sales → filtered to their branch_id only
     */
    protected function branchScope(Builder $query, string $column = 'branch_id'): Builder
    {
        $user = Auth::user();

        if ($user->isOwner()) {
            return $query->where('company_id', $user->company_id);
        }

        return $query
            ->where('company_id', $user->company_id)
            ->where($column, $user->branch_id);
    }

    protected function getBranchScopeId(): ?int
    {
        return Auth::user()->branchScopeId();
    }
}
