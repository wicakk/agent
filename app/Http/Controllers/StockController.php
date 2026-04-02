<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    use ScopesByBranch;

    public function index(Request $request)
    {
        $query = $this->branchScope(Product::query())->with('warehouse');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('sku','like',"%$s%"));
        }
        if ($request->filled('warehouse_id')) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('category'))     $query->where('category', $request->category);
        if ($request->status === 'low')       $query->whereColumn('stock_current','<=','stock_minimum');

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $warehouses = $this->branchScope(\App\Models\Warehouse::query())->where('is_active', true)->get();
        $categories = $this->branchScope(Product::query())->distinct()->pluck('category')->filter()->sort()->values();
        $lowStockCount = $this->branchScope(Product::query())->whereColumn('stock_current','<=','stock_minimum')->count();

        return view('stock.index', compact('products','warehouses','categories','lowStockCount'));
    }

    public function create()
    {
        $warehouses = $this->branchScope(\App\Models\Warehouse::query())->where('is_active', true)->get();
        if ($warehouses->isEmpty()) {
            return redirect()->route('stock.index')
                ->with('error', 'Buat gudang untuk cabang ini terlebih dahulu.');
        }
        return view('stock.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'warehouse_id'  => 'required|exists:warehouses,id',
            'name'          => 'required|string|max:100',
            'sku'           => 'nullable|string|max:50',
            'category'      => 'nullable|string|max:50',
            'brand'         => 'nullable|string|max:50',
            'unit'          => 'required|string|max:20',
            'unit_per_pack' => 'nullable|integer|min:1',
            'stock_current' => 'nullable|integer|min:0',
            'stock_minimum' => 'nullable|integer|min:0',
            'buy_price'     => 'nullable|numeric|min:0',
            'sell_price'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $initialStock = (int)($validated['stock_current'] ?? 0);
            $product = Product::create([
                ...$validated,
                'company_id'    => $user->company_id,
                'branch_id'     => $user->branch_id,  // ← scoped to user's branch
                'stock_current' => $initialStock,
                'stock_minimum' => $validated['stock_minimum'] ?? 10,
                'is_active'     => true,
            ]);

            if ($initialStock > 0) {
                StockMovement::create([
                    'company_id'   => $user->company_id,
                    'product_id'   => $product->id,
                    'warehouse_id' => $product->warehouse_id,
                    'user_id'      => $user->id,
                    'type'         => 'in',
                    'quantity'     => $initialStock,
                    'stock_before' => 0,
                    'stock_after'  => $initialStock,
                    'reason'       => 'Stok awal produk baru',
                ]);
            }
        });

        return redirect()->route('stock.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $warehouses = $this->branchScope(\App\Models\Warehouse::query())->where('is_active', true)->get();
        return view('stock.edit', compact('product', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'sku'           => 'nullable|string|max:50',
            'category'      => 'nullable|string|max:50',
            'brand'         => 'nullable|string|max:50',
            'unit'          => 'required|string|max:20',
            'stock_minimum' => 'nullable|integer|min:0',
            'buy_price'     => 'nullable|numeric|min:0',
            'sell_price'    => 'nullable|numeric|min:0',
        ]);
        $product->update([...$validated, 'is_active' => $request->boolean('is_active', true)]);
        return redirect()->route('stock.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function movement(Request $request, Product $product)
    {
        $this->authorizeProduct($product);
        $validated = $request->validate([
            'type'     => 'required|in:in,out,adjustment,return',
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string|max:200',
            'notes'    => 'nullable|string|max:500',
        ]);

        if ($validated['type'] === 'out' && $product->stock_current < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi.']);
        }

        DB::transaction(function () use ($validated, $product) {
            $stockBefore = $product->stock_current;
            $qty         = (int)$validated['quantity'];
            $stockAfter  = match($validated['type']) {
                'in'         => $stockBefore + $qty,
                'out'        => $stockBefore - $qty,
                'return'     => $stockBefore + $qty,
                'adjustment' => $qty,
                default      => $stockBefore,
            };
            $product->update(['stock_current' => $stockAfter]);
            StockMovement::create([
                'company_id'   => $product->company_id,
                'product_id'   => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'user_id'      => Auth::id(),
                'type'         => $validated['type'],
                'quantity'     => abs($stockAfter - $stockBefore) ?: $qty,
                'stock_before' => $stockBefore,
                'stock_after'  => $stockAfter,
                'reason'       => $validated['reason'] ?? null,
            ]);
        });

        return back()->with('success', 'Pergerakan stok berhasil dicatat.');
    }

    public function history(Product $product)
    {
        $this->authorizeProduct($product);
        $movements = $product->stockMovements()->with('user')->latest()->paginate(25);
        return view('stock.history', compact('product', 'movements'));
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);
        $name = $product->name;
        $product->delete();
        return redirect()->route('stock.index')->with('success', "Produk \"$name\" berhasil dihapus.");
    }

    private function authorizeProduct(Product $product): void
    {
        $user = Auth::user();
        if ($product->company_id !== $user->company_id) abort(403);
        // Admin/Sales can only edit products in their branch
        if (!$user->isOwner() && $product->branch_id !== $user->branch_id) abort(403, 'Produk ini bukan milik cabang Anda.');
    }
}
