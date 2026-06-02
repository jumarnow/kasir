<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - {{ $transaction->invoice_number }}</title>
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

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 13px;
        }

        .item {
            margin-bottom: 8px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 4px;
        }

        .item-header {
            font-weight: bold;
            font-size: 14px;
        }

        .item-meta {
            font-size: 12px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
        }

        .qc-container {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 12px;
            font-weight: bold;
        }

        .qc-list {
            flex: 1;
        }

        .qc-item {
            margin-bottom: 2px;
        }

        .sign-box {
            border: 1px solid black;
            width: 80px;
            height: 40px;
            text-align: center;
            padding-top: 2px;
            margin-left: 10px;
            position: relative;
        }

        .sign-box span {
            display: block;
            border-bottom: 1px solid transparent;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">SPK PRODUKSI</div>
        <div>{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
        <div>{{ $transaction->invoice_number }}</div>
        <div id="qrcode" style="display: flex; justify-content: center; margin-top: 10px; margin-bottom: 5px;"></div>
    </div>

    <div class="meta">
        <strong>Customer:</strong> {{ $transaction->customer->name ?? 'Pelanggan Umum' }}<br>
        <strong>CS:</strong> {{ $transaction->user->name ?? '-' }}<br>
        <strong>Eksekutor:</strong> {{ $transaction->eksekutor->name ?? '-' }}
        @if($transaction->eksekutorTwo)
            / {{ $transaction->eksekutorTwo->name }}
        @endif
        <br>
        <strong>Deadline:</strong> {{ $transaction->due_date ? date('d/m/Y', strtotime($transaction->due_date)) : '-' }}
    </div>

    <hr style="border-top: 1px dashed black;">

    @foreach($transaction->items as $item)
        <div class="item">
            <div class="item-header">
                {{ $item->custom_name ?? $item->product?->name ?? '-' }} (x{{ $item->quantity }})
            </div>
            <div class="item-meta">
                @if($item->product?->pricing_type == 'per_dimension')
                    Dimensi: {{ $item->width }}cm x {{ $item->length }}cm<br>
                    Bahan: {{ $item->custom_name ?? $item->product?->name ?? '-' }}<br>
                @endif
                @if($item->notes)
                    Catatan: {{ $item->notes }}<br>
                @endif
            </div>
        </div>
    @endforeach

    @if($transaction->files->where('file_type', 'design')->count() > 0)
        <div style="margin-top: 10px;">
            <strong>File Desain:</strong>
            @foreach($transaction->files->where('file_type', 'design') as $file)
                <div style="font-size: 10px;">- {{ $file->original_name }}</div>
            @endforeach
        </div>
    @endif

    <div class="qc-container">
        <div class="qc-list">
            <div style="margin-bottom: 2px;">QC CHECKLIST</div>
            <div class="qc-item">&#9744; BAHAN CETAK</div>
            <div class="qc-item">&#9744; UKURAN</div>
            <div class="qc-item">&#9744; HASIL CETAK</div>
            <div class="qc-item">&#9744; HASIL FINISHING</div>
        </div>
        <div class="sign-box">
            <span>PEMERIKSA</span>
        </div>
    </div>

    <div class="footer">
        --- Internal Use Only ---
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $transaction->invoice_number }}",
            width: 80,
            height: 80,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        // Tunggu QR Code dirender sebelum diprint
        setTimeout(() => {
            window.print();
        }, 300);
    </script>
</body>

</html>