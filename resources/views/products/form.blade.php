@extends('layouts.app')

@php
    $isEdit = isset($product);
    $title = $isEdit ? 'Edit Produk' : 'Tambah Produk';
    $subtitle = $isEdit ? 'Perbarui informasi produk dan stok' : 'Input detail produk baru dan kelola stok';
    $formAction = $isEdit ? route('products.update', $product) : route('products.store');
    $selectedCategory = old('category_id', $isEdit ? $product->category_id : '');
    $isActive = old('is_active', $isEdit ? $product->is_active : true);
@endphp

@section('title', $title)
@section('subtitle', $subtitle)

@section('head')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ $formAction }}" method="POST" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Nama Produk</label>
                        <div class="relative">
                            <input
                                type="text"
                                name="name"
                                id="product-name"
                                value="{{ old('name', $isEdit ? $product->name : '') }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 pr-32 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                required
                            >
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                <input type="file" id="ocr-upload" accept="image/*" class="hidden">
                                <button
                                    type="button"
                                    id="ocr-button"
                                    class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100 transition-colors"
                                >
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    OCR
                                </button>
                            </div>
                        </div>
                        <p id="ocr-status" class="mt-1 text-xs text-slate-400"></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Kategori</label>
                        <select
                            name="category_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected($selectedCategory == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">SKU</label>
                        <input
                            type="text"
                            name="sku"
                            value="{{ old('sku', $isEdit ? $product->sku : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Barcode</label>
                        <div class="relative">
                            <input
                                type="text"
                                name="barcode"
                                id="barcode-input"
                                value="{{ old('barcode', $isEdit ? $product->barcode : '') }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 pr-28 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            >
                            <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                <button
                                    type="button"
                                    id="scan-barcode-button"
                                    class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-100 transition-colors"
                                >
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                    Scan
                                </button>
                            </div>
                        </div>
                        <p id="barcode-status" class="mt-1 text-xs text-slate-400"></p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Harga Jual</label>
                        <input
                            type="text"
                            min="0"
                            name="price"
                            value="{{ old('price', $isEdit ? formatNumber($product->price) : '') }}"
                            class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Harga Modal</label>
                        <input
                            type="text"
                            min="0"
                            name="cost_price"
                            value="{{ old('cost_price', $isEdit ? formatNumber($product->cost_price) : '') }}"
                            class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                        <p class="mt-1 text-xs text-slate-400">Kosongkan jika sama dengan harga jual.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">{{ $isEdit ? 'Stok' : 'Stok Awal' }}</label>
                            <input
                                type="number"
                                min="0"
                                name="stock"
                                value="{{ old('stock', $isEdit ? $product->stock : 0) }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            >
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Alert Stok</label>
                            <input
                                type="number"
                                min="0"
                                name="stock_alert"
                                value="{{ old('stock_alert', $isEdit ? $product->stock_alert : 0) }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Satuan</label>
                        <input
                            type="text"
                            name="unit"
                            value="{{ old('unit', $isEdit ? $product->unit : 'pcs') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-600">Deskripsi</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $isEdit ? $product->description : '') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    {{ $isActive ? 'checked' : '' }}
                >
                <span class="text-sm text-slate-600">Produk aktif</span>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Barcode Scanner Modal -->
    <div id="barcode-scanner-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
            <div class="flex items-center justify-between p-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Scan Barcode</h3>
                <button type="button" id="close-scanner" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <div id="barcode-reader" class="w-full rounded-lg overflow-hidden"></div>
                <p class="mt-3 text-sm text-slate-600 text-center">Arahkan kamera ke barcode produk</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // OCR Feature
            const ocrButton = document.getElementById('ocr-button');
            const ocrUpload = document.getElementById('ocr-upload');
            const productNameInput = document.getElementById('product-name');
            const ocrStatus = document.getElementById('ocr-status');

            ocrButton.addEventListener('click', function() {
                ocrUpload.click();
            });

            ocrUpload.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validasi file
                if (!file.type.startsWith('image/')) {
                    ocrStatus.textContent = 'File harus berupa gambar';
                    ocrStatus.className = 'mt-1 text-xs text-red-500';
                    return;
                }

                // Show loading state
                ocrButton.disabled = true;
                ocrButton.innerHTML = '<svg class="w-4 h-4 inline-block animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
                ocrStatus.textContent = 'Memproses gambar...';
                ocrStatus.className = 'mt-1 text-xs text-indigo-600';

                try {
                    const formData = new FormData();
                    formData.append('produk', file);

                    const response = await fetch('{{ route('products.ocr') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.nama) {
                        productNameInput.value = data.nama;
                        ocrStatus.textContent = 'Berhasil membaca nama produk dari gambar';
                        ocrStatus.className = 'mt-1 text-xs text-green-600';
                    } else {
                        throw new Error(data.message || 'Gagal memproses gambar');
                    }
                } catch (error) {
                    ocrStatus.textContent = error.message || 'Terjadi kesalahan saat memproses gambar';
                    ocrStatus.className = 'mt-1 text-xs text-red-500';
                } finally {
                    // Reset button
                    ocrButton.disabled = false;
                    ocrButton.innerHTML = '<svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> OCR';
                    // Reset file input
                    ocrUpload.value = '';
                }
            });

            // Barcode Scanner Feature
            const scanBarcodeButton = document.getElementById('scan-barcode-button');
            const barcodeInput = document.getElementById('barcode-input');
            const barcodeStatus = document.getElementById('barcode-status');
            const scannerModal = document.getElementById('barcode-scanner-modal');
            const closeScannerButton = document.getElementById('close-scanner');
            let html5QrCode = null;

            scanBarcodeButton.addEventListener('click', function() {
                scannerModal.classList.remove('hidden');
                startBarcodeScanner();
            });

            closeScannerButton.addEventListener('click', function() {
                stopBarcodeScanner();
            });

            function startBarcodeScanner() {
                html5QrCode = new Html5Qrcode("barcode-reader");
                
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 150 },
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E
                    ]
                };

                // Coba gunakan kamera belakang dulu
                Html5Qrcode.getCameras().then(cameras => {
                    if (cameras && cameras.length) {
                        // Pilih kamera belakang jika ada, atau kamera pertama
                        const cameraId = cameras.length > 1 ? cameras[cameras.length - 1].id : cameras[0].id;
                        
                        html5QrCode.start(
                            cameraId,
                            config,
                            onScanSuccess,
                            onScanFailure
                        ).catch(err => {
                            console.error('Error starting scanner with camera ID:', err);
                            // Fallback ke facingMode jika gagal
                            html5QrCode.start(
                                { facingMode: "environment" },
                                config,
                                onScanSuccess,
                                onScanFailure
                            ).catch(err2 => {
                                console.error('Error starting scanner with facingMode:', err2);
                                barcodeStatus.textContent = 'Gagal mengakses kamera. Pastikan izin kamera diaktifkan.';
                                barcodeStatus.className = 'mt-1 text-xs text-red-500';
                                stopBarcodeScanner();
                            });
                        });
                    }
                }).catch(err => {
                    console.error('Error getting cameras:', err);
                    barcodeStatus.textContent = 'Tidak dapat mengakses daftar kamera. Pastikan izin kamera diaktifkan.';
                    barcodeStatus.className = 'mt-1 text-xs text-red-500';
                    stopBarcodeScanner();
                });
            }

            function stopBarcodeScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        html5QrCode.clear();
                        html5QrCode = null;
                        scannerModal.classList.add('hidden');
                    }).catch(err => {
                        console.error('Error stopping scanner:', err);
                        scannerModal.classList.add('hidden');
                    });
                } else {
                    scannerModal.classList.add('hidden');
                }
            }

            function onScanSuccess(decodedText, decodedResult) {
                barcodeInput.value = decodedText;
                barcodeStatus.textContent = 'Barcode berhasil dipindai: ' + decodedText;
                barcodeStatus.className = 'mt-1 text-xs text-green-600';
                stopBarcodeScanner();
            }

            function onScanFailure(error) {
                // Ignore scan failures (normal during scanning)
            }
        });
    </script>
@endsection
