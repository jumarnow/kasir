@extends('layouts.app')

@php
    $isEdit = isset($finishing);
    $title = $isEdit ? 'Edit Finishing' : 'Tambah Finishing';
    $subtitle = $isEdit ? 'Perbarui informasi finishing' : 'Input detail finishing baru';
    $formAction = $isEdit ? route('finishings.update', $finishing) : route('finishings.store');
    $isActive = old('is_active', $isEdit ? $finishing->is_active : true);
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
                        <label class="text-sm font-medium text-slate-600">Nama Finishing</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $isEdit ? $finishing->name : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Kode <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <input
                            type="text"
                            name="code"
                            value="{{ old('code', $isEdit ? $finishing->code : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Tipe Perhitungan Harga</label>
                        <select name="pricing_type" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="per_unit" {{ old('pricing_type', $isEdit ? $finishing->pricing_type : '') == 'per_unit' ? 'selected' : '' }}>Per Unit (Pcs)</option>
                            <option value="per_meter" {{ old('pricing_type', $isEdit ? $finishing->pricing_type : '') == 'per_meter' ? 'selected' : '' }}>Per Meter (Panjang)</option>
                            <option value="per_dimension" {{ old('pricing_type', $isEdit ? $finishing->pricing_type : '') == 'per_dimension' ? 'selected' : '' }}>Per Dimensi (Luas)</option>
                        </select>
                    </div>
                     <div>
                        <label class="text-sm font-medium text-slate-600">Harga</label>
                        <input
                            type="number"
                            min="0"
                             step="0.01"
                            name="price"
                            value="{{ old('price', $isEdit ? $finishing->price : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                </div>
                 <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Deskripsi <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $isEdit ? $finishing->description : '') }}</textarea>
                    </div>
                     <div class="flex items-center gap-2 pt-4">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            {{ $isActive ? 'checked' : '' }}
                        >
                        <span class="text-sm text-slate-600">Finishing aktif</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('finishings.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Finishing' }}
                </button>
            </div>
        </form>
    </div>
@endsection
