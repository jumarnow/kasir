<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseDetailSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Get expenses
        $expenses = Expense::with(['category', 'user'])
            ->betweenDates($this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d'))
            ->orderBy('expense_date', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'tanggal' => $expense->expense_date->format('d/m/Y'),
                    'kategori' => $expense->category->name,
                    'tipe' => $this->getTypeLabel($expense->category->type),
                    'keterangan' => $expense->description,
                    'jumlah' => number_format($expense->amount, 2),
                    'dibuat_oleh' => $expense->user ? $expense->user->name : '-',
                ];
            });

        // Get paid payrolls
        $payrolls = Payroll::with('employee')
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(function ($payroll) {
                return [
                    'tanggal' => $payroll->paid_at->format('d/m/Y'),
                    'kategori' => 'Penggajian',
                    'tipe' => 'Gaji',
                    'keterangan' => 'Gaji ' . ($payroll->employee ? $payroll->employee->name : '-') .
                        ' - ' . $payroll->period_month . '/' . $payroll->period_year,
                    'jumlah' => number_format($payroll->net_salary, 2),
                    'dibuat_oleh' => 'Sistem',
                ];
            });

        // Merge and sort by date
        return $expenses->concat($payrolls)->sortByDesc('tanggal')->values();
    }

    protected function getTypeLabel($type)
    {
        return match ($type) {
            'daily' => 'Harian',
            'monthly' => 'Bulanan',
            'material' => 'Bahan Baku',
            'payroll' => 'Gaji',
            default => '-',
        };
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Tipe',
            'Keterangan',
            'Jumlah (Rp)',
            'Dibuat Oleh',
        ];
    }

    public function title(): string
    {
        return 'Detail Pengeluaran';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 15,
            'D' => 40,
            'E' => 18,
            'F' => 20,
        ];
    }
}
