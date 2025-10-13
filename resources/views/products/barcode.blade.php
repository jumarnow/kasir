@extends('layouts.app')

@section('title', 'Barcode Produk')
@section('subtitle', 'Cetak barcode untuk produk')

@section('content')
    <div class="max-w-xl mx-auto rounded-2xl bg-white p-8 shadow-sm border border-slate-200">
        <div class="text-center space-y-3">
            <h2 class="text-xl font-semibold text-slate-800">{{ $product->name }}</h2>
            <p class="text-sm text-slate-500">SKU: {{ $product->sku }}</p>
            <p class="text-sm font-medium text-slate-600">Harga: Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            <div class="mt-6 flex justify-center">
                <svg id="barcode"></svg>
            </div>
            <p class="text-xs text-slate-400">Use Ctrl+P / Cmd+P untuk mencetak barcode ini.</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                Kembali ke daftar produk
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        const barcodeValue = "{{ $product->barcode ?? $product->sku }}";
        if (barcodeValue) {
            JsBarcode('#barcode', barcodeValue, {
                format: 'CODE128',
                width: 2,
                height: 80,
                displayValue: true,
                fontSize: 14,
            });
        }
    </script>
@endpush
