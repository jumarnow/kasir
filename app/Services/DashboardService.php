<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Check if the current user should see only their own transactions
     */
    private function shouldFilterByUser(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('kasir'));
    }

    /**
     * Apply user filter to transaction query if needed
     */
    private function applyUserFilter($query)
    {
        if ($this->shouldFilterByUser()) {
            $query->where('user_id', auth()->id());
        }
        return $query;
    }

    public function salesLastSevenDays(): array
    {
        $dates = collect(range(0, 6))
            ->map(fn($day) => Carbon::today()->subDays($day))
            ->reverse();

        $query = Transaction::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay());

        $this->applyUserFilter($query);

        $sales = $query->groupBy('date')
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
        $yesterday = Carbon::yesterday();

        // Get today's transactions with user filter
        $todayQuery = Transaction::whereDate('created_at', $today);
        $this->applyUserFilter($todayQuery);
        $transactions = $todayQuery->get();
        $totalSales = (float) $transactions->sum('total');

        // Get yesterday's transaction count with user filter
        $yesterdayQuery = Transaction::whereDate('created_at', $yesterday);
        $this->applyUserFilter($yesterdayQuery);
        $yesterdayTransactions = $yesterdayQuery->count();
        $todayTransactions = $transactions->count();

        // Calculate percentage change
        $percentageChange = 0;
        if ($yesterdayTransactions > 0) {
            $percentageChange = (($todayTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100;
        } elseif ($todayTransactions > 0) {
            $percentageChange = 100; // If no transactions yesterday but have today, it's 100% increase
        }

        // Get today's expenses
        $totalExpenses = (float) \App\Models\Expense::whereDate('expense_date', $today)
            ->sum('amount');

        // Get today's paid payrolls
        $totalPayroll = (float) \App\Models\Payroll::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', $today)
            ->sum('net_salary');

        // Calculate net profit: Sales - Expenses - Payroll
        $netProfit = $totalSales - $totalExpenses - $totalPayroll;

        // Calculate average transaction value (already filtered by user if needed)
        $averageTransaction = $todayTransactions > 0 ? $totalSales / $todayTransactions : 0;

        return [
            'sales' => $totalSales,
            'profit' => $netProfit,
            'transactions' => $todayTransactions,
            'yesterday_transactions' => $yesterdayTransactions,
            'percentage_change' => round($percentageChange, 1),
            'average_transaction' => $averageTransaction,
            'expenses' => $totalExpenses,
            'payroll' => $totalPayroll,
        ];
    }

    public function topProducts(int $limit = 5): array
    {
        $query = Product::select('products.id', 'products.name', 'products.sku', DB::raw('SUM(transaction_items.quantity) as quantity'))
            ->join('transaction_items', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.created_at', '>=', Carbon::today()->subDays(30));

        // Apply user filter to transactions
        if ($this->shouldFilterByUser()) {
            $query->where('transactions.user_id', auth()->id());
        }

        return $query->groupBy('products.id', 'products.name', 'products.sku')
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

    public function lowStockProducts(int $limit = 10): array
    {
        return Product::select('id', 'name', 'sku', 'stock', 'stock_alert')
            ->where('stock_alert', '>', 0)
            ->whereColumn('stock', '<=', 'stock_alert')
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get()
            ->map(static function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => (int) $product->stock,
                    'stock_alert' => (int) $product->stock_alert,
                    'is_low' => true,
                ];
            })
            ->all();
    }

    public function dashboardData(): array
    {
        $stockAlerts = $this->lowStockProducts();

        return [
            'chart' => $this->salesLastSevenDays(),
            'today' => $this->todaySummary(),
            'top_products' => $this->topProducts(),
            'stock_alerts' => $stockAlerts,
            'low_stock_count' => count($stockAlerts),
        ];
    }
}
