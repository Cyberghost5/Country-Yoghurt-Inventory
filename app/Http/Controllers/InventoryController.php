<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $this->ensureAdminOrStaff($request);

        $user  = $request->user();
        $query = Product::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status === 'in_stock') {
                $query->whereRaw('quantity > reorder_level');
            } elseif ($status === 'low_stock') {
                $query->where('quantity', '>', 0)->whereRaw('quantity <= reorder_level');
            } elseif ($status === 'out_of_stock') {
                $query->where('quantity', 0);
            }
        }

        $products = $query->orderBy('name')->get();

        // KPI stats
        $stats = [
            'total_products'  => Product::count(),
            'total_units'     => (int) Product::sum('quantity'),
            'low_stock'       => Product::where('quantity', '>', 0)->whereRaw('quantity <= reorder_level')->count(),
            'out_of_stock'    => Product::where('quantity', 0)->count(),
            'total_value'     => Product::selectRaw('SUM(cost_price * quantity) as val')->value('val') ?? 0,
        ];

        return view('admin.inventory.index', compact('user', 'products', 'stats'));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $this->ensureAdminOrStaff($request);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'nullable|string|max:100|unique:products,sku',
            'category'       => 'required|in:yoghurt,accessories,packaging,others',
            'flavor'         => 'nullable|string|max:100',
            'size_label'     => 'nullable|string|max:50',
            'unit'           => 'required|in:carton,pack,piece,litre',
            'cost_price'     => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'quantity'       => 'required|integer|min:0',
            'reorder_level'  => 'required|integer|min:0',
            'supplier_name'  => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        // Auto-generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data['category'], $data['name']);
        }

        $data['created_by'] = $request->user()->id;

        Product::create($data);

        return redirect()->route('admin.inventory.index')
            ->with('status', 'Product "' . $data['name'] . '" added successfully.');
    }

    /* ── Update ── */
    public function update(Request $request, Product $product)
    {
        $this->ensureAdminOrStaff($request);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
            'category'       => 'required|in:yoghurt,accessories,packaging,others',
            'flavor'         => 'nullable|string|max:100',
            'size_label'     => 'nullable|string|max:50',
            'unit'           => 'required|in:carton,pack,piece,litre',
            'cost_price'     => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'quantity'       => 'required|integer|min:0',
            'reorder_level'  => 'required|integer|min:0',
            'supplier_name'  => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $product->update($data);

        return redirect()->route('admin.inventory.index')
            ->with('status', 'Product "' . $product->name . '" updated successfully.');
    }

    /* ── Adjust Stock ── */
    public function adjustStock(Request $request, Product $product)
    {
        $this->ensureAdminOrStaff($request);

        $data = $request->validate([
            'adjustment' => 'required|integer',
        ]);

        $newQty = max(0, $product->quantity + $data['adjustment']);
        $product->update(['quantity' => $newQty]);

        return redirect()->route('admin.inventory.index')
            ->with('status', 'Stock for "' . $product->name . '" adjusted to ' . $newQty . '.');
    }

    /* ── Destroy ── */
    public function destroy(Request $request, Product $product)
    {
        $this->ensureAdmin($request);

        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.inventory.index')
            ->with('status', '"' . $name . '" removed from inventory.');
    }

    /* ── Helpers ── */
    private function ensureAdminOrStaff(Request $request): void
    {
        $role = $request->user()->role ?? null;
        if (!in_array($role, ['admin', 'staff'], true)) {
            abort(403);
        }
    }

    private function ensureAdmin(Request $request): void
    {
        if (($request->user()->role ?? null) !== 'admin') {
            abort(403);
        }
    }

    private function generateSku(string $category, string $name): string
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $slug   = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $name), 0, 4));
        $rand   = strtoupper(Str::random(4));

        return "{$prefix}-{$slug}-{$rand}";
    }
}
