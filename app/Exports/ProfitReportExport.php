<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProfitReportExport implements WithMultipleSheets
{
    use Exportable;

    protected $month;
    protected $startDate;
    protected $endDate;

    public function __construct($month)
    {
        $this->month = $month;
        $date = Carbon::parse($month . '-01');
        $this->startDate = $date->copy()->startOfMonth();
        $this->endDate = $date->copy()->endOfMonth();
    }

    public function sheets(): array
    {
        return [
            new ProfitSummarySheet($this->month, $this->startDate, $this->endDate),
            new SalesDetailSheet($this->startDate, $this->endDate),
            new ExpenseDetailSheet($this->startDate, $this->endDate),
        ];
    }
}
