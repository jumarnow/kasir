<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { margin: 0; background: #f8fafc; color: #0f172a; padding: 24px; }
        .invoice { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .brand { font-size: 24px; font-weight: 700; color: #4f46e5; }
        .meta { text-align: right; color: #64748b; font-size: 14px; }
        .section-title { font-size: 16px; font-weight: 600; margin-bottom: 12px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 14px; }
        th { text-align: left; background: #f1f5f9; color: #475569; font-weight: 600; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #1f2937; }
        .summary { margin-top: 24px; border-top: 1px dashed #cbd5f5; padding-top: 16px; font-size: 14px; }
        .summary div { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .summary div span:last-child { font-weight: 600; }
        .summary div.total span:last-child { font-size: 18px; color: #4f46e5; }
        .footer { margin-top: 32px; text-align: center; font-size: 12px; color: #94a3b8; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice { box-shadow: none; border: none; border-radius: 0; }
            .print-button { display: none; }
        }
        .print-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding: 10px 20px;
            background: #4f46e5;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 999px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="invoice-header">
            <div>
                <div class="brand">Kasir Modern</div>
                <div style="color:#94a3b8;font-size:13px;">Invoice penjualan resmi</div>
            </div>
            <div class="meta">
                <div>Invoice : <strong>{{ $transaction->invoice_number }}</strong></div>
                <div>Tanggal : {{ $transaction->created_at->format('d M Y, H:i') }}</div>
                <div>Kasir : {{ $transaction->user?->name ?? '—' }}</div>
            </div>
        </div>

        <div style="display:flex; gap:32px; flex-wrap:wrap;">
            <div style="flex:1; min-width:240px;">
                <div class="section-title">Ditagihkan kepada</div>
                @if ($transaction->customer)
                    <div style="color:#0f172a; font-weight:600;">{{ $transaction->customer->name }}</div>
                    <div style="color:#64748b;">{{ $transaction->customer->email }}</div>
                    <div style="color:#64748b;">{{ $transaction->customer->phone }}</div>
                    <div style="color:#94a3b8; font-size:12px; margin-top:8px;">{{ $transaction->customer->address }}</div>
                @else
                    <div style="color:#0f172a;">Pelanggan Umum</div>
                @endif
            </div>
            <div style="flex:1; min-width:240px;">
                <div class="section-title">Ringkasan</div>
                <div style="color:#64748b;">Metode : {{ strtoupper($transaction->payment_method) }}</div>
                <div style="color:#64748b;">Catatan : {{ $transaction->notes ?? '-' }}</div>
            </div>
        </div>

        <table>
            <thead>
            <tr>
                <th>Produk</th>
                <th style="text-align:center;">Jumlah</th>
                <th style="text-align:right;">Harga</th>
                <th style="text-align:right;">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($transaction->items as $item)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $item->product?->name ?? 'Produk terhapus' }}</div>
                        <div style="color:#94a3b8; font-size:12px;">SKU: {{ $item->product?->sku ?? '-' }}</div>
                    </td>
                    <td style="text-align:center;">{{ $item->quantity }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
            <div><span>Diskon</span><span>Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }} ({{ $transaction->discount_percent }}%)</span></div>
            <div class="total"><span>Total</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>
            <div><span>Dibayar</span><span>Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span></div>
            <div><span>Kembalian</span><span>Rp {{ number_format($transaction->change_due, 0, ',', '.') }}</span></div>
            <div><span>Profit</span><span style="color:#059669;">Rp {{ number_format($transaction->profit, 0, ',', '.') }}</span></div>
        </div>

        <a href="#" onclick="window.print(); return false;" class="print-button">🖨 Cetak Invoice</a>

        <div class="footer">
            Terima kasih telah berbelanja. Simpan invoice ini sebagai bukti transaksi.
        </div>
    </div>
</body>
</html>
