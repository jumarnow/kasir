@extends('layouts.app')

@section('title', 'Material')
@section('subtitle', 'Kelola data material dan stok')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Material</h2>
            <p class="text-sm text-slate-500">Kelola stok dan harga material</p>
        </div>
        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            <a href="{{ route('materials.create') }}"
                class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                + Material Baru
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('materials.index') }}" class="mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <input type="text" name="search" placeholder="Cari nama material / kode" value="{{ request('search') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <span class="absolute inset-y-0 right-4 flex items-center text-slate-400">⌕</span>
            </div>
            <button type="submit"
                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Cari
            </button>
        </div>
    </form>

    <div class="mt-6 hidden md:block overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Kode</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4">Harga Beli</th>
                    <th class="px-6 py-4">Harga Jual</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($materials as $material)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $material->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $material->code }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold {{ $material->stock <= $material->stock_alert ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $material->stock }} {{ $material->unit }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600">Rp {{ number_format($material->cost_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-slate-600">Rp {{ number_format($material->selling_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold {{ $material->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('materials.edit', $material) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('materials.destroy', $material) }}" method="POST"
                                    class="delete-form inline" data-message="Hapus material {{ $material->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-500 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-6 text-center text-sm text-slate-500">
                            Belum ada material. Tambahkan material baru sekarang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($materials as $material)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm relative">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-800">{{ $material->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Kode: {{ $material->code }}</p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $material->stock <= $material->stock_alert ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ $material->stock }} {{ $material->unit }}
                    </span>
                </div>

                <div class="mt-3 space-y-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Harga Beli:</span>
                        <span class="text-slate-800">Rp {{ number_format($material->cost_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Harga Jual:</span>
                        <span class="font-semibold text-slate-800">Rp
                            {{ number_format($material->selling_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold {{ $material->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('materials.edit', $material) }}"
                        class="flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Edit
                    </a>
                    <form action="{{ route('materials.destroy', $material) }}" method="POST" class="delete-form flex-1"
                        data-message="Hapus material {{ $material->name }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">Belum ada material. Tambahkan material baru sekarang.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $materials->withQueryString()->links() }}
    </div>
@endsection