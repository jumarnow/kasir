<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index()
    {
        $data = $this->dashboardService->dashboardData();

        return view('dashboard.index', compact('data'));
    }
}
