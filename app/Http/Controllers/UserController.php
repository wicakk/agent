<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ScopesByBranch;

    private function ensureAdminOrOwner(): void
    {
        if (!Auth::user()->isAdminOrOwner()) abort(403);
    }

    public function index()
    {
        $this->ensureAdminOrOwner();
        $user    = Auth::user();
        $company = $user->company;

        // Admin sees only users in their branch; Owner sees all
        $users = $this->branchScope(User::query())
            ->withCount(['transactions', 'salesActivities'])
            ->with('branch')
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'sales')")
            ->latest()
            ->paginate(20);

        $subscription = $company->activeSubscription;
        $userCount    = $company->salesCount();
        $userLimit    = $subscription ? $subscription->plan->max_users : 0;

        // For Owner: show all branches for assign UI
        $branches = $user->isOwner()
            ? $company->branches()->where('is_active', true)->get()
            : collect();

        return view('users.index', compact('users', 'userCount', 'userLimit', 'branches'));
    }

    public function create()
    {
        $this->ensureAdminOrOwner();
        $company = Auth::user()->company;

        if (!$company->canAddUser()) {
            return redirect()->route('users.index')
                ->with('error', 'Batas user sales telah tercapai. Upgrade paket untuk menambah lebih banyak user.');
        }

        $branches = $company->branches()->where('is_active', true)->get();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->ensureAdminOrOwner();
        $user    = Auth::user();
        $company = $user->company;

        if (!$company->canAddUser()) {
            return redirect()->route('users.index')->with('error', 'Batas user sales telah tercapai.');
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:admin,sales',
            'branch_id' => 'nullable|exists:branches,id',
            'password'  => 'required|min:8|confirmed',
        ]);

        // Admin can only create users in their own branch
        $branchId = $user->isOwner()
            ? ($validated['branch_id'] ?? null)
            : $user->branch_id;

        $company->users()->create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'branch_id' => $branchId,
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->ensureAdminOrOwner();
        $this->authorizeUser($user);
        $branches = Auth::user()->company->branches()->where('is_active', true)->get();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdminOrOwner();
        $this->authorizeUser($user);

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:owner,admin,sales',
            'branch_id' => 'nullable|exists:branches,id',
            'password'  => 'nullable|min:8|confirmed',
        ]);

        $updateData = [
            'name'  => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (!$user->isOwner()) {
            $updateData['role'] = $validated['role'];
            if (Auth::user()->isOwner()) {
                $updateData['branch_id'] = $validated['branch_id'] ?? null;
            }
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        return redirect()->route('users.index')
            ->with('success', 'User ' . $user->name . ' berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        $this->ensureAdminOrOwner();
        $this->authorizeUser($user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }
        if ($user->isOwner()) {
            return back()->with('error', 'Akun Owner tidak dapat dinonaktifkan.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    public function destroy(User $user)
    {
        $this->ensureAdminOrOwner();
        $this->authorizeUser($user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        if ($user->isOwner()) {
            return back()->with('error', 'Akun Owner tidak dapat dihapus.');
        }

        $name = $user->name;
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', "User {$name} berhasil dihapus.");
    }

    private function authorizeUser(User $user): void
    {
        $auth = Auth::user();
        if ($user->company_id !== $auth->company_id) abort(403);
        // Admin can only manage users in their own branch
        if ($auth->isAdmin() && $user->branch_id !== $auth->branch_id) {
            abort(403, 'User ini bukan dari cabang Anda.');
        }
    }
}
