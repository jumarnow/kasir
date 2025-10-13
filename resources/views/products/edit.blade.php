@extends('layouts.app')

@section('title', 'Edit Produk')
@section('subtitle', 'Perbarui informasi produk dan stok')

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Nama Produk</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Kategori</label>
                        <select name="category_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected(old('category_id', $product->category_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Harga Jual</label>
                        <input type="number" min="0" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Harga Modal</label>
                        <input type="number" min="0" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Stok</label>
                            <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Alert Stok</label>
                            <input type="number" min="0" name="stock_alert" value="{{ old('stock_alert', $product->stock_alert) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Satuan</label>
                        <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                    </div>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-600">Deskripsi</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                <span class="text-sm text-slate-600">Produk aktif</span>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
