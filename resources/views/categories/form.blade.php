@extends('layouts.app')

@php
    $isEdit = isset($category);
    $title = $isEdit ? 'Edit Kategori' : 'Tambah Kategori';
    $subtitle = $isEdit ? 'Perbarui informasi kategori produk' : 'Buat kategori baru untuk produk';
    $formAction = $isEdit ? route('categories.update', $category) : route('categories.store');
    $isActive = old('is_active', $isEdit ? $category->is_active : true);
@endphp

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <div class="max-w-3xl rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ $formAction }}" method="POST" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div>
                <label class="text-sm font-medium text-slate-600">Nama Kategori</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $isEdit ? $category->name : '') }}"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    required
                >
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Deskripsi</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    {{ $isActive ? 'checked' : '' }}
                >
                <span class="text-sm text-slate-600">Kategori aktif</span>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('categories.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
@endsection
