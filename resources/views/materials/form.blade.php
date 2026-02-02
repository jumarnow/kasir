@extends('layouts.app')

@php
    $isEdit = isset($material);
    $title = $isEdit ? 'Edit Material' : 'Tambah Material';
    $subtitle = $isEdit ? 'Perbarui informasi material' : 'Input detail material baru';
    $formAction = $isEdit ? route('materials.update', $material) : route('materials.store');
    $isActive = old('is_active', $isEdit ? $material->is_active : true);
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
                        <label class="text-sm font-medium text-slate-600">Nama Material</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $isEdit ? $material->name : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Kode <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <input
                            type="text"
                            name="code"
                            value="{{ old('code', $isEdit ? $material->code : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Satuan</label>
                        <input
                            type="text"
                            name="unit"
                            value="{{ old('unit', $isEdit ? $material->unit : 'pcs') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                             <label class="text-sm font-medium text-slate-600">Harga Beli</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name="cost_price"
                                value="{{ old('cost_price', $isEdit ? $material->cost_price : '') }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                required
                            >
                        </div>
                        <div>
                             <label class="text-sm font-medium text-slate-600">Harga Jual</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name="selling_price"
                                value="{{ old('selling_price', $isEdit ? $material->selling_price : '') }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                required
                            >
                        </div>
                    </div>
                </div>
                 <div class="space-y-4">
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                             <label class="text-sm font-medium text-slate-600">Stok</label>
                            <input
                                type="number"
                                min="0"
                                name="stock"
                                value="{{ old('stock', $isEdit ? $material->stock : 0) }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                required
                            >
                        </div>
                        <div>
                             <label class="text-sm font-medium text-slate-600">Alert Stok</label>
                            <input
                                type="number"
                                min="0"
                                name="stock_alert"
                                value="{{ old('stock_alert', $isEdit ? $material->stock_alert : 5) }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                required
                            >
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Deskripsi <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $isEdit ? $material->description : '') }}</textarea>
                    </div>
                     <div class="flex items-center gap-2 pt-4">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            {{ $isActive ? 'checked' : '' }}
                        >
                        <span class="text-sm text-slate-600">Material aktif</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('materials.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Material' }}
                </button>
            </div>
        </form>
    </div>
@endsection
