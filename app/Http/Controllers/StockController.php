<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->company;
        $query   = $company->products()->with('warehouse');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name',  'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('brand', 'like', "%{$s}%")
            );
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->status === 'low') {
            $query->whereColumn('stock_current', '<=', 'stock_minimum');
        }

        $products      = $query->orderBy('name')->paginate(20)->withQueryString();
        $warehouses    = $company->warehouses()->where('is_active', true)->get();
        $categories    = $company->products()->distinct()->pluck('category')->filter()->sort()->values();
        $lowStockCount = $company->products()->whereColumn('stock_current', '<=', 'stock_minimum')->count();

        return view('stock.index', compact('products', 'warehouses', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $warehouses = Auth::user()->company->warehouses()->where('is_active', true)->get();

        if ($warehouses->isEmpty()) {
            return redirect()->route('stock.index')
                ->with('error', 'Buat gudang terlebih dahulu sebelum menambah produk.');
        }

        return view('stock.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $company   = Auth::user()->company;
        $validated = $request->validate([
            'warehouse_id'  => 'required|exists:warehouses,id',
            'name'          => 'required|string|max:100',
            'sku'           => 'nullable|string|max:50',
            'barcode'       => 'nullable|string|max:50',
            'category'      => 'nullable|string|max:50',
            'brand'         => 'nullable|string|max:50',
            'unit'          => 'required|string|max:20',
            'unit_per_pack' => 'nullable|integer|min:1',
            'stock_current' => 'nullable|integer|min:0',
            'stock_minimum' => 'nullable|integer|min:0',
            'buy_price'     => 'nullable|numeric|min:0',
            'sell_price'    => 'nullable|numeric|min:0',
        ]);

        // Ensure warehouse belongs to company
        $wh = $company->warehouses()->findOrFail($validated['warehouse_id']);

        DB::transaction(function () use ($validated, $company) {
            $initialStock = (int) ($validated['stock_current'] ?? 0);
            $product = $company->products()->create([
                ...$validated,
                'stock_current' => $initialStock,
                'stock_minimum' => $validated['stock_minimum'] ?? 10,
                'is_active'     => true,
            ]);

            if ($initialStock > 0) {
                StockMovement::create([
                    'company_id'   => $company->id,
                    'product_id'   => $product->id,
                    'warehouse_id' => $product->warehouse_id,
                    'user_id'      => Auth::id(),
                    'type'         => 'in',
                    'quantity'     => $initialStock,
                    'stock_before' => 0,
                    'stock_after'  => $initialStock,
                    'reason'       => 'Stok awal produk baru',
                ]);
            }
        });

        return redirect()->route('stock.index')
            ->with('success', 'Produk "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $warehouses = Auth::user()->company->warehouses()->where('is_active', true)->get();
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

        $product->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('stock.index')
            ->with('success', 'Produk "' . $product->name . '" berhasil diperbarui.');
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
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi. Stok saat ini: ' . $product->stock_current]);
        }

        DB::transaction(function () use ($validated, $product) {
            $stockBefore = $product->stock_current;
            $qty         = (int) $validated['quantity'];

            $stockAfter = match ($validated['type']) {
                'in'         => $stockBefore + $qty,
                'out'        => $stockBefore - $qty,
                'return'     => $stockBefore + $qty,
                'adjustment' => $qty,               // qty = target stock
                default      => $stockBefore,
            };

            $movedQty = abs($stockAfter - $stockBefore);

            $product->update(['stock_current' => $stockAfter]);

            StockMovement::create([
                'company_id'   => $product->company_id,
                'product_id'   => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'user_id'      => Auth::id(),
                'type'         => $validated['type'],
                'quantity'     => $movedQty ?: $qty,
                'stock_before' => $stockBefore,
                'stock_after'  => $stockAfter,
                'reason'       => $validated['reason'] ?? null,
                'notes'        => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Pergerakan stok berhasil dicatat.');
    }

    public function history(Product $product)
    {
        $this->authorizeProduct($product);

        $movements = $product->stockMovements()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('stock.history', compact('product', 'movements'));
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);
        $name = $product->name;
        $product->delete();
        return redirect()->route('stock.index')
            ->with('success', 'Produk "' . $name . '" berhasil dihapus.');
    }

    private function authorizeProduct(Product $product): void
    {
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403, 'Akses tidak diizinkan.');
        }
    }
}
