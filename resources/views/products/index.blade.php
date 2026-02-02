@extends('layouts.app')

@section('title', 'Produk')
@section('subtitle', 'Kelola data produk dan stok')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Produk</h2>
            <p class="text-sm text-slate-500">Kelola harga, stok, barcode dan kategori</p>
        </div>
        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs shadow-sm">
                @csrf
                <label for="import_file" class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <span class="hidden sm:inline font-semibold text-slate-700">Import Excel</span>
                    <input id="import_file" type="file" name="import_file" accept=".xlsx,.xls" onchange="this.form.submit()" class="hidden" required>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-100">Pilih File</span>
                </label>
            </form>
            <a href="{{ route('products.import.template') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm hover:border-indigo-200 hover:text-indigo-600">
                Template Import
            </a>
            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                + Produk Baru
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="relative md:w-1/3">
                        <select id="category-select" name="category_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative flex-1">
                        <input type="text" name="q" placeholder="Cari nama produk / SKU / barcode" value="{{ $search }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <span class="absolute inset-y-0 right-4 flex items-center text-slate-400">⌕</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Cari
            </button>
        </div>
    </form>

    <div class="mt-4 rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-indigo-700">
        Import akan membuat atau memperbarui produk berdasarkan kolom <span class="font-semibold">SKU</span>. Pastikan kolom wajib terisi: <span class="font-semibold">nama, sku, satuan, harga_jual_1</span>. Gunakan template untuk contoh format lengkap.
    </div>

    <form id="bulk-action-form" action="{{ route('products.bulk_barcode') }}" method="POST" target="_blank">
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
                    <th class="px-6 py-4">Produk</th>
                    <th class="px-6 py-4">Kategori</th>
                    <th class="px-6 py-4">Harga</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4">Barcode</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" form="bulk-action-form" class="product-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $product->name }}</p>
                            <p class="text-xs text-slate-500">SKU: {{ $product->sku }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $product->category?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">
                                @if($product->pricing_type === 'per_dimension')
                                    Rp {{ number_format($product->price_per_meter ?? $product->price, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">/ m²</span>
                                @else
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                @endif
                            </div>
                            @if($product->pricing_type === 'per_dimension')
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 mt-1">
                                    Per Dimensi
                                </span>
                            @else
                                @if($product->price_2)
                                    <div class="text-xs text-slate-500">Harga 2: Rp {{ number_format($product->price_2, 0, ',', '.') }}</div>
                                @endif
                                @if($product->price_3)
                                    <div class="text-xs text-slate-500">Harga 3: Rp {{ number_format($product->price_3, 0, ',', '.') }}</div>
                                @endif
                            @endif
                            <div class="text-xs text-slate-400 mt-1">Modal: Rp {{ number_format($product->cost_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->stock <= $product->stock_alert ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $product->stock }} {{ $product->unit }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $product->barcode ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a target="_blank" href="{{ route('products.barcode', $product) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Barcode
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="delete-form inline" data-message="Hapus produk {{ $product->name }}?">
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
                            Belum ada produk. Tambahkan produk baru sekarang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($products as $product)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm relative">
                <div class="absolute top-4 left-4">
                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" form="bulk-action-form" class="product-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                </div>
                <div class="flex items-start justify-between gap-3 pl-8">
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-800">{{ $product->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">SKU: {{ $product->sku }}</p>
                        @if($product->category)
                            <p class="text-xs text-slate-500">{{ $product->category->name }}</p>
                        @endif
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->stock <= $product->stock_alert ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ $product->stock }} {{ $product->unit }}
                    </span>
                </div>

                <div class="mt-3 space-y-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Harga 1:</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    </div>
                    @if($product->price_2)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Harga 2:</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($product->price_2, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($product->price_3)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Harga 3:</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($product->price_3, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Modal:</span>
                        <span class="text-slate-500">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</span>
                    </div>
                    @if($product->barcode)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Barcode:</span>
                            <span class="text-slate-500">{{ $product->barcode }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a target="_blank" href="{{ route('products.barcode', $product) }}" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-medium text-slate-600 hover:bg-slate-50">
                        Barcode
                    </a>
                    <a href="{{ route('products.edit', $product) }}" class="flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Edit
                    </a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="delete-form flex-1" data-message="Hapus produk {{ $product->name }}?">
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
                <p class="text-sm text-slate-500">Belum ada produk. Tambahkan produk baru sekarang.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $products->withQueryString()->links() }}
    </div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom styles to match Tailwind */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-color: #e2e8f0 !important; /* slate-200 */
        border-radius: 0.75rem !important; /* rounded-xl */
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 1rem !important; /* px-4 */
        padding-right: 2rem !important;
        color: #334155 !important; /* slate-700 */
        font-size: 0.875rem !important; /* text-sm */
        font-weight: 400 !important;
    }
    .select2-dropdown {
        border-color: #e2e8f0 !important;
        border-radius: 0.75rem !important;
        overflow: hidden !important;
        margin-top: 4px !important;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #4f46e5 !important; /* indigo-600 */
    }
    .select2-container--default .select2-results__option--selected {
        background-color: #e0e7ff !important; /* indigo-100 */
        color: #3730a3 !important; /* indigo-800 */
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#category-select').select2({
            placeholder: 'Semua Kategori',
            allowClear: true,
            width: '100%'
        });

        // Select All checkboxes
        $('#select-all').on('click', function() {
            $('.product-checkbox').prop('checked', this.checked);
        });

        $('.product-checkbox').on('click', function() {
            if ($('.product-checkbox:checked').length === $('.product-checkbox').length) {
                $('#select-all').prop('checked', true);
            } else {
                $('#select-all').prop('checked', false);
            }
        });

        // Bulk action form validation
        $('#bulk-action-form').on('submit', function(e) {
            const submitter = e.originalEvent.submitter;
            if (submitter && submitter.name === 'selected') {
                if ($('.product-checkbox:checked').length === 0) {
                    alert('Pilih minimal satu produk untuk mencetak barcode.');
                    return false;
                }
            }
        });

        $('#btn-print-all-barcode').on('click', function() {
            Swal.fire({
                title: 'Cetak Semua Barcode?',
                text: 'Ini akan membuka halaman barcode untuk seluruh produk aktif.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Cetak!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const $form = $('#bulk-action-form');
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'all',
                        value: '1'
                    }).appendTo($form);
                    $form.submit();
                }
            });
        });
    });
</script>
@endpush
