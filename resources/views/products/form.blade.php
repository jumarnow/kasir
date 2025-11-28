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
                        <label class="text-sm font-medium text-slate-600">Kategori <span class="text-slate-400 text-xs">(opsional)</span></label>
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
                        <label class="text-sm font-medium text-slate-600">SKU <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <input
                            type="text"
                            name="sku"
                            value="{{ old('sku', $isEdit ? $product->sku : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
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
                        <label class="text-sm font-medium text-slate-600">Harga Jual 1</label>
                        <input
                            type="text"
                            min="0"
                            name="price"
                            value="{{ old('price', $isEdit ? formatNumber($product->price) : '') }}"
                            class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Harga Jual 2 <span class="text-slate-400 text-xs">(opsional)</span></label>
                            <input
                                type="text"
                                min="0"
                                name="price_2"
                                value="{{ old('price_2', $isEdit ? formatNumber($product->price_2) : '') }}"
                                class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            >
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Harga Jual 3 <span class="text-slate-400 text-xs">(opsional)</span></label>
                            <input
                                type="text"
                                min="0"
                                name="price_3"
                                value="{{ old('price_3', $isEdit ? formatNumber($product->price_3) : '') }}"
                                class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            >
                        </div>
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
                                value="{{ old('stock_alert', $isEdit ? $product->stock_alert : 5) }}"
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
                <label class="text-sm font-medium text-slate-600">Deskripsi <span class="text-slate-400 text-xs">(opsional)</span></label>
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
                <div class="relative">
                    <div id="barcode-reader" class="w-full rounded-lg overflow-hidden"></div>
                    <button type="button" id="switch-barcode-camera" class="absolute top-3 right-3 rounded-lg bg-white/90 backdrop-blur-sm p-2.5 shadow-lg hover:bg-white transition-colors">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
                <p class="mt-3 text-sm text-slate-600 text-center">Arahkan kamera ke barcode produk</p>
            </div>
        </div>
    </div>

    <!-- OCR Camera Modal -->
    <div id="ocr-camera-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
            <div class="flex items-center justify-between p-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">OCR - Scan Nama Produk</h3>
                <button type="button" id="close-ocr-camera" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <div class="relative">
                    <video id="ocr-video" class="w-full rounded-lg" autoplay playsinline></video>
                    <canvas id="ocr-canvas" class="hidden"></canvas>
                    <button type="button" id="switch-ocr-camera" class="absolute top-3 right-3 rounded-lg bg-white/90 backdrop-blur-sm p-2.5 shadow-lg hover:bg-white transition-colors">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
                <div class="mt-4 flex flex-col gap-3">
                    <button type="button" id="capture-ocr" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-500 transition-colors">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Ambil Foto
                    </button>
                    <p class="text-sm text-slate-600 text-center">Arahkan kamera ke nama produk yang ingin dibaca</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // OCR Camera Feature
            const ocrButton = document.getElementById('ocr-button');
            const productNameInput = document.getElementById('product-name');
            const ocrStatus = document.getElementById('ocr-status');
            const ocrCameraModal = document.getElementById('ocr-camera-modal');
            const closeOcrCameraButton = document.getElementById('close-ocr-camera');
            const ocrVideo = document.getElementById('ocr-video');
            const ocrCanvas = document.getElementById('ocr-canvas');
            const captureOcrButton = document.getElementById('capture-ocr');
            const switchOcrCameraButton = document.getElementById('switch-ocr-camera');
            let ocrStream = null;
            let currentFacingMode = 'environment'; // Default ke kamera belakang

            ocrButton.addEventListener('click', function() {
                ocrCameraModal.classList.remove('hidden');
                startOcrCamera();
            });

            closeOcrCameraButton.addEventListener('click', function() {
                stopOcrCamera();
            });

            switchOcrCameraButton.addEventListener('click', function() {
                // Toggle between front and back camera
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                // Restart camera with new facing mode
                if (ocrStream) {
                    stopOcrCameraStream();
                    startOcrCamera();
                }
            });

            function startOcrCamera() {
                navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: currentFacingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                })
                .then(stream => {
                    ocrStream = stream;
                    ocrVideo.srcObject = stream;
                })
                .catch(err => {
                    console.error('Error accessing camera:', err);
                    ocrStatus.textContent = 'Gagal mengakses kamera. Pastikan izin kamera diaktifkan.';
                    ocrStatus.className = 'mt-1 text-xs text-red-500';
                    stopOcrCamera();
                });
            }

            function stopOcrCameraStream() {
                if (ocrStream) {
                    ocrStream.getTracks().forEach(track => track.stop());
                    ocrStream = null;
                }
            }

            function stopOcrCamera() {
                stopOcrCameraStream();
                ocrCameraModal.classList.add('hidden');
                currentFacingMode = 'environment'; // Reset to default
            }

            captureOcrButton.addEventListener('click', async function() {
                // Capture image from video
                ocrCanvas.width = ocrVideo.videoWidth;
                ocrCanvas.height = ocrVideo.videoHeight;
                const context = ocrCanvas.getContext('2d');
                context.drawImage(ocrVideo, 0, 0);

                // Convert canvas to blob
                ocrCanvas.toBlob(async (blob) => {
                    // Show loading state
                    captureOcrButton.disabled = true;
                    captureOcrButton.innerHTML = '<svg class="w-5 h-5 inline-block animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';
                    ocrStatus.textContent = 'Memproses gambar...';
                    ocrStatus.className = 'mt-1 text-xs text-indigo-600';

                    try {
                        const formData = new FormData();
                        formData.append('produk', blob, 'capture.jpg');

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
                            stopOcrCamera();
                        } else {
                            throw new Error(data.message || 'Gagal memproses gambar');
                        }
                    } catch (error) {
                        ocrStatus.textContent = error.message || 'Terjadi kesalahan saat memproses gambar';
                        ocrStatus.className = 'mt-1 text-xs text-red-500';
                    } finally {
                        // Reset button
                        captureOcrButton.disabled = false;
                        captureOcrButton.innerHTML = '<svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Ambil Foto';
                    }
                }, 'image/jpeg', 0.9);
            });

            // Barcode Scanner Feature
            const scanBarcodeButton = document.getElementById('scan-barcode-button');
            const barcodeInput = document.getElementById('barcode-input');
            const barcodeStatus = document.getElementById('barcode-status');
            const scannerModal = document.getElementById('barcode-scanner-modal');
            const closeScannerButton = document.getElementById('close-scanner');
            const switchBarcodeCameraButton = document.getElementById('switch-barcode-camera');
            let html5QrCode = null;
            let availableCameras = [];
            let currentCameraIndex = 0;

            scanBarcodeButton.addEventListener('click', function() {
                scannerModal.classList.remove('hidden');
                startBarcodeScanner();
            });

            closeScannerButton.addEventListener('click', function() {
                stopBarcodeScanner();
            });

            switchBarcodeCameraButton.addEventListener('click', function() {
                if (availableCameras.length > 1) {
                    // Switch to next camera
                    currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
                    restartBarcodeScanner();
                }
            });

            function restartBarcodeScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        startBarcodeScannerWithCamera(availableCameras[currentCameraIndex].id);
                    }).catch(err => {
                        console.error('Error stopping scanner:', err);
                        startBarcodeScannerWithCamera(availableCameras[currentCameraIndex].id);
                    });
                }
            }

            function startBarcodeScanner() {
                html5QrCode = new Html5Qrcode("barcode-reader");
                
                // Get available cameras
                Html5Qrcode.getCameras().then(cameras => {
                    if (cameras && cameras.length) {
                        availableCameras = cameras;
                        // Default ke kamera belakang (biasanya kamera terakhir)
                        currentCameraIndex = cameras.length > 1 ? cameras.length - 1 : 0;
                        startBarcodeScannerWithCamera(availableCameras[currentCameraIndex].id);
                    } else {
                        // Fallback ke facingMode jika tidak ada kamera terdeteksi
                        startBarcodeScannerWithFacingMode();
                    }
                }).catch(err => {
                    console.error('Error getting cameras:', err);
                    // Fallback ke facingMode
                    startBarcodeScannerWithFacingMode();
                });
            }

            function startBarcodeScannerWithCamera(cameraId) {
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

                html5QrCode.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                ).catch(err => {
                    console.error('Error starting scanner with camera ID:', err);
                    barcodeStatus.textContent = 'Gagal mengakses kamera. Pastikan izin kamera diaktifkan.';
                    barcodeStatus.className = 'mt-1 text-xs text-red-500';
                });
            }

            function startBarcodeScannerWithFacingMode() {
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

                html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess,
                    onScanFailure
                ).catch(err => {
                    console.error('Error starting scanner with facingMode:', err);
                    barcodeStatus.textContent = 'Gagal mengakses kamera. Pastikan izin kamera diaktifkan.';
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
