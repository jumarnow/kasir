<table>
    <thead>
        <tr>
            <th colspan="6" style="font-weight: bold; font-size: 14px;">Laporan Detail Performa Pegawai (SPK)</th>
        </tr>
        <tr>
            <th colspan="6">Periode: {{ $startDate ?? 'Semua Waktu' }} s/d {{ $endDate ?? 'Semua Waktu' }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">Nama Pegawai</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">Jenis Pekerjaan</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">No. Invoice / SPK</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">Pelanggan</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">Keterangan / Produk</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td style="border: 1px solid #000000;">{{ $row['employee'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['type'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['date'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['invoice'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['customer'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['detail'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
