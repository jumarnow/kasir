@extends('layouts.app')

@section('title', 'Bahan Baku')
@section('subtitle', 'Tambah data bahan baku baru')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Tambah Bahan Baku</h2>
            <p class="text-sm text-slate-500">Masukkan informasi bahan baku baru ke dalam sistem</p>
        </div>
        <div>
            <a href="{{ route('raw-materials.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                ← Kembali
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-6">
            <form action="{{ route('raw-materials.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Bahan Baku <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('name') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror" required>
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Satuan <span class="text-red-500">*</span></label>
                        <input type="text" name="unit" value="{{ old('unit') }}" placeholder="Contoh: Pcs, Rim, Roll, Lembar" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('unit') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror" required>
                        @error('unit')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Barcode Pabrik (Opsional)</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="Kosongkan jika ingin generate otomatis" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('barcode') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror">
                        <p class="mt-1 text-[11px] text-slate-400">Gunakan scanner barcode untuk mengisi field ini.</p>
                        @error('barcode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Batas Minimum Stok (Alert)</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('min_stock') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror">
                        @error('min_stock')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('description') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all">
                        Simpan Data
                    </button>
                    <a href="{{ route('raw-materials.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
