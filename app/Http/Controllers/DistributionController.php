<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\GpsLog;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $drivers       = $this->branchScope(\App\Models\User::query())
            ->where('is_active', true)
            ->whereIn('role', ['sales', 'admin'])
            ->get();
        $statusCounts  = $this->branchScope(Distribution::query())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('distribution.index', compact('distributions', 'drivers', 'statusCounts'));
    }

    public function create()
    {
        $user       = Auth::user();
        $drivers    = $this->branchScope(\App\Models\User::query())
            ->where('is_active', true)->whereIn('role', ['sales','admin'])->get();
        $stores     = $this->branchScope(\App\Models\Store::query())
            ->whereIn('status', ['active','potential'])->orderBy('name')->get();
        $warehouses = $this->branchScope(\App\Models\Warehouse::query())
            ->where('is_active', true)->get();
        $products   = $this->branchScope(\App\Models\Product::query())
            ->where('is_active', true)->where('stock_current', '>', 0)->orderBy('name')->get();

        if ($warehouses->isEmpty()) {
            return redirect()->route('distribution.index')
                ->with('error', 'Buat gudang terlebih dahulu sebelum membuat distribusi.');
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
                    'quantity_requested' => (int)$item['quantity'],
                    'quantity_delivered' => 0,
                    'unit_price'         => (float)$item['unit_price'],
                ]);
            }
        });

        return redirect()->route('distribution.index')
            ->with('success', 'Distribusi berhasil dibuat.');
    }

    public function show(Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);
        $distribution->load(['driver', 'store', 'warehouse', 'items.product']);

        // Load GPS track points for this delivery
        $trackPoints = GpsLog::where('company_id', $distribution->company_id)
            ->where('user_id', $distribution->driver_id)
            ->when($distribution->departed_at, fn($q) =>
                $q->where('logged_at', '>=', $distribution->departed_at)
            )
            ->when($distribution->delivered_at, fn($q) =>
                $q->where('logged_at', '<=', $distribution->delivered_at)
            )
            ->orderBy('logged_at')
            ->get(['latitude', 'longitude', 'logged_at', 'speed', 'is_mock_location']);

        return view('distribution.show', compact('distribution', 'trackPoints'));
    }

    /**
     * Driver mulai berangkat — aktifkan GPS tracking
     */
    public function depart(Request $request, Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);

        if ($distribution->status !== 'pending') {
            return response()->json(['error' => 'Status tidak valid.'], 422);
        }

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($distribution, $request) {
            $distribution->update([
                'status'      => 'in_transit',
                'departed_at' => now(),
            ]);

            // Log initial GPS point
            GpsLog::create([
                'company_id'  => $distribution->company_id,
                'user_id'     => $distribution->driver_id,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
                'accuracy'    => $request->accuracy,
                'is_mock_location' => false,
                'is_location_jump' => false,
                'logged_at'   => now(),
            ]);

            // Update driver's current location
            $distribution->driver->update([
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'last_location_at' => now(),
            ]);
        });

        return response()->json([
            'success'    => true,
            'status'     => 'in_transit',
            'message'    => 'Perjalanan dimulai. GPS tracking aktif.',
            'departed_at'=> now()->format('d M Y, H:i'),
        ]);
    }

    /**
     * Kirim GPS point selama perjalanan (dipanggil setiap ~15 detik dari JS)
     */
    public function logGps(Request $request, Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);

        if ($distribution->status !== 'in_transit') {
            return response()->json(['error' => 'Tracking hanya aktif saat in_transit.'], 422);
        }

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric',
            'speed'     => 'nullable|numeric',
        ]);

        $isJump = GpsLog::detectAnomaly(
            $distribution->driver,
            $request->latitude,
            $request->longitude
        );

        $lastLog  = GpsLog::where('user_id', $distribution->driver_id)->latest('logged_at')->first();
        $distance = $lastLog
            ? GpsLog::haversine($lastLog->latitude, $lastLog->longitude, $request->latitude, $request->longitude)
            : null;

        GpsLog::create([
            'company_id'             => $distribution->company_id,
            'user_id'                => $distribution->driver_id,
            'latitude'               => $request->latitude,
            'longitude'              => $request->longitude,
            'accuracy'               => $request->accuracy,
            'speed'                  => $request->speed,
            'is_mock_location'       => false,
            'is_location_jump'       => $isJump,
            'distance_from_previous' => $distance,
            'logged_at'              => now(),
        ]);

        $distribution->driver->update([
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'last_location_at' => now(),
        ]);

        return response()->json(['success' => true, 'anomaly' => $isJump]);
    }

    /**
     * Get live GPS track data as JSON (polling)
     */
    public function trackData(Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);

        $points = GpsLog::where('company_id', $distribution->company_id)
            ->where('user_id', $distribution->driver_id)
            ->when($distribution->departed_at, fn($q) =>
                $q->where('logged_at', '>=', $distribution->departed_at)
            )
            ->orderBy('logged_at')
            ->get(['latitude', 'longitude', 'logged_at', 'speed', 'is_mock_location', 'is_location_jump']);

        return response()->json([
            'status'      => $distribution->status,
            'driver'      => [
                'name'      => $distribution->driver->name,
                'latitude'  => $distribution->driver->latitude,
                'longitude' => $distribution->driver->longitude,
                'last_seen' => $distribution->driver->last_location_at?->diffForHumans(),
            ],
            'points'      => $points,
            'destination' => [
                'lat'     => $distribution->destination_lat,
                'lng'     => $distribution->destination_lng,
                'address' => $distribution->destination_address,
            ],
            'departed_at' => $distribution->departed_at?->format('H:i'),
        ]);
    }

    /**
     * Tandai terkirim — wajib foto bukti
     */
    public function deliver(Request $request, Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);

        if ($distribution->status !== 'in_transit') {
            return response()->json(['error' => 'Hanya bisa ditandai terkirim saat status In Transit.'], 422);
        }

        $request->validate([
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'proof_photo'    => 'required|string', // base64
            'delivery_notes' => 'nullable|string|max:500',
        ]);

        // Decode & save base64 photo
        $base64 = $request->proof_photo;
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $ext    = $matches[1];
            $data   = base64_decode(substr($base64, strpos($base64, ',') + 1));
            $filename = 'proofs/' . $distribution->delivery_no . '_' . time() . '.' . $ext;
            Storage::disk('public')->put($filename, $data);
        } else {
            return response()->json(['error' => 'Format foto tidak valid.'], 422);
        }

        DB::transaction(function () use ($distribution, $request, $filename) {
            $distribution->update([
                'status'         => 'delivered',
                'delivered_at'   => now(),
                'proof_photo'    => $filename,
                'delivery_notes' => $request->delivery_notes,
            ]);

            // Deduct stock for each item
            foreach ($distribution->items as $item) {
                $product = Product::find($item->product_id);
                if (!$product) continue;

                $qty = $item->quantity_requested;
                if ($product->stock_current < $qty) $qty = $product->stock_current;
                if ($qty <= 0) continue;

                $stockBefore = $product->stock_current;
                $product->decrement('stock_current', $qty);

                StockMovement::create([
                    'company_id'   => $distribution->company_id,
                    'product_id'   => $product->id,
                    'warehouse_id' => $distribution->warehouse_id,
                    'user_id'      => Auth::id(),
                    'type'         => 'out',
                    'quantity'     => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $product->stock_current,
                    'reference_no' => $distribution->delivery_no,
                    'reason'       => 'Distribusi ' . $distribution->delivery_no,
                ]);

                $item->update(['quantity_delivered' => $qty]);
            }

            // Final GPS point at delivery location
            GpsLog::create([
                'company_id'  => $distribution->company_id,
                'user_id'     => $distribution->driver_id,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
                'is_mock_location' => false,
                'logged_at'   => now(),
            ]);
        });

        return response()->json([
            'success'      => true,
            'message'      => 'Pengiriman berhasil dikonfirmasi!',
            'delivered_at' => now()->format('d M Y, H:i'),
        ]);
    }

    public function updateStatus(Request $request, Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);
        $request->validate(['status' => 'required|in:pending,in_transit,delivered,cancelled,returned']);
        $distribution->update(['status' => $request->status]);
        return back()->with('success', 'Status diperbarui.');
    }

    public function destroy(Distribution $distribution)
    {
        $this->authorizeDistribution($distribution);
        if ($distribution->status !== 'pending') {
            return back()->with('error', 'Hanya distribusi Pending yang dapat dihapus.');
        }
        $distribution->items()->delete();
        $distribution->delete();
        return redirect()->route('distribution.index')->with('success', 'Distribusi dihapus.');
    }

    private function authorizeDistribution(Distribution $distribution): void
    {
        if ($distribution->company_id !== Auth::user()->company_id) abort(403);
    }
}
