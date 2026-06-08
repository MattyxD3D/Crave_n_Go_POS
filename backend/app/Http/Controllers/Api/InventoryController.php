<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id'                  => $product->id,
                    'name'                => $product->name,
                    'category'            => $product->category?->name,
                    'stock_qty'           => $product->stock_qty,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'is_low_stock'        => $product->isLowStock(),
                    'is_available'        => $product->is_available,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'change_qty' => 'required|integer',
            'reason'     => 'required|string|max:255',
        ]);

        $previousQty = $product->stock_qty;
        $newQty      = max(0, $previousQty + $validated['change_qty']);

        $product->update(['stock_qty' => $newQty]);

        $log = InventoryLog::create([
            'product_id'   => $product->id,
            'change_qty'   => $validated['change_qty'],
            'previous_qty' => $previousQty,
            'new_qty'      => $newQty,
            'reason'       => $validated['reason'],
            'order_id'     => null,
            'user_id'      => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully.',
            'data'    => [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'previous_qty' => $previousQty,
                'new_qty'      => $newQty,
                'change_qty'   => $validated['change_qty'],
                'log'          => $log,
            ],
        ]);
    }

    public function logs(Product $product)
    {
        $logs = InventoryLog::with(['user', 'order'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }
}
