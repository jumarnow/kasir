<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesDetailSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
        return Transaction::with(['customer', 'user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function map($transaction): array
    {
        return [
            $transaction->invoice_number,
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->customer ? $transaction->customer->name : 'Umum',
            number_format($transaction->subtotal, 2),
            number_format($transaction->discount, 2),
            number_format($transaction->shipping_cost ?? 0, 2),
            number_format($transaction->total, 2),
            $transaction->payment_method == 'pending' ? 'Unpaid' : $transaction->payment_method,
            $transaction->status,
            $transaction->user ? $transaction->user->name : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'Tanggal',
            'Pelanggan',
            'Subtotal',
            'Diskon',
            'Ongkir',
            'Total',
            'Metode Bayar',
            'Status',
            'Kasir',
        ];
    }

    public function title(): string
    {
        return 'Detail Penjualan';
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
            'A' => 18,
            'B' => 18,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 12,
            'J' => 20,
        ];
    }
}
