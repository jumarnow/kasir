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

    public function dailySpkPerformance(): array
    {
        $today = Carbon::today();
        
        $employees = \App\Models\Employee::select('employees.id', 'employees.name')
            ->addSelect([
                'design_count' => \App\Models\Transaction::selectRaw('count(*)')
                    ->whereColumn('desainer_id', 'employees.id')
                    ->whereDate('created_at', $today),

                'produksi_count' => \App\Models\Transaction::selectRaw('count(*)')
                    ->where(function ($query) {
                        $query->whereColumn('eksekutor_id', 'employees.id')
                            ->orWhereColumn('eksekutor_2_id', 'employees.id');
                    })
                    ->whereDate('created_at', $today),
            ])
            ->havingRaw('(design_count + produksi_count) > 0')
            ->orderByRaw('(design_count + produksi_count) DESC')
            ->get();

        $labels = [];
        $designData = [];
        $produksiData = [];

        foreach ($employees as $employee) {
            $labels[] = $employee->name;
            $designData[] = (int) $employee->design_count;
            $produksiData[] = (int) $employee->produksi_count;
        }

        return [
            'labels' => $labels,
            'design' => $designData,
            'produksi' => $produksiData,
        ];
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

    public function lowStockRawMaterials(int $limit = 10): array
    {
        return \App\Models\RawMaterial::select('id', 'name', 'sku', 'unit', 'stock', 'min_stock')
            ->where('min_stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get()
            ->map(static function ($material) {
                return [
                    'id' => $material->id,
                    'name' => $material->name,
                    'sku' => $material->sku,
                    'unit' => $material->unit,
                    'stock' => (int) $material->stock,
                    'min_stock' => (int) $material->min_stock,
                    'is_low' => true,
                ];
            })
            ->all();
    }

    public function rawMaterialSummary(): array
    {
        return [
            'total_items' => \App\Models\RawMaterial::count(),
            'total_stock' => \App\Models\RawMaterial::sum('stock'),
        ];
    }

    public function dashboardData(): array
    {
        $stockAlerts = $this->lowStockProducts();
        $rawMaterialAlerts = $this->lowStockRawMaterials();
        $user = auth()->user();
        $isManager = $user && $user->hasRole('manager');
        $canViewSpkChart = $user && $user->hasPermission('view_spk_chart');

        return [
            'chart' => $this->salesLastSevenDays(),
            'today' => $this->todaySummary(),
            'stock_alerts' => $stockAlerts,
            'raw_material_alerts' => $rawMaterialAlerts,
            'low_stock_count' => count($stockAlerts) + count($rawMaterialAlerts),
            'spk_chart' => $canViewSpkChart ? $this->dailySpkPerformance() : null,
            'spk_3_days_chart' => $canViewSpkChart ? $this->lastThreeDaysSpkPerformance() : null,
            'is_manager' => $isManager,
            'raw_materials' => $this->rawMaterialSummary(),
        ];
    }

    public function lastThreeDaysSpkPerformance(): array
    {
        $dates = [
            Carbon::today(),
            Carbon::today()->subDays(1),
            Carbon::today()->subDays(2)
        ];
        
        $dateLabels = [];
        $dateStrings = [];
        foreach ($dates as $date) {
            if ($date->isToday()) {
                $dateLabels[] = 'Hari Ini (' . $date->format('d M') . ')';
            } else {
                $dateLabels[] = $date->format('d M');
            }
            $dateStrings[] = $date->format('Y-m-d');
        }

        $transactions = \App\Models\Transaction::select('desainer_id', 'eksekutor_id', 'eksekutor_2_id', 'created_at')
            ->where('created_at', '>=', Carbon::today()->subDays(2)->startOfDay())
            ->get();

        $employees = \App\Models\Employee::select('id', 'name')->get();
        $employeeData = [];
        
        $colors = ['#0ea5e9', '#f59e0b', '#ec4899', '#1d4ed8', '#eab308', '#9333ea', '#14b8a6', '#f43f5e', '#8b5cf6'];
        $colorIndex = 0;

        foreach ($employees as $employee) {
            $designData = [];
            $produksiData = [];
            $hasData = false;

            foreach ($dateStrings as $dateStr) {
                // Filter transactions for this date
                $dailyTrans = $transactions->filter(function($t) use ($dateStr) {
                    return Carbon::parse($t->created_at)->format('Y-m-d') === $dateStr;
                });

                $designCount = $dailyTrans->where('desainer_id', $employee->id)->count();
                $produksiCount = $dailyTrans->filter(function($t) use ($employee) {
                    return $t->eksekutor_id === $employee->id || $t->eksekutor_2_id === $employee->id;
                })->count();

                if ($designCount > 0 || $produksiCount > 0) $hasData = true;
                
                $designData[] = $designCount;
                $produksiData[] = $produksiCount;
            }

            if ($hasData) {
                // Ambil nama depan saja
                $firstName = explode(' ', trim($employee->name))[0];

                $baseColor = $colors[$colorIndex % count($colors)];
                // Convert hex to rgb to apply opacity
                $hex = ltrim($baseColor, '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                
                $designColor = "rgba($r, $g, $b, 1)";
                $produksiColor = "rgba($r, $g, $b, 0.4)";

                // Add Design Dataset
                $employeeData[] = [
                    'label' => $firstName . ' (Design)',
                    'data' => $designData,
                    'backgroundColor' => $designColor,
                    'borderColor' => $designColor,
                    'borderWidth' => 1,
                    'stack' => $firstName, // Same stack for the same employee
                ];

                // Add Produksi Dataset
                $employeeData[] = [
                    'label' => $firstName . ' (Produksi)',
                    'data' => $produksiData,
                    'backgroundColor' => $produksiColor,
                    'borderColor' => $designColor,
                    'borderWidth' => 1,
                    'stack' => $firstName, // Same stack for the same employee
                ];

                $colorIndex++;
            }
        }

        return [
            'labels' => $dateLabels,
            'datasets' => array_values($employeeData),
        ];
    }
}
