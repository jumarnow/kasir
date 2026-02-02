@extends('layouts.app')

@section('title', 'Finishing')
@section('subtitle', 'Kelola data finishing dan harga')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Finishing</h2>
            <p class="text-sm text-slate-500">Kelola jenis finishing dan harga layanan</p>
        </div>
        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            <a href="{{ route('finishings.create') }}"
                class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                + Finishing Baru
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('finishings.index') }}" class="mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <input type="text" name="search" placeholder="Cari nama finishing / kode" value="{{ request('search') }}"
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
                    <th class="px-6 py-4">Tipe Harga</th>
                    <th class="px-6 py-4">Harga</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($finishings as $finishing)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $finishing->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $finishing->code }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            @if($finishing->pricing_type == 'per_meter') Per Meter
                            @elseif($finishing->pricing_type == 'per_dimension') Per Dimensi
                            @else Per Unit
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">Rp {{ number_format($finishing->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold {{ $finishing->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                {{ $finishing->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('finishings.edit', $finishing) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('finishings.destroy', $finishing) }}" method="POST"
                                    class="delete-form inline" data-message="Hapus finishing {{ $finishing->name }}?">
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
                            Belum ada finishing. Tambahkan finishing baru sekarang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($finishings as $finishing)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm relative">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-800">{{ $finishing->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Kode: {{ $finishing->code }}</p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $finishing->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                        {{ $finishing->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="mt-3 space-y-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Tipe Harga:</span>
                        <span class="font-medium text-slate-800">
                            @if($finishing->pricing_type == 'per_meter') Per Meter
                            @elseif($finishing->pricing_type == 'per_dimension') Per Dimensi
                            @else Per Unit
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Harga:</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($finishing->price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('finishings.edit', $finishing) }}"
                        class="flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Edit
                    </a>
                    <form action="{{ route('finishings.destroy', $finishing) }}" method="POST" class="delete-form flex-1"
                        data-message="Hapus finishing {{ $finishing->name }}?">
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
                <p class="text-sm text-slate-500">Belum ada finishing. Tambahkan finishing baru sekarang.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $finishings->withQueryString()->links() }}
    </div>
@endsection