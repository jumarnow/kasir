<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProfitSummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $month;
    protected $startDate;
    protected $endDate;

    public function __construct($month, $startDate, $endDate)
    {
        $this->month = $month;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Get total sales
        $totalSales = Transaction::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Get expenses by category
        $expensesByCategory = ExpenseCategory::with([
            'expenses' => function ($query) {
                $query->betweenDates($this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d'));
            }
        ])
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'type' => $category->type,
                    'total' => $category->expenses->sum('amount'),
                ];
            })
            ->filter(function ($category) {
                return $category['total'] > 0;
            });

        // Get paid payrolls
        $totalPayroll = Payroll::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->sum('net_salary');

        if ($totalPayroll > 0) {
            $expensesByCategory->push([
                'name' => 'Penggajian',
                'type' => 'payroll',
                'total' => $totalPayroll,
            ]);
        }

        $totalExpenses = $expensesByCategory->sum('total');
        $netProfit = $totalSales - $totalExpenses;

        // Build summary data
        $data = collect([
            ['LAPORAN PROFIT BULANAN', ''],
            ['Periode', $this->startDate->format('d M Y') . ' - ' . $this->endDate->format('d M Y')],
            ['', ''],
            ['PENJUALAN', ''],
            ['Total Penjualan Kotor', number_format($totalSales, 2)],
            ['', ''],
            ['MODAL USAHA (PENGELUARAN)', ''],
        ]);

        // Add expense breakdown
        foreach ($expensesByCategory as $category) {
            $data->push([
                '  ' . $category['name'],
                number_format($category['total'], 2)
            ]);
        }

        $data->push(['', '']);
        $data->push(['Total Modal Usaha', number_format($totalExpenses, 2)]);
        $data->push(['', '']);
        $data->push(['PROFIT BERSIH', number_format($netProfit, 2)]);
        $data->push(['Persentase Profit', $totalSales > 0 ? number_format(($netProfit / $totalSales) * 100, 2) . '%' : '0%']);

        return $data;
    }

    public function headings(): array
    {
        return ['Keterangan', 'Jumlah (Rp)'];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']]],
            7 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEBEE']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 20,
        ];
    }
}
