@extends('layouts.app')

@section('title', 'Bahan Baku')
@section('subtitle', 'Kelola data bahan baku dan stok')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Bahan Baku</h2>
            <p class="text-sm text-slate-500">Kelola stok, satuan, dan barcode bahan baku</p>
        </div>
        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            <button type="button" onclick="openImportModal()" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                📄 Import Excel
            </button>
            <button type="button" onclick="openScannerModal('in')" class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500 transition-all">
                📥 Scan Masuk
            </button>
            <button type="button" onclick="openScannerModal('out')" class="inline-flex items-center justify-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-100 transition-all">
                📤 Scan Keluar
            </button>
            <a href="{{ route('raw-materials.create') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all">
                + Bahan Baku Baru
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex justify-between items-start">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="text-red-500 hover:text-red-700 font-bold ml-4" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif



    @if(session('success'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button type="button" class="text-emerald-500 hover:text-emerald-700 font-bold" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    <form method="GET" action="{{ route('raw-materials.index') }}" class="mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <input type="text" name="search" placeholder="Cari nama bahan baku / SKU / barcode..." value="{{ request('search') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <span class="absolute inset-y-0 right-4 flex items-center text-slate-400">⌕</span>
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition-all">
                Cari
            </button>
        </div>
    </form>

    <form id="bulk-action-form" action="{{ route('raw-materials.bulk_barcode') }}" method="POST" target="_blank">
        @csrf
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" name="selected" value="1" class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-indigo-100 transition-all">
                🖨️ Cetak Barcode Terpilih
            </button>
            <button type="button" id="btn-print-all-barcode" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                📑 Cetak Semua Barcode
            </button>
        </div>
    </form>

    <!-- Desktop Table View -->
    <div class="mt-6 hidden md:block overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-6 py-4 w-10">No</th>
                    <th class="px-6 py-4">Bahan Baku</th>
                    <th class="px-6 py-4">Satuan</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4">Barcode / SKU</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rawMaterials as $item)
                    <tr class="{{ $item->stock <= $item->min_stock ? 'bg-red-50/30' : '' }}">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="material_ids[]" value="{{ $item->id }}" form="bulk-action-form" class="material-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $loop->iteration + $rawMaterials->firstItem() - 1 }}</td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $item->name }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $item->unit }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->stock <= $item->min_stock ? 'bg-red-100 text-red-700' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ number_format($item->stock) }} {{ $item->unit }}
                            </span>
                            @if($item->min_stock > 0)
                            <div class="mt-1 text-[10px] text-slate-400">Min: {{ number_format($item->min_stock) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            @if($item->barcode)
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200">
                                    {{ $item->barcode }}
                                </span>
                            @else
                                <button type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 underline" onclick="generateBarcode({{ $item->id }}, '{{ $item->name }}')">
                                    Buat Barcode
                                </button>
                            @endif
                            <div class="mt-1 text-xs text-slate-400">SKU: {{ $item->sku }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                @if($item->barcode)
                                <button type="button" onclick="showBarcode('{{ $item->barcode }}', '{{ $item->name }}')" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    🖨️ Cetak
                                </button>
                                @endif
                                <a href="{{ route('raw-materials.edit', $item->id) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('raw-materials.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus bahan baku {{ $item->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-500 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500">
                            Belum ada bahan baku.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($rawMaterials as $item)
            <div class="rounded-xl border {{ $item->stock <= $item->min_stock ? 'border-red-200 bg-red-50/20' : 'border-slate-200 bg-white' }} p-4 shadow-sm relative">
                <div class="absolute top-4 left-4">
                    <input type="checkbox" name="material_ids[]" value="{{ $item->id }}" form="bulk-action-form" class="material-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                </div>
                <div class="flex items-start justify-between gap-3 pl-8">
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-800">{{ $item->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">SKU: {{ $item->sku }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->stock <= $item->min_stock ? 'bg-red-100 text-red-700' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ number_format($item->stock) }} {{ $item->unit }}
                    </span>
                </div>

                <div class="mt-3 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Barcode:</span>
                        @if($item->barcode)
                            <span class="font-medium text-slate-700">{{ $item->barcode }}</span>
                        @else
                            <button type="button" class="text-indigo-600 hover:text-indigo-800 underline" onclick="generateBarcode({{ $item->id }}, '{{ $item->name }}')">
                                Buat Barcode
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    @if($item->barcode)
                    <button type="button" onclick="showBarcode('{{ $item->barcode }}', '{{ $item->name }}')" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-medium text-slate-600 hover:bg-slate-50">
                        Cetak
                    </button>
                    @endif
                    <a href="{{ route('raw-materials.edit', $item->id) }}" class="flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Edit
                    </a>
                    <form action="{{ route('raw-materials.destroy', $item->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Hapus bahan baku {{ $item->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">Belum ada bahan baku.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $rawMaterials->withQueryString()->links() }}
    </div>

    <!-- Import Modal Overlay -->
    <div id="importModalOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all transform scale-95 opacity-0" tabindex="-1">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-indigo-600 flex justify-between items-center text-white">
                <h3 class="font-bold text-lg">Import Excel Bahan Baku</h3>
                <button type="button" onclick="closeImportModal()" class="text-white hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="p-6 bg-slate-50 space-y-4">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-indigo-700 leading-relaxed">
                    Import akan menimpa (update) data lama jika kolom <span class="font-semibold">SKU</span> sama. Kolom wajib terisi: <span class="font-semibold">nama, satuan</span>. Kolom stok_awal hanya diproses saat memasukkan data baru (tidak merubah stok lama).
                </div>
                
                <div class="flex justify-center mb-2">
                    <a href="{{ route('raw-materials.import.template') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-slate-700 transition-all">
                        ⬇️ Download Template
                    </a>
                </div>

                <form action="{{ route('raw-materials.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-white hover:bg-slate-50 hover:border-indigo-400 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <p class="mb-2 text-sm text-slate-500"><span class="font-semibold">Klik untuk unggah</span> file Excel</p>
                            <p class="text-xs text-slate-400">.xlsx, .xls</p>
                        </div>
                        <input id="dropzone-file" type="file" name="import_file" accept=".xlsx,.xls" onchange="this.form.submit()" class="hidden" required />
                    </label>
                </form>
            </div>
        </div>
    </div>

    <!-- Scanner Modal Overlay -->
    <div id="scannerModalOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- Scanner Modal -->
    <div id="scannerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all transform scale-95 opacity-0" tabindex="-1">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
            <div id="scannerModalHeader" class="px-6 py-4 bg-emerald-600 flex justify-between items-center text-white">
                <h3 class="font-bold text-lg" id="scannerModalTitle">Scan Barang</h3>
                <button type="button" onclick="closeScannerModal()" class="text-white hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="p-6 bg-slate-50">
                <div id="scanAlert" class="mb-4 hidden rounded-xl px-4 py-3 text-sm font-medium"></div>
                
                <form id="scannerForm" onsubmit="handleScan(event)">
                    <input type="hidden" id="scanType" name="type" value="in">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kuantitas</label>
                        <input type="number" id="scanQuantity" name="quantity" value="1" min="1" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-lg font-bold text-center focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" required>
                        <p class="text-[10px] text-slate-500 text-center mt-1">Ubah jika item masuk/keluar lebih dari 1 sekaligus.</p>
                    </div>
                    
                    <div class="mb-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Barcode</label>
                        <input type="text" id="scanBarcode" name="barcode" placeholder="Arahkan Scanner..." class="w-full rounded-xl border-2 border-indigo-300 bg-white px-4 py-4 text-center text-xl font-bold tracking-widest text-slate-800 shadow-inner focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-200/50" autocomplete="off" required>
                    </div>
                    <button type="submit" class="hidden">Submit</button>
                </form>
            </div>
            <div class="bg-slate-100 px-6 py-3 text-center border-t border-slate-200">
                <p class="text-xs text-slate-500">Pastikan garis kursor biru berada di dalam kotak barcode sebelum menembak alat scanner.</p>
            </div>
        </div>
    </div>

    <!-- Barcode Print Modal -->
    <div id="barcodePrintModalOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden" aria-hidden="true"></div>
    <div id="barcodePrintModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" tabindex="-1">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Cetak Barcode</h3>
                <button type="button" onclick="closeBarcodeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-8 flex flex-col items-center justify-center bg-white" id="printArea">
                <p id="barcodeItemName" class="font-bold text-slate-800 text-center mb-3 text-lg"></p>
                <svg id="barcodeCanvas" class="max-w-full"></svg>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeBarcodeModal()" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors">Batal</button>
                <button type="button" onclick="printBarcode()" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    const scannerModal = document.getElementById('scannerModal');
    const scannerOverlay = document.getElementById('scannerModalOverlay');
    const barcodeModal = document.getElementById('barcodePrintModal');
    const barcodeOverlay = document.getElementById('barcodePrintModalOverlay');

    function openImportModal() {
        const modal = document.getElementById('importModal');
        const overlay = document.getElementById('importModalOverlay');
        
        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        
        // Trigger reflow
        void modal.offsetWidth;
        
        modal.classList.remove('scale-95', 'opacity-0');
        modal.classList.add('scale-100', 'opacity-100');
        
        overlay.classList.remove('opacity-0');
        overlay.classList.add('opacity-100');
    }
    
    function closeImportModal() {
        const modal = document.getElementById('importModal');
        const overlay = document.getElementById('importModalOverlay');
        
        modal.classList.remove('scale-100', 'opacity-100');
        modal.classList.add('scale-95', 'opacity-0');
        
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
        }, 200);
    }

    function openScannerModal(type) {
        document.getElementById('scanType').value = type;
        document.getElementById('scannerModalTitle').innerText = type === 'in' ? 'Scan Masuk (Stok +)' : 'Scan Keluar (Stok -)';
        
        const header = document.getElementById('scannerModalHeader');
        if (type === 'in') {
            header.className = 'px-6 py-4 bg-emerald-600 flex justify-between items-center text-white';
        } else {
            header.className = 'px-6 py-4 bg-red-500 flex justify-between items-center text-white';
        }
        
        document.getElementById('scanBarcode').value = '';
        document.getElementById('scanQuantity').value = 1;
        document.getElementById('scanAlert').classList.add('hidden');
        
        scannerOverlay.classList.remove('hidden');
        scannerModal.classList.remove('hidden');
        
        // Add timeout for transition
        setTimeout(() => {
            scannerOverlay.classList.remove('opacity-0');
            scannerModal.classList.remove('opacity-0', 'scale-95');
            document.getElementById('scanBarcode').focus();
        }, 10);
    }

    function closeScannerModal() {
        scannerOverlay.classList.add('opacity-0');
        scannerModal.classList.add('opacity-0', 'scale-95');
        
        setTimeout(() => {
            scannerOverlay.classList.add('hidden');
            scannerModal.classList.add('hidden');
            
            // Reload if success alert is showing
            const alertBox = document.getElementById('scanAlert');
            if(!alertBox.classList.contains('hidden') && alertBox.classList.contains('bg-emerald-100')) {
                window.location.reload();
            }
        }, 300);
    }

    async function handleScan(event) {
        event.preventDefault();
        
        const barcodeInput = document.getElementById('scanBarcode');
        const barcode = barcodeInput.value;
        const type = document.getElementById('scanType').value;
        const quantity = document.getElementById('scanQuantity').value;
        
        if(!barcode) return;
        barcodeInput.disabled = true;
        
        try {
            const response = await fetch('{{ route("raw-materials.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ barcode, type, quantity })
            });

            const data = await response.json();
            const alertBox = document.getElementById('scanAlert');
            
            alertBox.className = 'mb-4 rounded-xl px-4 py-3 text-sm font-medium'; // Reset classes
            
            if (data.success) {
                alertBox.classList.add('bg-emerald-100', 'text-emerald-700', 'border', 'border-emerald-200');
                alertBox.innerHTML = `<strong>Berhasil!</strong> ${data.message}`;
                barcodeInput.value = ''; // clear for next
            } else {
                alertBox.classList.add('bg-red-100', 'text-red-700', 'border', 'border-red-200');
                alertBox.innerHTML = `<strong>Gagal!</strong> ${data.message}`;
                barcodeInput.value = '';
            }
            alertBox.classList.remove('hidden');
        } catch (error) {
            const alertBox = document.getElementById('scanAlert');
            alertBox.className = 'mb-4 rounded-xl px-4 py-3 text-sm font-medium bg-red-100 text-red-700 border border-red-200';
            alertBox.innerHTML = '<strong>Error!</strong> Terjadi kesalahan jaringan.';
            alertBox.classList.remove('hidden');
        } finally {
            barcodeInput.disabled = false;
            barcodeInput.focus();
        }
    }

    async function generateBarcode(id, name) {
        if(!confirm(`Buat barcode otomatis untuk bahan baku: ${name}?`)) return;
        
        try {
            const response = await fetch(`/raw-materials/${id}/generate-barcode`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                window.location.reload();
            }
        } catch (error) {
            alert('Gagal membuat barcode');
        }
    }

    function showBarcode(barcode, name) {
        document.getElementById('barcodeItemName').innerText = name;
        JsBarcode("#barcodeCanvas", barcode, {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 16,
            margin: 10
        });
        
        barcodeOverlay.classList.remove('hidden');
        barcodeModal.classList.remove('hidden');
    }

    function closeBarcodeModal() {
        barcodeOverlay.classList.add('hidden');
        barcodeModal.classList.add('hidden');
    }

    function printBarcode() {
        const printContent = document.getElementById('printArea').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `<div style="display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;">${printContent}</div>`;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload(); 
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const materialCheckboxes = document.querySelectorAll('.material-checkbox');
        const bulkActionForm = document.getElementById('bulk-action-form');
        const printAllBtn = document.getElementById('btn-print-all-barcode');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                materialCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        materialCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(materialCheckboxes).every(c => c.checked);
                if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
            });
        });

        if (bulkActionForm) {
            bulkActionForm.addEventListener('submit', function(e) {
                if (e.submitter && e.submitter.name === 'selected') {
                    const hasChecked = Array.from(materialCheckboxes).some(c => c.checked);
                    if (!hasChecked) {
                        e.preventDefault();
                        alert('Pilih minimal satu bahan baku untuk mencetak barcode.');
                    }
                }
            });
        }

        if (printAllBtn) {
            printAllBtn.addEventListener('click', function() {
                if(confirm('Cetak semua barcode bahan baku?')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'all';
                    input.value = '1';
                    bulkActionForm.appendChild(input);
                    bulkActionForm.submit();
                }
            });
        }
    });
</script>
@endpush
