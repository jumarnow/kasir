@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Pengeluaran</h1>
            <a href="{{ route('expenses.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <span class="mr-1">+</span> Tambah Pengeluaran
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Vendor / Supplier</label>
                    <input type="text" name="vendor_name" value="{{ request('vendor_name') }}" placeholder="Cari vendor..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex-1">
                        Filter
                    </button>
                    <a href="{{ route('expenses.index') }}"
                        class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Total Summary -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow p-6 mb-6 text-white">
            <div class="text-sm font-medium opacity-90">Total Pengeluaran</div>
            <div class="text-3xl font-bold mt-1">Rp {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}</div>
        </div>

        <!-- Expenses Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Vendor / Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Keterangan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase">Bukti</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $expense->expense_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($expense->category->type === 'daily') bg-blue-100 text-blue-800
                                                        @elseif($expense->category->type === 'monthly') bg-purple-100 text-purple-800
                                                        @else bg-green-100 text-green-800
                                                        @endif">
                                    {{ $expense->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $expense->vendor_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                {{ Str::limit($expense->description, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-slate-900">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($expense->receipt_image)
                                    <a href="{{ asset('storage/' . $expense->receipt_image) }}" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-900 text-xs font-medium border border-indigo-200 rounded-md px-2 py-1">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-2 justify-end">
                                    <a href="{{ route('expenses.edit', $expense) }}"
                                        class="rounded-full border border-amber-200 px-3 py-1 text-xs text-amber-600 hover:bg-amber-50">
                                        Edit
                                    </a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Yakin ingin menghapus pengeluaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data pengeluaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($expenses->hasPages())
            <div class="mt-6">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
@endsection