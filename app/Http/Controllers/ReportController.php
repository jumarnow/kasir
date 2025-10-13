<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function sales(ReportFilterRequest $request)
    {
        $filters = $request->validated();
        $report = $this->reportService->aggregate(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['group_by'] ?? 'day'
        );

        return view('reports.sales', [
            'report' => $report,
            'filters' => $filters,
        ]);
    }

    public function profit(ReportFilterRequest $request)
    {
        $filters = $request->validated();
        $report = $this->reportService->aggregate(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['group_by'] ?? 'day'
        );

        return view('reports.profit', [
            'report' => $report,
            'filters' => $filters,
        ]);
    }
}

