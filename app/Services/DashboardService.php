<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function salesLastSevenDays(): array
    {
        $dates = collect(range(0, 6))
            ->map(fn ($day) => Carbon::today()->subDays($day))
            ->reverse();

        $sales = Transaction::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return $dates
            ->map(function (Carbon $date) use ($sales) {
                $formatted = $date->format('Y-m-d');

                return [
                    'date' => $formatted,
                    'label' => $date->shortDayName,
                    'total' => (float) ($sales[$formatted] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    public function todaySummary(): array
    {
        $today = Carbon::today();

        $transactions = Transaction::whereDate('created_at', $today)->get();

        return [
            'sales' => (float) $transactions->sum('total'),
            'profit' => (float) $transactions->sum('profit'),
            'transactions' => $transactions->count(),
        ];
    }

    public function topProducts(int $limit = 5): array
    {
        return Product::select('products.id', 'products.name', 'products.sku', DB::raw('SUM(transaction_items.quantity) as quantity'))
            ->join('transaction_items', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.created_at', '>=', Carbon::today()->subDays(30))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get()
            ->map(static function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => (int) $product->quantity,
                ];
            })
            ->all();
    }

    public function lowStockProducts(int $limit = 5): array
    {
        return Product::select('id', 'name', 'sku', 'stock', 'stock_alert')
            ->orderByRaw('CASE WHEN stock_alert > 0 THEN stock - stock_alert ELSE stock END ASC')
            ->limit($limit)
            ->get()
            ->map(static function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => (int) $product->stock,
                    'stock_alert' => (int) $product->stock_alert,
                    'is_low' => $product->stock_alert > 0 && $product->stock <= $product->stock_alert,
                ];
            })
            ->all();
    }

    public function dashboardData(): array
    {
        return [
            'chart' => $this->salesLastSevenDays(),
            'today' => $this->todaySummary(),
            'top_products' => $this->topProducts(),
            'stock_alerts' => $this->lowStockProducts(),
        ];
    }
}
