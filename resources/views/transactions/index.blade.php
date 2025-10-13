@extends('layouts.app')

@section('title', 'Transaksi')
@section('subtitle', 'Pantau transaksi harian kasir')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Riwayat Transaksi</h2>
            <p class="text-sm text-slate-500">Filter transaksi berdasarkan tanggal dan invoice</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            + Transaksi Baru
        </a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="mt-6 grid gap-4 md:grid-cols-4">
        <div>
            <label class="text-xs uppercase text-slate-500">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div>
            <label class="text-xs uppercase text-slate-500">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div>
            <label class="text-xs uppercase text-slate-500">Invoice</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari invoice..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4">Invoice</th>
                    <th class="px-6 py-4">Kasir</th>
                    <th class="px-6 py-4">Pelanggan</th>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $transaction->invoice_number }}</p>
                            <p class="text-xs text-slate-500">Status: {{ ucfirst($transaction->status) }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $transaction->user?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $transaction->customer?->name ?? 'Umum' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $transaction->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-500">Profit: Rp {{ number_format($transaction->profit, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('transactions.show', $transaction) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Detail
                                </a>
                                <a target="_blank" href="{{ route('transactions.invoice', $transaction) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Invoice
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500">
                            Belum ada transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $transactions->withQueryString()->links() }}
    </div>
@endsection
