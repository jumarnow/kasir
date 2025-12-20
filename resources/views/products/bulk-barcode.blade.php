<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Barcode Produk</title>
    <style>
        @page {
            size: 70mm 50mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: Arial, sans-serif;
        }
        .barcode-item {
            width: 70mm;
            height: 50mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-after: always;
            box-sizing: border-box;
            padding: 2mm;
            overflow: hidden;
        }
        .product-name {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 2mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }
        .product-price {
            font-size: 9pt;
            margin-top: 2mm;
        }
        svg {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body onload="window.print()">
    @foreach($products as $product)
    <div class="barcode-item">
        <div class="product-name">{{ $product->name }}</div>
        <svg class="barcode" data-value="{{ $product->barcode ?? $product->sku }}"></svg>
        <div class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
    </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.querySelectorAll('.barcode').forEach(function(el) {
            const val = el.getAttribute('data-value');
            if (val) {
                JsBarcode(el, val, {
                    format: 'CODE128',
                    width: 2,
                    height: 50,
                    displayValue: true,
                    fontSize: 12,
                    margin: 2
                });
            }
        });
    </script>
</body>
</html>
