@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $category ? 'Edit Kategori Pengeluaran' : 'Tambah Kategori Pengeluaran' }}
            </h1>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
            <form
                action="{{ $category ? route('expense-categories.update', $category) : route('expense-categories.store') }}"
                method="POST">
                @csrf
                @if($category)
                    @method('PUT')
                @endif

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                        required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('type') border-red-500 @enderror"
                        required>
                        <option value="">Pilih Tipe</option>
                        <option value="daily" {{ old('type', $category->type ?? '') === 'daily' ? 'selected' : '' }}>
                            Harian
                        </option>
                        <option value="monthly" {{ old('type', $category->type ?? '') === 'monthly' ? 'selected' : '' }}>
                            Bulanan
                        </option>
                        <option value="material" {{ old('type', $category->type ?? '') === 'material' ? 'selected' : '' }}>
                            Bahan Baku
                        </option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-slate-700">Aktif</span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <a href="{{ route('expense-categories.index') }}"
                        class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection