<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function ensureAdminOrOwner(): void
    {
        if (!Auth::user()->isAdminOrOwner()) {
            abort(403, 'Akses tidak diizinkan.');
        }
    }

    public function index()
    {
        $this->ensureAdminOrOwner();

        $company      = Auth::user()->company;
        $users        = $company->users()
            ->withCount(['transactions', 'salesActivities'])
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'sales')")
            ->latest()
            ->paginate(20);

        $subscription = $company->activeSubscription;
        $userCount    = $company->salesCount();
        $userLimit    = $subscription ? $subscription->plan->max_users : 0;

        return view('users.index', compact('users', 'userCount', 'userLimit'));
    }

    public function create()
    {
        $this->ensureAdminOrOwner();

        $company = Auth::user()->company;
        if (!$company->canAddUser()) {
            return redirect()->route('users.index')
                ->with('error', 'Batas user sales telah tercapai. Upgrade paket untuk menambah lebih banyak user.');
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->ensureAdminOrOwner();

        $company = Auth::user()->company;
        if (!$company->canAddUser()) {
            return redirect()->route('users.index')
                ->with('error', 'Batas user sales telah tercapai.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,sales',
            'password' => 'required|min:8|confirmed',
        ]);

        $company->users()->create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
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
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdminOrOwner();
        $this->authorizeUser($user);

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:owner,admin,sales',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Prevent changing owner role unless you are owner
        if ($user->isOwner() && !Auth::user()->isOwner()) {
            abort(403);
        }

        $updateData = [
            'name'  => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ];

        // Only allow role change if not owner, or if current user is owner
        if (!$user->isOwner()) {
            $updateData['role'] = $validated['role'];
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
        if ($user->company_id !== Auth::user()->company_id) {
            abort(403, 'Akses tidak diizinkan.');
        }
    }
}
