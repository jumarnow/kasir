@extends('layouts.app')

@php
    $isEdit = isset($display);
    $title = $isEdit ? 'Edit Display' : 'Tambah Display';
    $subtitle = $isEdit ? 'Perbarui informasi display' : 'Input detail display baru';
    $formAction = $isEdit ? route('displays.update', $display) : route('displays.store');
    $isActive = old('is_active', $isEdit ? $display->is_active : true);
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
                        <label class="text-sm font-medium text-slate-600">Nama Display</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $isEdit ? $display->name : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Kode <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <input
                            type="text"
                            name="code"
                            value="{{ old('code', $isEdit ? $display->code : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Lokasi <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <input
                            type="text"
                            name="location"
                            value="{{ old('location', $isEdit ? $display->location : '') }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                             <label class="text-sm font-medium text-slate-600">Stok</label>
                            <input
                                type="number"
                                min="0"
                                name="stock"
                                value="{{ old('stock', $isEdit ? $display->stock : 0) }}"
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
                                value="{{ old('stock_alert', $isEdit ? $display->stock_alert : 5) }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                required
                            >
                        </div>
                    </div>
                </div>
                 <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Deskripsi <span class="text-slate-400 text-xs">(opsional)</span></label>
                        <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $isEdit ? $display->description : '') }}</textarea>
                    </div>
                     <div class="flex items-center gap-2 pt-4">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            {{ $isActive ? 'checked' : '' }}
                        >
                        <span class="text-sm text-slate-600">Display aktif</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('displays.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Display' }}
                </button>
            </div>
        </form>
    </div>
@endsection
