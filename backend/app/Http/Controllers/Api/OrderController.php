<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Combo;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddOn;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['cashier', 'orderItems'])
            ->orderBy('created_at', 'desc');

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        $orders = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'cashier',
            'orderItems.product',
            'orderItems.combo',
            'orderItems.orderItemAddOns.addOn',
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_type'       => 'nullable|in:dine_in,takeout',
            'payment_method'   => 'nullable|in:cash,gcash',
            'amount_tendered'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'offline_id'       => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.combo_id'   => 'nullable|exists:combos,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.notes'      => 'nullable|string',
            'items.*.add_on_ids' => 'nullable|array',
            'items.*.add_on_ids.*' => 'exists:add_ons,id',
        ]);

        // Check for duplicate offline_id
        if (!empty($validated['offline_id'])) {
            $existing = Order::where('offline_id', $validated['offline_id'])->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order already synced.',
                    'data'    => $existing->load(['cashier', 'orderItems.product', 'orderItems.combo', 'orderItems.orderItemAddOns.addOn']),
                ]);
            }
        }

        try {
            $order = DB::transaction(function () use ($validated, $request) {
                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($validated['items'] as $item) {
                    $unitPrice = 0;
                    $itemName  = '';

                    if (!empty($item['product_id'])) {
                        $product = Product::findOrFail($item['product_id']);
                        if (!$product->is_available || !$product->is_active) {
                            throw new \Exception("Product '{$product->name}' is not available.");
                        }
                        if ($product->stock_qty < $item['quantity']) {
                            throw new \Exception("Insufficient stock for '{$product->name}'. Available: {$product->stock_qty}.");
                        }
                        $unitPrice = $product->price;
                        $itemName  = $product->name;
                    } elseif (!empty($item['combo_id'])) {
                        $combo = Combo::findOrFail($item['combo_id']);
                        if (!$combo->is_active) {
                            throw new \Exception("Combo '{$combo->name}' is not available.");
                        }
                        $unitPrice = $combo->price;
                        $itemName  = $combo->name;
                    } else {
                        throw new \Exception('Each order item must have a product_id or combo_id.');
                    }

                    // Add-on prices
                    $addOnTotal = 0;
                    $addOnIds   = $item['add_on_ids'] ?? [];
                    if (!empty($addOnIds)) {
                        $addOns     = AddOn::whereIn('id', $addOnIds)->where('is_active', true)->get();
                        $addOnTotal = $addOns->sum('price');
                    }

                    $lineSubtotal = ($unitPrice + $addOnTotal) * $item['quantity'];
                    $subtotal    += $lineSubtotal;

                    $itemsToCreate[] = [
                        'raw'        => $item,
                        'unit_price' => $unitPrice,
                        'subtotal'   => $lineSubtotal,
                    ];
                }

                $total         = $subtotal;
                $amountTendered = $validated['amount_tendered'] ?? null;
                $changeAmount  = $amountTendered !== null ? max(0, $amountTendered - $total) : null;

                $order = Order::create([
                    'order_number'    => Order::generateOrderNumber(),
                    'cashier_id'      => $request->user()->id,
                    'order_type'      => $validated['order_type'] ?? 'dine_in',
                    'payment_method'  => $validated['payment_method'] ?? 'cash',
                    'subtotal'        => $subtotal,
                    'total'           => $total,
                    'amount_tendered' => $amountTendered,
                    'change_amount'   => $changeAmount,
                    'status'          => 'paid',
                    'notes'           => $validated['notes'] ?? null,
                    'offline_id'      => $validated['offline_id'] ?? null,
                    'synced_at'       => !empty($validated['offline_id']) ? now() : null,
                ]);

                // Create order items, deduct inventory, log changes
                foreach ($itemsToCreate as $entry) {
                    $raw  = $entry['raw'];
                    $item = OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $raw['product_id'] ?? null,
                        'combo_id'   => $raw['combo_id'] ?? null,
                        'quantity'   => $raw['quantity'],
                        'unit_price' => $entry['unit_price'],
                        'subtotal'   => $entry['subtotal'],
                        'notes'      => $raw['notes'] ?? null,
                    ]);

                    // Add-ons for item
                    if (!empty($raw['add_on_ids'])) {
                        $addOns = AddOn::whereIn('id', $raw['add_on_ids'])->get();
                        foreach ($addOns as $addOn) {
                            OrderItemAddOn::create([
                                'order_item_id' => $item->id,
                                'add_on_id'     => $addOn->id,
                                'price'         => $addOn->price,
                            ]);
                        }
                    }

                    // Deduct product stock
                    if (!empty($raw['product_id'])) {
                        $product     = Product::find($raw['product_id']);
                        $previousQty = $product->stock_qty;
                        $newQty      = max(0, $previousQty - $raw['quantity']);

                        $product->update(['stock_qty' => $newQty]);

                        InventoryLog::create([
                            'product_id'   => $product->id,
                            'change_qty'   => -$raw['quantity'],
                            'previous_qty' => $previousQty,
                            'new_qty'      => $newQty,
                            'reason'       => 'sale',
                            'order_id'     => $order->id,
                            'user_id'      => $request->user()->id,
                        ]);
                    }
                }

                return $order;
            });

            $order->load([
                'cashier',
                'orderItems.product',
                'orderItems.combo',
                'orderItems.orderItemAddOns.addOn',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data'    => $order,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'orders'   => 'required|array|min:1',
            'orders.*' => 'array',
        ]);

        $results = [];
        $errors  = [];

        foreach ($request->orders as $index => $orderData) {
            try {
                $fakeRequest = new Request($orderData);
                $fakeRequest->setUserResolver(fn() => $request->user());
                $response = $this->store($fakeRequest);
                $body     = json_decode($response->getContent(), true);
                $results[] = $body['data'] ?? null;
            } catch (\Exception $e) {
                $errors[] = [
                    'index'   => $index,
                    'offline_id' => $orderData['offline_id'] ?? null,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' order(s) synced. ' . count($errors) . ' failed.',
            'data'    => [
                'synced' => $results,
                'errors' => $errors,
            ],
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already cancelled.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            // Restore inventory for product items
            foreach ($order->orderItems as $item) {
                if ($item->product_id) {
                    $product     = Product::find($item->product_id);
                    $previousQty = $product->stock_qty;
                    $newQty      = $previousQty + $item->quantity;

                    $product->update(['stock_qty' => $newQty]);

                    InventoryLog::create([
                        'product_id'   => $product->id,
                        'change_qty'   => $item->quantity,
                        'previous_qty' => $previousQty,
                        'new_qty'      => $newQty,
                        'reason'       => 'cancellation',
                        'order_id'     => $order->id,
                        'user_id'      => $request->user()->id,
                    ]);
                }
            }

            $order->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled and inventory restored.',
            'data'    => $order->fresh(),
        ]);
    }
}
