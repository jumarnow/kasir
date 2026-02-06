<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
{
    use Exportable;

    protected array $data;
    protected array $summary;
    protected string $startDate;
    protected string $endDate;
    protected ?int $userId;

    public function __construct(array $data, array $summary, string $startDate, string $endDate, ?int $userId = null)
    {
        $this->data = $data;
        $this->summary = $summary;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    public function sheets(): array
    {
        return [
            'Ringkasan' => new SalesReportSummarySheet($this->data, $this->summary, $this->startDate, $this->endDate),
            'Detail Barang' => new SalesReportItemsSheet($this->startDate, $this->endDate, $this->userId),
        ];
    }
}
