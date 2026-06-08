<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    public function index()
    {
        $addOns = AddOn::where('is_active', true)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $addOns,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $addOn = AddOn::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Add-on created successfully.',
            'data' => $addOn,
        ], 201);
    }

    public function update(Request $request, AddOn $addOn)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $addOn->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Add-on updated successfully.',
            'data' => $addOn->fresh(),
        ]);
    }

    public function destroy(AddOn $addOn)
    {
        $addOn->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Add-on deactivated successfully.',
        ]);
    }
}
