@extends('layouts.app')

@section('title', 'Tambah Kategori')
@section('subtitle', 'Buat kategori baru untuk produk')

@section('content')
    <div class="max-w-3xl rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-600">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Deskripsi</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description') }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="text-sm text-slate-600">Kategori aktif</span>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('categories.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Simpan</button>
            </div>
        </form>
    </div>
@endsection
