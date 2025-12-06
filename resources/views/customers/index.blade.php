@extends('layouts.app')

@section('title', 'Pelanggan')
@section('subtitle', 'Kelola data pelanggan dan histori transaksi')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Pelanggan</h2>
            <p class="text-sm text-slate-500">Catat data pelanggan untuk program loyalti dan identifikasi transaksi</p>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            + Pelanggan Baru
        </a>
    </div>

    <form method="GET" action="{{ route('customers.index') }}" class="mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <input type="text" name="q" placeholder="Cari nama / email / nomor telepon" value="{{ $search }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <span class="absolute inset-y-0 right-4 flex items-center text-slate-400">⌕</span>
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Cari
            </button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4">Pelanggan</th>
                    <th class="px-6 py-4">Kontak</th>
                    <th class="px-6 py-4">Alamat</th>
                    <th class="px-6 py-4">Tier Harga</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $customer->name }}</p>
                            <p class="text-xs text-slate-500">Catatan: {{ $customer->notes ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <p>{{ $customer->email ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $customer->phone ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <p>{{ $customer->address ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $customer->city }} {{ $customer->state }} {{ $customer->postal_code }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            @if ($customer->price_tier == 1)
                                Harga Jual 1 (Regular)
                            @elseif ($customer->price_tier == 2)
                                Harga Jual 2 (Grosir)
                            @elseif ($customer->price_tier == 3)
                                Harga Jual 3 (Distributor)
                            @else
                                —
                            @endif
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $customer->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-200 text-slate-600' }}">
                                {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('customers.edit', $customer) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus data pelanggan?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-500 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-500">
                            Belum ada pelanggan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $customers->withQueryString()->links() }}
    </div>
@endsection
