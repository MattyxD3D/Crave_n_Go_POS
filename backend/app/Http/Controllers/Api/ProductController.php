<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'addOns'])
            ->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('available')) {
            $query->where('is_available', filter_var($request->available, FILTER_VALIDATE_BOOLEAN));
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $product->load(['category', 'addOns', 'combos']);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_path' => 'nullable|string',
            'stock_qty' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'add_on_ids' => 'nullable|array',
            'add_on_ids.*' => 'exists:add_ons,id',
        ]);

        $addOnIds = $validated['add_on_ids'] ?? [];
        unset($validated['add_on_ids']);

        $product = Product::create($validated);

        if (!empty($addOnIds)) {
            $product->addOns()->sync($addOnIds);
        }

        $product->load(['category', 'addOns']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'image_path' => 'nullable|string',
            'stock_qty' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'add_on_ids' => 'nullable|array',
            'add_on_ids.*' => 'exists:add_ons,id',
        ]);

        $addOnIds = $validated['add_on_ids'] ?? null;
        unset($validated['add_on_ids']);

        $product->update($validated);

        if ($addOnIds !== null) {
            $product->addOns()->sync($addOnIds);
        }

        $product->load(['category', 'addOns']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product,
        ]);
    }

    public function toggle(Product $product)
    {
        $product->update(['is_available' => !$product->is_available]);

        return response()->json([
            'success' => true,
            'message' => 'Product availability toggled.',
            'data' => [
                'id' => $product->id,
                'is_available' => $product->is_available,
            ],
        ]);
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false, 'is_available' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Product deactivated successfully.',
        ]);
    }
}
