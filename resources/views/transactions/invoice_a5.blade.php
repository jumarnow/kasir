<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A5 landscape;
            margin: 0;
        }

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            padding: 20px;
            font-size: 11px;
        }

        .invoice-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
        }

        .meta {
            text-align: right;
        }

        .store-info {
            font-size: 10px;
            color: #64748b;
        }

        .customer-info {
            margin-top: 10px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            text-align: left;
            background: #f1f5f9;
            padding: 8px;
            font-weight: 600;
            font-size: 10px;
            border-bottom: 1px solid #cbd5e1;
        }

        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .totals {
            width: 200px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .totals-row.grand-total {
            font-weight: 700;
            font-size: 12px;
            color: #4f46e5;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
        }

        .signature {
            text-align: center;
            margin-top: 30px;
        }

        .signature-line {
            width: 120px;
            border-bottom: 1px solid #94a3b8;
            margin-top: 40px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 700;
        }

        .badge-paid {
            background: #dcfce7;
            color: #166534;
        }

        .badge-dp {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="header">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                @if(isset($settings['store_logo']) && $settings['store_logo'])
                    <img src="{{ asset('storage/' . $settings['store_logo']) }}" alt="Logo"
                        style="height: 50px; width: auto; object-fit: contain;">
                @else
                    <img src="{{ asset('img/logo.png') }}" alt="Logo"
                        style="height: 50px; width: auto; object-fit: contain;">
                @endif
                <div>
                    <div class="brand">{{ $settings['store_name'] ?? 'Kasir Percetakan' }}</div>
                    <div class="store-info">{{ $settings['store_address'] ?? 'Alamat Toko' }}</div>
                    <div class="store-info">{{ $settings['store_phone'] ?? '08123456789' }}</div>
                </div>
            </div>
            <div class="meta">
                <div style="font-size: 14px; font-weight: 700;">INVOICE</div>
                <div>#{{ $transaction->invoice_number }}</div>
                <div>{{ $transaction->created_at->format('d/m/Y') }}</div>
                <div style="margin-top: 4px;">
                    @if($transaction->payment_status == 'paid')
                        <span class="badge badge-paid">LUNAS</span>
                    @elseif($transaction->payment_status == 'dp')
                        <span class="badge badge-dp">DP (Kurang Bayar)</span>
                    @else
                        <span class="badge badge-unpaid">BELUM BAYAR</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="customer-info">
            <div>
                <div style="font-weight: 600; color: #475569; font-size: 9px;">DITAGIHKAN KEPADA:</div>
                <div style="font-weight: 700;">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</div>
                <div style="color: #64748b;">{{ $transaction->customer->phone ?? '-' }}</div>
            </div>
            @if($transaction->due_date && $transaction->payment_status != 'paid')
                <div style="text-align: right;">
                    <div style="font-weight: 600; color: #be123c; font-size: 9px;">JATUH TEMPO:</div>
                    <div style="font-weight: 700; color: #be123c;">{{ date('d/m/Y', strtotime($transaction->due_date)) }}
                    </div>
                </div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $item->custom_name ?? $item->product?->name ?? '-' }}</div>
                            @if($item->product?->pricing_type == 'per_dimension')
                                <div style="color: #64748b; font-size: 9px;">
                                    Dimensi: {{ $item->width }}cm x {{ $item->length }}cm ({{ $item->area }}m²)
                                </div>
                            @endif
                            @if($item->notes)
                                <div style="font-weight: 400; font-style: italic;">"{{ $item->notes }}"</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <div class="payment-info" style="width: 50%;">
                <div style="margin-bottom: 20px; font-size: 10px; color: #475569;">
                    <div style="font-weight: 600;">Rek BCA :</div>
                    <div style="font-weight: 700; font-size: 12px; margin: 2px 0;">3261602057</div>
                    <div>a/n Arif Fibriyanto</div>
                </div>
                <div class="signature">
                    <div style="margin-bottom: 40px; font-size: 10px; color: #64748b;">Hormat Kami,</div>
                    <div style="font-weight: 600;">{{ $settings['store_name'] ?? 'Admin' }}</div>
                </div>
            </div>

            <div class="totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($transaction->discount_amount > 0)
                    <div class="totals-row">
                        <span>Diskon</span>
                        <span>-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($transaction->shipping_cost > 0)
                    <div class="totals-row">
                        <span>Ongkir</span>
                        <span>Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="totals-row grand-total">
                    <span>TOTAL TAGIHAN</span>
                    <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>

                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dotted #cbd5e1;">
                    <div class="totals-row">
                        <span>Sudah Dibayar</span>
                        <span>Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->remaining_amount > 0)
                        <div class="totals-row" style="color: #be123c; font-weight: 600;">
                            <span>Sisa Tagihan</span>
                            <span>Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</span>
                        </div>
                    @elseif($transaction->change_due > 0)
                        <div class="totals-row">
                            <span>Kembali</span>
                            <span>Rp {{ number_format($transaction->change_due, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>


    </div>
</body>

</html>