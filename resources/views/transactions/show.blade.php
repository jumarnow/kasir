@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('subtitle', 'Ringkasan transaksi dan invoice')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase text-slate-500">Invoice</p>
                        <h2 class="text-xl font-semibold text-slate-800">{{ $transaction->invoice_number }}</h2>
                        <p class="text-sm text-slate-500">Dibuat pada {{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase text-slate-500">Kasir</p>
                        <p class="font-medium text-slate-700">{{ $transaction->user?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $transaction->status }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200 overflow-hidden">
                <h3 class="text-lg font-semibold text-slate-800">Daftar Item</h3>
                <table class="mt-4 w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaction->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-700">{{ $item->product?->name ?? 'Produk terhapus' }}</p>
                                    <p class="text-xs text-slate-400">SKU: {{ $item->product?->sku ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                    Rp {{ number_format($item->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Ringkasan Pembayaran</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Subtotal</dt>
                        <dd class="font-semibold text-slate-700">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Diskon</dt>
                        <dd class="font-semibold text-slate-700">Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }} ({{ $transaction->discount_percent }}%)</dd>
                    </div>
                    <div class="flex items-center justify-between text-base font-semibold text-slate-800">
                        <dt>Total</dt>
                        <dd class="text-indigo-600">Rp {{ number_format($transaction->total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Dibayar</dt>
                        <dd class="font-semibold text-slate-700">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Kembalian</dt>
                        <dd class="font-semibold text-slate-700">Rp {{ number_format($transaction->change_due, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Profit</dt>
                        <dd class="font-semibold text-emerald-600">Rp {{ number_format($transaction->profit, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Metode</dt>
                        <dd class="font-semibold text-slate-700">{{ strtoupper($transaction->payment_method) }}</dd>
                    </div>
                </dl>
                <a href="{{ route('transactions.invoice', $transaction) }}" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    Cetak Invoice
                </a>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Pelanggan</h3>
                <div class="mt-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-700">{{ $transaction->customer?->name ?? 'Umum' }}</p>
                    @if ($transaction->customer)
                        <p>{{ $transaction->customer->email }}</p>
                        <p>{{ $transaction->customer->phone }}</p>
                        <p class="text-xs text-slate-400 mt-2">{{ $transaction->customer->address }}</p>
                    @else
                        <p class="text-xs text-slate-400">Tidak ada data pelanggan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if (session('print_invoice'))
    @push('scripts')
        <script>
            window.addEventListener('load', function () {
                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
                } catch (error) {
                    // ignore storage failures
                }

                const printWindow = window.open('', 'invoice-print', 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes');

                if (printWindow) {
                    printWindow.location.replace('{{ route('transactions.invoice', $transaction) }}');
                    printWindow.focus();
                }
            });
        </script>
    @endpush
@endif
