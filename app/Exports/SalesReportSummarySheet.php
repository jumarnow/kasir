<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportSummarySheet implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $data;
    protected array $summary;
    protected string $startDate;
    protected string $endDate;

    public function __construct(array $data, array $summary, string $startDate, string $endDate)
    {
        $this->data = $data;
        $this->summary = $summary;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function array(): array
    {
        $rows = [];

        // Data rows
        foreach ($this->data as $row) {
            $rows[] = [
                $row['label'],
                $row['sales'],
                $row['profit'],
                $row['transactions'],
            ];
        }

        // Empty row before summary
        $rows[] = ['', '', '', ''];

        // Summary rows
        $rows[] = ['RINGKASAN', '', '', ''];
        $rows[] = ['Periode', $this->startDate . ' - ' . $this->endDate, '', ''];
        $rows[] = ['Total Penjualan', $this->summary['sales'], '', ''];
        $rows[] = ['Total Profit', $this->summary['profit'], '', ''];
        $rows[] = ['Total Transaksi', $this->summary['transactions'], '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Penjualan (Rp)',
            'Profit (Rp)',
            'Transaksi',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
