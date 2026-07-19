<?php

namespace App\Exports;

use App\Models\ProductionTracking;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringStatusExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithColumnWidths
{
    protected $transactions;
    protected $mergeRanges = [];

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function array(): array
    {
        $data = [];
        $currentRow = 2; // Row 1 is header

        foreach ($this->transactions as $trx) {
            $itemCount = $trx->items->count();
            
            // If transaction has multiple items, we record the range to merge for Invoice (A) and Customer (B)
            if ($itemCount > 1) {
                $endRow = $currentRow + $itemCount - 1;
                $this->mergeRanges[] = [
                    'start' => $currentRow,
                    'end' => $endRow,
                ];
            }

            foreach ($trx->items as $item) {
                $designIn = $item->trackings->where('type', ProductionTracking::TYPE_DESIGN_IN)->last();
                $designOut = $item->trackings->where('type', ProductionTracking::TYPE_DESIGN_OUT)->last();
                $productionIn = $item->trackings->where('type', ProductionTracking::TYPE_PRODUCTION_IN)->last();
                $productionOut = $item->trackings->where('type', ProductionTracking::TYPE_PRODUCTION_OUT)->last();

                $status = '';
                if ($item->status === 'finished' || $item->pickup_method) {
                    if ($item->pickup_method === 'customer') {
                        $status = 'Diambil Customer';
                    } elseif ($item->pickup_method === 'kurir') {
                        $status = 'Diambil Kurir';
                    } elseif ($item->pickup_method === 'diantar') {
                        $status = 'Diantar ke Lokasi';
                    } else {
                        $status = 'Selesai';
                    }
                    if ($item->picked_up_at) {
                        $status .= "\n" . \Carbon\Carbon::parse($item->picked_up_at)->format('d M Y, H:i');
                    }
                } else {
                    $status = ucfirst($item->status);
                }

                $data[] = [
                    $trx->invoice_number,
                    $trx->customer ? $trx->customer->name : 'Walk-in Customer',
                    $item->product?->name ?? $item->custom_name,
                    $item->quantity,
                    $trx->created_at->format('d M Y, H:i'),
                    $designIn ? $designIn->tracked_at->format('d M Y, H:i') . "\n(" . ($designIn->user->name ?? 'Designer') . ")" : '-',
                    $designOut ? $designOut->tracked_at->format('d M Y, H:i') . "\n(" . ($designOut->user->name ?? 'Designer') . ")" : '-',
                    $productionIn ? $productionIn->tracked_at->format('d M Y, H:i') . "\n(" . ($productionIn->user->name ?? 'Produksi') . ")" : '-',
                    $productionOut ? $productionOut->tracked_at->format('d M Y, H:i') . "\n(" . ($productionOut->user->name ?? 'Produksi') . ")" : '-',
                    $status,
                ];
                
                $currentRow++;
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No Invoice',
            'Nama Customer',
            'Deskripsi Produk',
            'Qty',
            'Tanggal Pembuatan',
            'Start Design',
            'Finish Design',
            'Start Production',
            'Finish Production',
            'Status'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, // Invoice
            'B' => 25, // Customer
            'C' => 30, // Product
            'D' => 8,  // Qty
            'E' => 18, // Tanggal Pembuatan
            'F' => 22, // Start Design
            'G' => 22, // Finish Design
            'H' => 22, // Start Production
            'I' => 22, // Finish Production
            'J' => 20, // Status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'], // Indigo 600
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            // Wrap text and vertical align top for all data cells
            $sheet->getStyle("A1:J{$highestRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("A1:J{$highestRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            // Add borders to all cells
            $sheet->getStyle("A1:J{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCCCCCC'],
                    ],
                ],
            ]);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells for Invoice and Customer
                foreach ($this->mergeRanges as $range) {
                    $start = $range['start'];
                    $end = $range['end'];
                    $sheet->mergeCells("A{$start}:A{$end}");
                    $sheet->mergeCells("B{$start}:B{$end}");
                }
            },
        ];
    }
}
