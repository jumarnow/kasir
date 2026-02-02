<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - {{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
            /* Adjust for thermal printer */
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed black;
            padding-bottom: 5px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 10px;
        }

        .item {
            margin-bottom: 8px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 4px;
        }

        .item-header {
            font-weight: bold;
        }

        .item-meta {
            font-size: 10px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">SPK PRODUKSI</div>
        <div>{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
        <div>{{ $transaction->invoice_number }}</div>
    </div>

    <div class="meta">
        <strong>Customer:</strong> {{ $transaction->customer->name ?? 'Pelanggan Umum' }}<br>
        <strong>CS:</strong> {{ $transaction->user->name ?? '-' }}<br>
        <strong>Deadline:</strong> {{ $transaction->due_date ? date('d/m/Y', strtotime($transaction->due_date)) : '-' }}
    </div>

    <hr style="border-top: 1px dashed black;">

    @foreach($transaction->items as $item)
        <div class="item">
            <div class="item-header">
                {{ $item->product->name }} (x{{ $item->quantity }})
            </div>
            <div class="item-meta">
                @if($item->product->pricing_type == 'per_dimension')
                    Dimensi: {{ $item->width }}cm x {{ $item->length }}cm<br>
                    Bahan: {{ $item->product->name }}<br>
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

    <div class="footer">
        --- Internal Use Only ---
    </div>

    <script>
        window.print();
    </script>
</body>

</html>