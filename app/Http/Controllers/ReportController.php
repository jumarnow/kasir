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
        $users = User::orderBy('name')->get(['id', 'name']);
        $report = $this->reportService->aggregate(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['group_by'] ?? 'day',
            isset($filters['user_id']) ? (int) $filters['user_id'] : null
        );

        return view('reports.sales', [
            'report' => $report,
            'filters' => $filters,
            'users' => $users,
        ]);
    }

    public function profit(ReportFilterRequest $request)
    {
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
