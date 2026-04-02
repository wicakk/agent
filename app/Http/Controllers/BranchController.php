<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    private function ownerOnly(): void
    {
        if (!Auth::user()->isOwner()) {
            abort(403, 'Hanya Owner yang dapat mengelola cabang.');
        }
    }

    public function index()
    {
        $this->ownerOnly();
        $company  = Auth::user()->company;
        $branches = $company->branches()
            ->withCount([
                'users as admin_count' => fn($q) => $q->where('role', 'admin'),
                'users as sales_count' => fn($q) => $q->where('role', 'sales')->where('is_active', true),
                'stores',
                'warehouses',
            ])
            ->orderBy('name')
            ->get();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $this->ownerOnly();
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $this->ownerOnly();
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'code'     => 'nullable|string|max:20',
            'city'     => 'nullable|string|max:50',
            'address'  => 'nullable|string|max:500',
            'phone'    => 'nullable|string|max:20',
            'pic_name' => 'nullable|string|max:100',
        ]);

        Auth::user()->company->branches()->create($validated);
        return redirect()->route('branches.index')
            ->with('success', 'Cabang "' . $validated['name'] . '" berhasil dibuat.');
    }

    public function edit(Branch $branch)
    {
        $this->ownerOnly();
        $this->authorizeBranch($branch);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->ownerOnly();
        $this->authorizeBranch($branch);

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'nullable|string|max:20',
            'city'      => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:20',
            'pic_name'  => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $branch->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }

    public function show(Branch $branch)
    {
        $this->ownerOnly();
        $this->authorizeBranch($branch);

        $branch->load(['users', 'warehouses', 'stores']);
        $stats = [
            'sales_count'  => $branch->users()->where('role','sales')->where('is_active',true)->count(),
            'admin_count'  => $branch->users()->where('role','admin')->count(),
            'store_count'  => $branch->stores()->count(),
            'wh_count'     => $branch->warehouses()->count(),
            'revenue_month'=> $branch->transactions()
                                ->where('type','sale')->where('status','!=','cancelled')
                                ->whereMonth('created_at', now()->month)
                                ->sum('total'),
        ];

        return view('branches.show', compact('branch', 'stats'));
    }

    public function destroy(Branch $branch)
    {
        $this->ownerOnly();
        $this->authorizeBranch($branch);

        // Prevent deleting branch with active users
        if ($branch->users()->where('is_active', true)->exists()) {
            return back()->with('error', 'Tidak dapat menghapus cabang yang masih memiliki user aktif.');
        }

        $name = $branch->name;
        $branch->delete();
        return redirect()->route('branches.index')
            ->with('success', "Cabang \"{$name}\" berhasil dihapus.");
    }

    // Assign a user (admin/sales) to a branch
    public function assignUser(Request $request)
    {
        $this->ownerOnly();
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user   = \App\Models\User::findOrFail($request->user_id);
        $branch = Branch::findOrFail($request->branch_id);

        // Ensure both belong to this company
        if ($user->company_id !== Auth::user()->company_id
            || $branch->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $user->update(['branch_id' => $branch->id]);
        return back()->with('success', "{$user->name} berhasil di-assign ke cabang {$branch->name}.");
    }

    private function authorizeBranch(Branch $branch): void
    {
        if ($branch->company_id !== Auth::user()->company_id) {
            abort(403, 'Akses tidak diizinkan.');
        }
    }
}
