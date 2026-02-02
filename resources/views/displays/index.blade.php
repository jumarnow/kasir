@extends('layouts.app')

@section('title', 'Display')
@section('subtitle', 'Kelola data display dan stok')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Display</h2>
            <p class="text-sm text-slate-500">Kelola stok dan lokasi display</p>
        </div>
        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            <a href="{{ route('displays.create') }}"
                class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                + Display Baru
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('displays.index') }}" class="mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <input type="text" name="search" placeholder="Cari nama display / kode / lokasi"
                    value="{{ request('search') }}"
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
                    <th class="px-6 py-4">Lokasi</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($displays as $display)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $display->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $display->code }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $display->location ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold {{ $display->stock <= $display->stock_alert ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $display->stock }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold {{ $display->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                {{ $display->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('displays.edit', $display) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('displays.destroy', $display) }}" method="POST"
                                    class="delete-form inline" data-message="Hapus display {{ $display->name }}?">
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
                        <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500">
                            Belum ada display. Tambahkan display baru sekarang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($displays as $display)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm relative">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-800">{{ $display->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Kode: {{ $display->code }}</p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $display->stock <= $display->stock_alert ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ $display->stock }}
                    </span>
                </div>

                <div class="mt-3 space-y-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Lokasi:</span>
                        <span class="text-slate-800">{{ $display->location ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold {{ $display->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ $display->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('displays.edit', $display) }}"
                        class="flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Edit
                    </a>
                    <form action="{{ route('displays.destroy', $display) }}" method="POST" class="delete-form flex-1"
                        data-message="Hapus display {{ $display->name }}?">
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
                <p class="text-sm text-slate-500">Belum ada display. Tambahkan display baru sekarang.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $displays->withQueryString()->links() }}
    </div>
@endsection