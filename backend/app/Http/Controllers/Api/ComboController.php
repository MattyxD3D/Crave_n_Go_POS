<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\Request;

class ComboController extends Controller
{
    public function index()
    {
        $combos = Combo::where('is_active', true)
            ->with(['products' => function ($query) {
                $query->withPivot('quantity');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $combos,
        ]);
    }

    public function show(Combo $combo)
    {
        if (!$combo->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Combo not found.',
            ], 404);
        }

        $combo->load(['products' => function ($query) {
            $query->withPivot('quantity');
        }]);

        return response()->json([
            'success' => true,
            'data' => $combo,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_path' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $combo = Combo::create($validated);

        foreach ($items as $item) {
            $combo->products()->attach($item['product_id'], ['quantity' => $item['quantity']]);
        }

        $combo->load(['products' => function ($query) {
            $query->withPivot('quantity');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Combo created successfully.',
            'data' => $combo,
        ], 201);
    }

    public function update(Request $request, Combo $combo)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'image_path' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $combo->update($validated);

        if ($items !== null) {
            $syncData = [];
            foreach ($items as $item) {
                $syncData[$item['product_id']] = ['quantity' => $item['quantity']];
            }
            $combo->products()->sync($syncData);
        }

        $combo->load(['products' => function ($query) {
            $query->withPivot('quantity');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Combo updated successfully.',
            'data' => $combo,
        ]);
    }

    public function destroy(Combo $combo)
    {
        $combo->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Combo deactivated successfully.',
        ]);
    }
}
