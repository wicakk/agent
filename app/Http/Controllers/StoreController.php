<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    use ScopesByBranch;

    public function index(Request $request)
    {
        $query = $this->branchScope(Store::query())->with('salesUser');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name',       'like', "%$s%")
                ->orWhere('owner_name','like', "%$s%")
                ->orWhere('city',      'like', "%$s%")
            );
        }
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);

        $stores      = $query->orderBy('name')->paginate(18)->withQueryString();
        $salesUsers  = $this->branchScope(\App\Models\User::query())->where('role','sales')->where('is_active',true)->get();
        $statusCount = $this->branchScope(Store::query())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('stores.index', compact('stores', 'salesUsers', 'statusCount'));
    }

    public function create()
    {
        $salesUsers = $this->branchScope(\App\Models\User::query())->where('role','sales')->where('is_active',true)->get();
        return view('stores.create', compact('salesUsers'));
    }

    public function store(Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'owner_name' => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'required|string|max:500',
            'city'       => 'nullable|string|max:50',
            'district'   => 'nullable|string|max:50',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
            'status'     => 'required|in:active,potential,inactive',
            'store_type' => 'nullable|string|max:50',
            'user_id'    => 'nullable|exists:users,id',
            'notes'      => 'nullable|string|max:1000',
        ]);

        Store::create([
            ...$validated,
            'company_id' => $user->company_id,
            'branch_id'  => $user->branch_id,
        ]);

        return redirect()->route('stores.index')->with('success', 'Toko berhasil ditambahkan.');
    }

    public function show(Store $store)
    {
        $this->authorizeStore($store);
        $store->load([
            'salesUser',
            'transactions' => fn($q) => $q->latest()->take(10),
            'activities'   => fn($q) => $q->with('user')->latest('activity_at')->take(10),
        ]);
        return view('stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $this->authorizeStore($store);
        $salesUsers = $this->branchScope(\App\Models\User::query())->where('role','sales')->where('is_active',true)->get();
        return view('stores.edit', compact('store', 'salesUsers'));
    }

    public function update(Request $request, Store $store)
    {
        $this->authorizeStore($store);
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'owner_name' => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'required|string|max:500',
            'city'       => 'nullable|string|max:50',
            'district'   => 'nullable|string|max:50',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
            'status'     => 'required|in:active,potential,inactive',
            'store_type' => 'nullable|string|max:50',
            'user_id'    => 'nullable|exists:users,id',
        ]);
        $store->update($validated);
        return redirect()->route('stores.index')->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store)
    {
        $this->authorizeStore($store);
        $name = $store->name;
        $store->delete();
        return redirect()->route('stores.index')->with('success', "Toko \"$name\" berhasil dihapus.");
    }

    private function authorizeStore(Store $store): void
    {
        $user = Auth::user();
        if ($store->company_id !== $user->company_id) abort(403);
        if (!$user->isOwner() && $store->branch_id !== $user->branch_id) abort(403, 'Toko ini bukan milik cabang Anda.');
    }
}
