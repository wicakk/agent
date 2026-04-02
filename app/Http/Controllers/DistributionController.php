<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    use ScopesByBranch;

    public function index(Request $request)
    {
        $query = $this->branchScope(Distribution::query())
            ->with(['driver', 'store', 'warehouse', 'items.product']);

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('driver_id')) $query->where('driver_id', $request->driver_id);
        if ($request->filled('search'))    $query->where('delivery_no', 'like', '%'.$request->search.'%');

        $distributions = $query->latest()->paginate(15)->withQueryString();

        $drivers = $this->branchScope(\App\Models\User::query())
            ->where('is_active', true)
            ->whereIn('role', ['sales','admin'])
            ->get();

        $statusCounts = $this->branchScope(Distribution::query())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('distribution.index', compact('distributions', 'drivers', 'statusCounts'));
    }

    public function create()
    {
        $user       = Auth::user();
        $drivers    = $this->branchScope(\App\Models\User::query())->where('is_active', true)->whereIn('role', ['sales','admin'])->get();
        $stores     = $this->branchScope(\App\Models\Store::query())->whereIn('status', ['active','potential'])->orderBy('name')->get();
        $warehouses = $this->branchScope(\App\Models\Warehouse::query())->where('is_active', true)->get();
        $products   = $this->branchScope(Product::query())->where('is_active', true)->where('stock_current', '>', 0)->orderBy('name')->get();

        if ($warehouses->isEmpty()) {
            return redirect()->route('distribution.index')
                ->with('error', 'Buat gudang untuk cabang ini terlebih dahulu.');
        }

        return view('distribution.create', compact('drivers', 'stores', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'driver_id'           => 'required|exists:users,id',
            'store_id'            => 'nullable|exists:stores,id',
            'destination_address' => 'required|string|max:500',
            'scheduled_at'        => 'nullable|date',
            'notes'               => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $distribution = Distribution::create([
                'company_id'          => $user->company_id,
                'branch_id'           => $user->branch_id,
                'warehouse_id'        => $validated['warehouse_id'],
                'driver_id'           => $validated['driver_id'],
                'store_id'            => $validated['store_id'] ?? null,
                'destination_address' => $validated['destination_address'],
                'scheduled_at'        => $validated['scheduled_at'] ?? null,
                'notes'               => $validated['notes'] ?? null,
                'status'              => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $distribution->items()->create([
                    'product_id'         => $item['product_id'],
                    'quantity_requested' => (int) $item['quantity'],
                    'quantity_delivered' => 0,
                    'unit_price'         => (float) $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('distribution.index')->with('success', 'Distribusi berhasil dibuat.');
    }

    public function show(Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);
        $distribution->load(['driver', 'store', 'warehouse', 'items.product']);
        return view('distribution.show', compact('distribution'));
    }

    public function updateStatus(Request $request, Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);
        $request->validate(['status' => 'required|in:pending,in_transit,delivered,cancelled,returned']);

        DB::transaction(function () use ($distribution, $request) {
            $newStatus = $request->status;
            $updates   = ['status' => $newStatus];

            if ($newStatus === 'in_transit' && !$distribution->departed_at) {
                $updates['departed_at'] = now();
            }

            if ($newStatus === 'delivered' && !$distribution->delivered_at) {
                $updates['delivered_at'] = now();
                foreach ($distribution->items as $item) {
                    $product = Product::find($item->product_id);
                    if (!$product) continue;
                    $qty = min($item->quantity_requested, $product->stock_current);
                    if ($qty > 0) {
                        $before = $product->stock_current;
                        $product->decrement('stock_current', $qty);
                        StockMovement::create([
                            'company_id'   => $distribution->company_id,
                            'product_id'   => $product->id,
                            'warehouse_id' => $distribution->warehouse_id,
                            'user_id'      => Auth::id(),
                            'type'         => 'out',
                            'quantity'     => $qty,
                            'stock_before' => $before,
                            'stock_after'  => $product->stock_current,
                            'reference_no' => $distribution->delivery_no,
                            'reason'       => 'Distribusi ' . $distribution->delivery_no,
                        ]);
                        $item->update(['quantity_delivered' => $qty]);
                    }
                }
            }
            $distribution->update($updates);
        });

        return back()->with('success', 'Status distribusi diperbarui.');
    }

    public function destroy(Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);
        if ($distribution->status !== 'pending') {
            return back()->with('error', 'Hanya distribusi berstatus Pending yang dapat dihapus.');
        }
        $no = $distribution->delivery_no;
        $distribution->items()->delete();
        $distribution->delete();
        return redirect()->route('distribution.index')->with('success', "Distribusi {$no} berhasil dihapus.");
    }

    private function authorizeDistribution(Distribution $distribution): void
    {
        $user = Auth::user();
        if ($distribution->company_id !== $user->company_id) abort(403);
        if (!$user->isOwner() && $distribution->branch_id !== $user->branch_id) abort(403, 'Distribusi ini bukan milik cabang Anda.');
    }
}
