<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Resi Pengiriman {{ $transaction->invoice_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page { size: 100mm 150mm; margin: 0; }
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        body { margin: 0; background: #fff; color: #0f172a; }
        .label-container { width: 100mm; height: 150mm; padding: 5mm; border: 1px dashed #ccc; position: relative; display: flex; flex-direction: column; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 10px; color: #64748b; }
        .section { margin-bottom: 10px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 2px; border-bottom: 1px solid #eee; padding-bottom: 2px; }
        .content { font-size: 12px; font-weight: 600; line-height: 1.4; }
        .content-address { font-size: 11px; font-weight: 400; margin-top: 2px; white-space: pre-wrap; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto; border-top: 2px solid #000; padding-top: 10px; }
        .meta-item { }
        .meta-label { font-size: 9px; color: #64748b; }
        .meta-value { font-size: 11px; font-weight: bold; }
        .barcode-area { text-align: center; margin-top: 10px; }
        .note { font-size: 10px; margin-top: 5px; border: 1px solid #000; padding: 5px; border-radius: 4px; }
        
        @media print {
            body { margin: 0; padding: 0; }
            .label-container { border: none; width: 100%; height: 100%; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="label-container">
        <div class="header">
            <h1>Resi Pengiriman</h1>
            <p>{{ $settings['store_name'] ?? config('app.name', 'Kasir App') }}</p>
        </div>

        <div class="section">
            <div class="section-title">Penerima</div>
            <div class="content">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</div>
            <div class="content">{{ $transaction->customer->phone ?? '-' }}</div>
            <div class="content-address">{{ $transaction->customer->address ?? '-' }}</div>
        </div>

        <div class="section">
            <div class="section-title">Pengirim</div>
            <div class="content">{{ $settings['store_name'] ?? 'Toko Anda' }}</div>
            <div class="content">{{ $settings['store_phone'] ?? '-' }}</div>
            <div class="content-address">{{ $settings['store_address'] ?? 'Jl. Contoh No. 123, Kota Anda' }}</div>
        </div>

        @if($transaction->notes)
        <div class="section">
            <div class="section-title">Catatan</div>
            <div class="note">
                {{ $transaction->notes }}
            </div>
        </div>
        @endif

        <div class="meta-grid">
            <div class="meta-item">
                <div class="meta-label">No. Invoice</div>
                <div class="meta-value">{{ $transaction->invoice_number }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Tanggal</div>
                <div class="meta-value">{{ $transaction->created_at->format('d/m/Y') }}</div>
            </div>
            <!-- <div class="meta-item">
                <div class="meta-label">Ongkir</div>
                <div class="meta-value">Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</div>
            </div> -->
        </div>

        <div class="barcode-area">
            <!-- Placeholder for barcode if needed -->
            <div style="font-family: monospace; font-size: 14px; letter-spacing: 2px;">{{ $transaction->invoice_number }}</div>
        </div>
    </div>
</body>
</html>
