<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfitReportController extends Controller
{
    /**
     * Display the profit report
     */
    public function index(Request $request)
    {
        // Default to current month
        $month = $request->input('month', now()->format('Y-m'));
        $date = Carbon::parse($month . '-01');

        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // Get total sales for the period
        $totalSales = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Get expenses grouped by category
        $expensesByCategory = ExpenseCategory::with([
            'expenses' => function ($query) use ($startDate, $endDate) {
                $query->betweenDates($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
            }
        ])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'total' => $category->expenses->sum('amount'),
                ];
            })
            ->filter(function ($category) {
                return $category['total'] > 0;
            });

        // Get paid payrolls for the period
        $totalPayroll = \App\Models\Payroll::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('net_salary');

        // Add payroll as a category if there are paid payrolls
        if ($totalPayroll > 0) {
            $expensesByCategory->push([
                'id' => 'payroll',
                'name' => 'Penggajian',
                'type' => 'payroll',
                'total' => $totalPayroll,
            ]);
        }

        $totalExpenses = $expensesByCategory->sum('total');
        $netProfit = $totalSales - $totalExpenses;

        return view('reports.profit', compact(
            'month',
            'totalSales',
            'expensesByCategory',
            'totalExpenses',
            'netProfit',
            'startDate',
            'endDate',
            'totalPayroll'
        ));
    }

    /**
     * Export profit report to Excel
     */
    public function export(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $date = Carbon::parse($month . '-01');

        $filename = 'Laporan_Profit_' . $date->format('F_Y') . '.xlsx';

        return (new \App\Exports\ProfitReportExport($month))->download($filename);
    }
}
