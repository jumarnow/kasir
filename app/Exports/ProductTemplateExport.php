<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'Contoh Produk',
                'SKU-001',
                '1234567890123',
                'Minuman',
                'Botol',
                15000,
                13000,
                12000,
                12000,
                50,
                10,
                'Air mineral ukuran 600 ml',
                'ya',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nama',
            'sku',
            'barcode',
            'kategori',
            'satuan',
            'harga_jual_1',
            'harga_jual_2',
            'harga_jual_3',
            'harga_modal',
            'stok',
            'stok_minimum',
            'deskripsi',
            'aktif',
        ];
    }
}

