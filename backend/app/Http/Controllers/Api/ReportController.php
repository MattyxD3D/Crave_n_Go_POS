<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $orders = Order::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->get();

        $totalRevenue  = $orders->sum('total');
        $totalOrders   = $orders->count();
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        // Orders grouped by hour using SQLite-compatible strftime
        $ordersByHour = Order::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, COUNT(*) as count, SUM(total) as revenue")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Fill all 24 hours
        $hourlyData = collect(range(0, 23))->map(function ($hour) use ($ordersByHour) {
            $found = $ordersByHour->firstWhere('hour', $hour);
            return [
                'hour'    => $hour,
                'label'   => sprintf('%02d:00', $hour),
                'count'   => $found ? (int) $found->count : 0,
                'revenue' => $found ? (float) $found->revenue : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'date'            => $date,
                'total_revenue'   => (float) $totalRevenue,
                'total_orders'    => $totalOrders,
                'avg_order_value' => (float) $avgOrderValue,
                'orders_by_hour'  => $hourlyData,
            ],
        ]);
    }

    public function topProducts(Request $request)
    {
        $limit     = $request->get('limit', 10);
        $startDate = $request->get('start_date', today()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', today()->toDateString());

        $topProducts = OrderItem::with('product')
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'paid')
                  ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            })
            ->whereNotNull('product_id')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, COUNT(*) as order_count')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id'    => $item->product_id,
                    'product_name'  => $item->product?->name,
                    'total_qty'     => (int) $item->total_qty,
                    'total_revenue' => (float) $item->total_revenue,
                    'order_count'   => (int) $item->order_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'top_products' => $topProducts,
            ],
        ]);
    }

    public function hourly(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $hourlyData = Order::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, COUNT(*) as order_count, SUM(total) as revenue")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $filled = collect(range(0, 23))->map(function ($hour) use ($hourlyData) {
            $found = $hourlyData->firstWhere('hour', $hour);
            return [
                'hour'        => $hour,
                'label'       => sprintf('%02d:00 - %02d:59', $hour, $hour),
                'order_count' => $found ? (int) $found->order_count : 0,
                'revenue'     => $found ? (float) $found->revenue : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'date'   => $date,
                'hourly' => $filled,
            ],
        ]);
    }
}
