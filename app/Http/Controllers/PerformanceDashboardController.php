<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'monthly'); // daily, monthly, quadmester, yearly

        $salesData = [];
        $expenseData = [];
        $transCountData = [];
        $nettProfitData = [];
        $labels = [];

        $now = Carbon::now();

        if ($period == 'daily') {
            // Last 30 days
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();

            $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'pending'])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'), DB::raw('SUM(profit) as profit'))
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $expenses = Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->select(DB::raw('expense_date as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('expense_date')
                ->get()
                ->keyBy('date');

            $payrolls = \App\Models\Payroll::whereBetween('paid_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'paid')
                ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(net_salary) as total'))
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            for ($i = 0; $i < 30; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $labels[] = Carbon::parse($date)->format('d M');
                $salesData[] = $transactions->has($date) ? $transactions[$date]->total : 0;
                
                $expenseTotal = $expenses->has($date) ? $expenses[$date]->total : 0;
                $payrollTotal = $payrolls->has($date) ? $payrolls[$date]->total : 0;
                $expenseData[] = $expenseTotal + $payrollTotal;
                $transCountData[] = $transactions->has($date) ? $transactions[$date]->count : 0;
                $nettProfitData[] = ($transactions->has($date) ? $transactions[$date]->total : 0) - $expenseTotal - $payrollTotal;
            }

        } elseif ($period == 'quadmester') {
            // Last 3 years
            $startDate = $now->copy()->subYears(2)->startOfYear();
            
            $transactions = Transaction::where('created_at', '>=', $startDate)
                ->whereIn('status', ['completed', 'pending'])
                ->get();
                
            $expenses = Expense::where('expense_date', '>=', $startDate->format('Y-m-d'))->get();
            $payrolls = \App\Models\Payroll::where('paid_at', '>=', $startDate->format('Y-m-d'))
                ->where('status', 'paid')
                ->get();

            $groupedSales = [];
            $groupedExpenses = [];
            $groupedTransCount = [];
            $groupedProfit = [];

            for ($y = $startDate->year; $y <= $now->year; $y++) {
                for ($q = 1; $q <= 3; $q++) { // 3 quadmester in a year (4 months each)
                    $key = $y . '-Q' . $q;
                    $labels[] = 'Q' . $q . ' ' . $y . ' (Bln ' . (($q-1)*4 + 1) . '-' . ($q*4) . ')';
                    $groupedSales[$key] = 0;
                    $groupedExpenses[$key] = 0;
                    $groupedTransCount[$key] = 0;
                    $groupedProfit[$key] = 0;
                }
            }

            foreach ($transactions as $t) {
                $y = $t->created_at->year;
                $q = ceil($t->created_at->month / 4);
                $key = $y . '-Q' . $q;
                if (isset($groupedSales[$key])) {
                    $groupedSales[$key] += $t->total;
                    $groupedTransCount[$key]++;
                    $groupedProfit[$key] += $t->total;
                }
            }

            foreach ($expenses as $e) {
                $date = Carbon::parse($e->expense_date);
                $y = $date->year;
                $q = ceil($date->month / 4);
                $key = $y . '-Q' . $q;
                if (isset($groupedExpenses[$key])) {
                    $groupedExpenses[$key] += $e->amount;
                    $groupedProfit[$key] -= $e->amount;
                }
            }

            foreach ($payrolls as $p) {
                $date = Carbon::parse($p->paid_at);
                $y = $date->year;
                $q = ceil($date->month / 4);
                $key = $y . '-Q' . $q;
                if (isset($groupedExpenses[$key])) {
                    $groupedExpenses[$key] += $p->net_salary;
                    $groupedProfit[$key] -= $p->net_salary;
                }
            }

            $salesData = array_values($groupedSales);
            $expenseData = array_values($groupedExpenses);
            $transCountData = array_values($groupedTransCount);
            $nettProfitData = array_values($groupedProfit);

        } elseif ($period == 'yearly') {
            // Last 5 years
            $startDate = $now->copy()->subYears(4)->startOfYear();
            
            $transactions = Transaction::where('created_at', '>=', $startDate)
                ->whereIn('status', ['completed', 'pending'])
                ->select(DB::raw('YEAR(created_at) as year'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'), DB::raw('SUM(profit) as profit'))
                ->groupBy('year')
                ->get()
                ->keyBy('year');
                
            $expenses = Expense::where('expense_date', '>=', $startDate->format('Y-m-d'))
                ->select(DB::raw('YEAR(expense_date) as year'), DB::raw('SUM(amount) as total'))
                ->groupBy('year')
                ->get()
                ->keyBy('year');

            $payrolls = \App\Models\Payroll::where('paid_at', '>=', $startDate->format('Y-m-d'))
                ->where('status', 'paid')
                ->select(DB::raw('YEAR(paid_at) as year'), DB::raw('SUM(net_salary) as total'))
                ->groupBy('year')
                ->get()
                ->keyBy('year');

            for ($i = 0; $i < 5; $i++) {
                $year = $startDate->copy()->addYears($i)->year;
                $labels[] = $year;
                $salesData[] = $transactions->has($year) ? $transactions[$year]->total : 0;
                
                $expenseTotal = $expenses->has($year) ? $expenses[$year]->total : 0;
                $payrollTotal = $payrolls->has($year) ? $payrolls[$year]->total : 0;
                $expenseData[] = $expenseTotal + $payrollTotal;
                $transCountData[] = $transactions->has($year) ? $transactions[$year]->count : 0;
                $nettProfitData[] = ($transactions->has($year) ? $transactions[$year]->total : 0) - $expenseTotal - $payrollTotal;
            }

        } else {
            // monthly (default) - Last 12 months
            $startDate = $now->copy()->subMonths(11)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();

            $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'pending'])
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'), DB::raw('SUM(profit) as profit'))
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $expenses = Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->select(DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $payrolls = \App\Models\Payroll::whereBetween('paid_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'paid')
                ->select(DB::raw('DATE_FORMAT(paid_at, "%Y-%m") as month'), DB::raw('SUM(net_salary) as total'))
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            for ($i = 0; $i < 12; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $monthKey = $date->format('Y-m');
                $labels[] = $date->format('M Y');
                $salesData[] = $transactions->has($monthKey) ? $transactions[$monthKey]->total : 0;
                
                $expenseTotal = $expenses->has($monthKey) ? $expenses[$monthKey]->total : 0;
                $payrollTotal = $payrolls->has($monthKey) ? $payrolls[$monthKey]->total : 0;
                $expenseData[] = $expenseTotal + $payrollTotal;
                $transCountData[] = $transactions->has($monthKey) ? $transactions[$monthKey]->count : 0;
                $nettProfitData[] = ($transactions->has($monthKey) ? $transactions[$monthKey]->total : 0) - $expenseTotal - $payrollTotal;
            }
        }

        // Employee Performance (Match the period's start date)
        $perfStartDate = isset($startDate) ? $startDate : $now->copy()->subDays(30)->startOfDay();
        $perfEndDate = isset($endDate) ? $endDate : $now->copy()->endOfDay();

        $employeePerformance = Transaction::where('created_at', '>=', $perfStartDate)
            ->where('created_at', '<=', $perfEndDate)
            ->whereNotNull('user_id')
            ->whereIn('status', ['completed', 'pending'])
            ->select('user_id', DB::raw('COUNT(id) as total_transactions'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get();

        $empLabels = [];
        $empSalesData = [];
        $empTransData = [];

        foreach ($employeePerformance as $emp) {
            $empLabels[] = $emp->user ? $emp->user->name : 'Unknown';
            $empSalesData[] = $emp->total_sales;
            $empTransData[] = $emp->total_transactions;
        }

        // SPK Employee Performance
        $spkEmployees = \App\Models\Employee::select('employees.*')
            ->addSelect([
                'design_count' => \App\Models\Transaction::selectRaw('count(*)')
                    ->whereColumn('desainer_id', 'employees.id')
                    ->whereDate('created_at', '>=', $perfStartDate)
                    ->whereDate('created_at', '<=', $perfEndDate),

                'produksi_count' => \App\Models\Transaction::selectRaw('count(*)')
                    ->where(function ($query) {
                        $query->whereColumn('eksekutor_id', 'employees.id')
                            ->orWhereColumn('eksekutor_2_id', 'employees.id');
                    })
                    ->whereDate('created_at', '>=', $perfStartDate)
                    ->whereDate('created_at', '<=', $perfEndDate),
            ])
            ->havingRaw('(design_count + produksi_count) > 0')
            ->orderByRaw('(design_count + produksi_count) DESC')
            ->get();

        $spkLabels = [];
        $spkDesignData = [];
        $spkProduksiData = [];

        foreach ($spkEmployees as $emp) {
            $spkLabels[] = $emp->name;
            $spkDesignData[] = $emp->design_count;
            $spkProduksiData[] = $emp->produksi_count;
        }

        return view('reports.performance', compact(
            'period', 
            'labels', 
            'salesData', 
            'expenseData',
            'transCountData',
            'nettProfitData',
            'empLabels',
            'empSalesData',
            'empTransData',
            'spkLabels',
            'spkDesignData',
            'spkProduksiData'
        ));
    }
}
