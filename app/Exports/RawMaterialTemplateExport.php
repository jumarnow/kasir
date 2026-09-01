<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RawMaterialTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [
                'Kertas A4',
                'Rim',
                '',
                'RM-123456',
                10,
                2,
                'Kertas ukuran A4 80gsm',
            ],
            [
                'Gagang Stempel Flash',
                'Pcs',
                'BRC001',
                '',
                50,
                5,
                '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nama',
            'satuan',
            'barcode',
            'sku',
            'stok_awal',
            'batas_minimum',
            'deskripsi',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'], // Indigo-600
                ],
            ],
        ];
    }
}
