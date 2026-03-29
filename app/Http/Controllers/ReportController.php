<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\User;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function sales(ReportFilterRequest $request)
    {
        $filters = $request->validated();

        // Konversi filter bulan menjadi rentang tanggal
        if (!empty($filters['month'])) {
            $monthCarbon = \Carbon\Carbon::parse($filters['month'] . '-01');
            $filters['start_date'] = $monthCarbon->copy()->startOfMonth()->toDateString();
            $filters['end_date'] = $monthCarbon->copy()->endOfMonth()->toDateString();
        }

        $users = User::orderBy('name')->get(['id', 'name']);
        $report = $this->reportService->aggregate(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['group_by'] ?? 'day',
            isset($filters['user_id']) ? (int) $filters['user_id'] : null
        );

        // Tambahkan key 'month' ke range agar bisa jadi default di view
        $report['range']['month'] = \Carbon\Carbon::parse($report['range']['start'])->format('Y-m');

        $startDate = $filters['start_date'] ?? $report['range']['start'];
        $endDate = $filters['end_date'] ?? $report['range']['end'];

        $employees = \App\Models\Employee::select('employees.*')
            ->addSelect([
                'transactions_count' => \App\Models\Transaction::selectRaw('count(*)')
                    ->where(function ($query) {
                        $query->whereColumn('eksekutor_id', 'employees.id')
                            ->orWhereColumn('eksekutor_2_id', 'employees.id');
                    })
                    ->when($startDate, fn($query) => $query->whereDate('created_at', '>=', $startDate))
                    ->when($endDate, fn($query) => $query->whereDate('created_at', '<=', $endDate))
            ])
            ->having('transactions_count', '>', 0)
            ->orderByDesc('transactions_count')
            ->get();

        return view('reports.sales', [
            'report' => $report,
            'filters' => $filters,
            'users' => $users,
            'employees' => $employees,
        ]);
    }

    public function exportSalesExcel(ReportFilterRequest $request)
    {
        $filters = $request->validated();

        // Konversi filter bulan menjadi rentang tanggal
        if (!empty($filters['month'])) {
            $monthCarbon = \Carbon\Carbon::parse($filters['month'] . '-01');
            $filters['start_date'] = $monthCarbon->copy()->startOfMonth()->toDateString();
            $filters['end_date'] = $monthCarbon->copy()->endOfMonth()->toDateString();
        }

        $report = $this->reportService->aggregate(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['group_by'] ?? 'day',
            isset($filters['user_id']) ? (int) $filters['user_id'] : null
        );

        $filename = 'laporan-penjualan-' . ($report['range']['start'] ?? now()->format('Y-m-d')) . '.xlsx';
        $userId = isset($filters['user_id']) ? (int) $filters['user_id'] : null;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalesReportExport(
                $report['data'],
                $report['summary'],
                $report['range']['start'],
                $report['range']['end'],
                $userId
            ),
            $filename
        );
    }

    public function profit(ReportFilterRequest $request)
    {
        if (!auth()->user()->hasPermission('view_profit')) {
            abort(403);
        }

        $filters = $request->validated();
        $users = User::orderBy('name')->get(['id', 'name']);
        $report = $this->reportService->aggregate(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['group_by'] ?? 'day',
            isset($filters['user_id']) ? (int) $filters['user_id'] : null
        );

        return view('reports.profit', [
            'report' => $report,
            'filters' => $filters,
            'users' => $users,
        ]);
    }
}
