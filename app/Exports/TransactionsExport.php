<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithColumnWidths, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection(): Collection
    {
        return Transaction::with(['customer', 'user', 'items.product'])
            ->when($this->filters['start_date'] ?? null, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($this->filters['end_date'] ?? null, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($this->filters['search'] ?? null, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('invoice_number', 'like', '%' . $term . '%')
                      ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%' . $term . '%'))
                      ->orWhereHas('items', function ($iq) use ($term) {
                          $iq->where('custom_name', 'like', '%' . $term . '%')
                             ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', '%' . $term . '%'));
                      });
                });
            })
            ->when($this->filters['payment_status'] ?? null, function ($query, $status) {
                if ($status === 'cod_kurir') {
                    return $query->where('payment_method', 'cod_kurir');
                }

                return $query->where('payment_status', $status)
                    ->where('payment_method', '!=', 'cod_kurir');
            })
            ->when($this->filters['order_status'] ?? null, fn($query, $status) => $query->where('order_status', $status))
            ->when($this->filters['delivery_method'] ?? null, function ($query, $method) {
                if ($method === 'none') {
                    return $query->whereNull('delivery_method')->orWhere('delivery_method', '');
                }
                return $query->where('delivery_method', $method);
            })
            ->when($this->filters['user_id'] ?? null, fn($query, $userId) => $query->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Tanggal',
            'Kasir',
            'Pelanggan',
            'Item',
            'Qty Total',
            'Subtotal',
            'Diskon',
            'Ongkir',
            'Total',
            'Dibayar',
            'Sisa',
            'Status Pembayaran',
            'Status Order',
            'Jatuh Tempo',
        ];
    }

    public function map($transaction): array
    {
        $items = $transaction->items
            ->map(function ($item) {
                $name = $item->custom_name ?? $item->product?->name ?? 'Produk terhapus';
                return $name . ' x' . $item->quantity;
            })
            ->implode(', ');

        return [
            $transaction->invoice_number,
            $transaction->created_at?->format('d/m/Y H:i'),
            $transaction->user?->name ?? '-',
            $transaction->customer?->name ?? 'Umum',
            $items,
            $transaction->items->sum('quantity'),
            (float) $transaction->subtotal,
            (float) $transaction->discount_amount,
            (float) $transaction->shipping_cost,
            (float) $transaction->total,
            (float) ($transaction->dp_amount + $transaction->amount_paid),
            (float) $transaction->remaining_amount,
            $transaction->payment_method === 'cod_kurir'
                ? 'COD Kurir'
                : match ($transaction->payment_status) {
                    'paid' => 'Lunas',
                    'dp' => 'DP',
                    'unpaid' => 'Belum Dibayar',
                    default => ucfirst((string) $transaction->payment_status),
                },
            match ($transaction->order_status) {
                'pending' => 'Pending',
                'production' => 'Produksi',
                'completed' => 'Selesai',
                'delivered' => 'Terkirim',
                default => ucfirst((string) $transaction->order_status),
            },
            $transaction->due_date?->format('d/m/Y') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = max(1, $sheet->getHighestRow());

        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:O' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        $sheet->getStyle('G2:L' . $highestRow)
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->getStyle('F2:F' . $highestRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('G2:L' . $highestRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = max(1, $sheet->getHighestRow());

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:O' . $highestRow);
                $sheet->getRowDimension(1)->setRowHeight(24);

                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }
                }

                $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setWrapText(true);
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 20,
            'C' => 20,
            'D' => 24,
            'E' => 42,
            'F' => 10,
            'G' => 16,
            'H' => 16,
            'I' => 16,
            'J' => 16,
            'K' => 16,
            'L' => 16,
            'M' => 18,
            'N' => 16,
            'O' => 15,
        ];
    }
}
