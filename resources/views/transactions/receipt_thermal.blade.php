<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota - {{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            padding: 10px;
            width: 80mm;
            -webkit-print-color-adjust: exact;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed black;
            padding-bottom: 5px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 13px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .item-name {
            font-weight: bold;
            font-size: 14px;
        }

        .total-section {
            border-top: 1px dashed black;
            margin-top: 10px;
            padding-top: 5px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ asset('logo_hitam_putih.png') }}" alt="Logo" style="max-width: 150px; margin-bottom: 5px;">
        <div class="store-name">{{ $settings['store_name'] ?? 'Kasir Percetakan' }}</div>
        <div>{{ $settings['store_address'] ?? 'Alamat Toko' }}</div>
        <div>0878 3871 6684 / 0856 6847 5244</div>
    </div>

    <div class="meta">
        <div>No: {{ $transaction->invoice_number }}</div>
        <div>Tgl: {{ $transaction->created_at->format('d/m/Y H:i') }}</div>
        <div>Kasir: {{ $transaction->user->name }}</div>
        <div>Pelanggan: {{ $transaction->customer->name ?? 'Umum' }}</div>
    </div>

    <hr style="border-top: 1px dashed black;">

    @foreach($transaction->items as $item)
        <div style="margin-bottom: 8px;">
            <div class="item-name">{{ $item->custom_name ?? $item->product?->name ?? '-' }}</div>
            <div class="item">
                <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                <span>{{ number_format($item->total, 0, ',', '.') }}</span>
            </div>
            @if($item->product?->pricing_type == 'per_dimension')
                <div style="font-size: 10px; color: #555;">
                    Dimensi: {{ $item->width }}cm x {{ $item->length }}cm
                </div>
            @endif
        </div>
    @endforeach

    <div class="total-section">
        <div class="row">
            <span>Subtotal</span>
            <span>Rp. {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($transaction->discount_amount > 0)
            <div class="row">
                <span>Diskon</span>
                <span>-Rp. {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
            </div>
        @endif
        @if($transaction->shipping_cost > 0)
            <div class="row">
                <span>Ongkir</span>
                <span>Rp. {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="row" style="font-weight: bold; margin-top: 5px;">
            <span>Total Belanja</span>
            <span>Rp. {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        @if(strtolower($transaction->payment_type) == 'tunai' || empty($transaction->payment_type))
            <div class="row" style="margin-top: 5px;">
                <span>Tunai</span>
                <span>Rp. {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Kembali</span>
                <span>Rp. {{ number_format($transaction->change_due, 0, ',', '.') }}</span>
            </div>
        @else
            <div class="row" style="margin-top: 5px;">
                <span>Non Tunai</span>
                <span>Rp. {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    @if($transaction->isPaid())
        <div style="text-align: center; margin-top: 15px; margin-bottom: 5px;">
            <span style="font-size: 22px; font-weight: bold; border: 2px dashed black; padding: 5px 15px; display: inline-block; letter-spacing: 2px;">LUNAS</span>
        </div>
    @endif

    <div class="footer">
        Terima kasih atas kunjungan Anda.<br>
        Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
    </div>


</body>

</html>