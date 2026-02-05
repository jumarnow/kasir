@extends('layouts.app')

@section('title', 'Transaksi')
@section('subtitle', 'Pantau transaksi harian kasir')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Riwayat Transaksi</h2>
            <p class="text-sm text-slate-500">Filter transaksi berdasarkan tanggal dan invoice</p>
        </div>
        <a href="{{ route('transactions.create') }}"
            class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            + Transaksi Baru
        </a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="mt-6 grid gap-4 md:grid-cols-6">
        <div>
            <label class="text-xs uppercase text-slate-500">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div>
            <label class="text-xs uppercase text-slate-500">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div>
            <label class="text-xs uppercase text-slate-500">Pelanggan</label>
            <select name="customer_id" 
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Semua Pelanggan</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs uppercase text-slate-500">Status Pembayaran</label>
            <select name="payment_status" 
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Semua Status</option>
                <option value="paid" {{ ($filters['payment_status'] ?? '') == 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="dp" {{ ($filters['payment_status'] ?? '') == 'dp' ? 'selected' : '' }}>DP (Kurang Bayar)</option>
                <option value="pending" {{ ($filters['payment_status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending (Belum Bayar)</option>
            </select>
        </div>
        <div>
            <label class="text-xs uppercase text-slate-500">Invoice</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari invoice..."
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div class="flex items-end">
            <button type="submit"
                class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        </div>
    </form>

    <!-- Desktop Table View -->
    <div class="mt-6 hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                            <p class="text-xs text-slate-500">Status: {{ 
                                match($transaction->payment_status) {
                                    'dp' => 'DP (Kurang Bayar)',
                                    'paid' => 'Lunas',
                                    'pending' => 'Pending (Belum Bayar)',
                                    default => ucfirst($transaction->payment_status)
                                }
                            }}</p>
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
                            <div class="font-semibold text-slate-800">Rp {{ number_format($transaction->total, 0, ',', '.') }}
                            </div>
                            @if(auth()->user()->hasPermission('view_profit'))
                                <div class="text-xs text-slate-500">Profit: Rp
                                    {{ number_format($transaction->profit, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('transactions.show', $transaction) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Detail
                                </a>
                                <a target="_blank" href="{{ route('transactions.invoice_a5', $transaction) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Invoice
                                </a>
                                <a target="_blank" href="{{ route('transactions.spk', $transaction) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-emerald-200 hover:text-emerald-600">
                                    SPK
                                </a>
                                @can('edit_transactions')
                                <a href="{{ route('transactions.edit', $transaction) }}"
                                    class="rounded-full border border-amber-200 px-3 py-1 text-xs text-amber-600 hover:bg-amber-50">
                                    Edit
                                </a>
                                @endcan
                                @can('delete_transactions')
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini? Stok akan dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                                @endcan
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

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($transactions as $transaction)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $transaction->invoice_number }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold bg-indigo-50 text-indigo-600">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Kasir:</span>
                        <span class="font-medium">{{ $transaction->user?->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Pelanggan:</span>
                        <span class="font-medium">{{ $transaction->customer?->name ?? 'Umum' }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="font-semibold text-slate-800">Total</span>
                        <div class="text-right">
                            <span class="block font-bold text-slate-800">Rp
                                {{ number_format($transaction->total, 0, ',', '.') }}</span>
                            @if(auth()->user()->hasPermission('view_profit'))
                                <span class="block text-xs text-slate-400">Profit: Rp
                                    {{ number_format($transaction->profit, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="{{ route('transactions.show', $transaction) }}"
                        class="flex-1 min-w-[80px] rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Detail
                    </a>
                    <a target="_blank" href="{{ route('transactions.invoice_a5', $transaction) }}"
                        class="flex-1 min-w-[80px] rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-medium text-slate-600 hover:bg-slate-50">
                        Invoice
                    </a>
                    <a target="_blank" href="{{ route('transactions.spk', $transaction) }}"
                        class="flex-1 min-w-[80px] rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-600 hover:bg-emerald-100">
                        SPK
                    </a>
                    @can('edit_transactions')
                    <a href="{{ route('transactions.edit', $transaction) }}"
                        class="flex-1 min-w-[80px] rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-center text-xs font-medium text-amber-600 hover:bg-amber-100">
                        Edit
                    </a>
                    @endcan
                    @can('delete_transactions')
                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="flex-1 min-w-[80px]" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-center text-xs font-medium text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">Belum ada transaksi.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $transactions->withQueryString()->links() }}
    </div>
@endsection