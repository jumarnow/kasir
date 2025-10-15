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
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $isEdit ? $product->name : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
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
                        <input
                            type="text"
                            name="barcode"
                            value="{{ old('barcode', $isEdit ? $product->barcode : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Harga Jual</label>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            name="price"
                            value="{{ old('price', $isEdit ? $product->price : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Harga Modal</label>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            name="cost_price"
                            value="{{ old('cost_price', $isEdit ? $product->cost_price : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
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
@endsection
