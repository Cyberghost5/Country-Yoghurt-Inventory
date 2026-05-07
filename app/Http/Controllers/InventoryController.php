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
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('name')->get();

        // KPI stats
        $stats = [
            'total_products' => Product::count(),
        ];

        return view('admin.inventory.index', compact('user', 'products', 'stats'));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $this->ensureAdminOrStaff($request);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'unit'          => 'required|in:carton,pack,piece,litre',
            'selling_price' => 'required|numeric|min:0',
        ]);

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
            'name'          => 'required|string|max:255',
            'unit'          => 'required|in:carton,pack,piece,litre',
            'selling_price' => 'required|numeric|min:0',
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
        if (!$request->user()?->isAdminOrStaff()) {
            abort(403);
        }
    }

    private function ensureAdmin(Request $request): void
    {
        if (!$request->user()?->isAdmin()) {
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
