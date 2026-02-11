@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $expense ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}
            </h1>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
            <form action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @if($expense)
                    @method('PUT')
                @endif

                <!-- Expense Date -->
                <div class="mb-4">
                    <label for="expense_date" class="block text-sm font-medium text-slate-700 mb-2">
                        Tanggal Pengeluaran <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="expense_date" id="expense_date"
                        value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('expense_date') border-red-500 @enderror"
                        required>
                    @error('expense_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror"
                        required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-slate-700 mb-2">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount ?? '') }}"
                        step="0.01" min="0"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                        required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                        Keterangan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                        required>{{ old('description', $expense->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Receipt Image -->
                <div class="mb-6">
                    <label for="receipt_image" class="block text-sm font-medium text-slate-700 mb-2">
                        Foto Bukti (Opsional)
                    </label>

                    @if($expense && $expense->receipt_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $expense->receipt_image) }}" alt="Receipt"
                                class="max-w-xs rounded-lg border border-slate-300">
                            <p class="text-sm text-slate-500 mt-1">Upload gambar baru untuk mengganti</p>
                        </div>
                    @endif

                    <input type="file" name="receipt_image" id="receipt_image" accept="image/*"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('receipt_image') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-slate-500">Format: JPG, PNG. Maksimal 2MB</p>
                    @error('receipt_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Simpan
                    </button>
                    <a href="{{ route('expenses.index') }}"
                        class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2 rounded-lg font-medium transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection