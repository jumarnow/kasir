<?php

namespace App\Exports;

use App\Models\TransactionItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportItemsSheet implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithMapping
{
    protected string $startDate;
    protected string $endDate;
    protected ?int $userId;

    public function __construct(string $startDate, string $endDate, ?int $userId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    public function title(): string
    {
        return 'Detail Barang';
    }

    public function query()
    {
        return TransactionItem::query()
            ->with(['transaction.customer', 'product'])
            ->whereHas('transaction', function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate)
                    ->whereDate('created_at', '<=', $this->endDate);

                if ($this->userId) {
                    $query->where('user_id', $this->userId);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function map($item): array
    {
        return [
            $item->transaction->created_at->format('d/m/Y H:i'),
            $item->transaction->invoice_number,
            $item->transaction->customer->name ?? 'Pelanggan Umum',
            $item->product->name ?? '-',
            $item->product->sku ?? '-',
            $item->quantity,
            $item->dimensions ?? '-',
            $item->price,
            $item->total,
            $item->profit,
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No Invoice',
            'Customer',
            'Produk',
            'SKU',
            'Qty',
            'Dimensi',
            'Harga (Rp)',
            'Total (Rp)',
            'Profit (Rp)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
