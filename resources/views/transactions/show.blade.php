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
                        @if($transaction->payment_status !== 'paid' && $transaction->due_date)
                            <p class="text-sm font-semibold text-red-600 mt-1">Jatuh Tempo: {{ $transaction->due_date->format('d M Y') }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase text-slate-500">Kasir</p>
                        <p class="font-medium text-slate-700">{{ $transaction->user?->name ?? '—' }}</p>
                        <div class="mt-1 flex flex-col items-end gap-1">
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $transaction->status }}</span>
                        </div>
                        <div class="mt-3">
                            <p class="text-xs uppercase text-slate-500">Eksekutor</p>
                            <p class="font-medium text-slate-700">{{ $transaction->eksekutor?->name ?? '-' }}</p>
                            @if($transaction->eksekutorTwo)
                                <p class="font-medium text-slate-700">{{ $transaction->eksekutorTwo->name }}</p>
                            @endif
                        </div>
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
                                    @if($item->product?->pricing_type === 'per_dimension')
                                        <p class="text-xs text-slate-500">Dimensi: {{ $item->width + 0 }} x {{ $item->length + 0 }} cm</p>
                                    @endif
                                    @if($item->finishing || $item->material || $item->display)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @if($item->material)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">Mat:
                                                    {{ $item->material->name }}</span>
                                            @endif
                                            @if($item->finishing)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Fin:
                                                    {{ $item->finishing->name }}</span>
                                            @endif
                                            @if($item->display)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-[10px] font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Disp:
                                                    {{ $item->display->name }}</span>
                                            @endif
                                        </div>
                                    @endif
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
                        <dd class="font-semibold text-slate-700">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Diskon</dt>
                        <dd class="font-semibold text-slate-700">Rp
                            {{ number_format($transaction->discount_amount, 0, ',', '.') }}
                            ({{ $transaction->discount_percent }}%)</dd>
                    </div>
                    @if ($transaction->shipping_cost > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Ongkir</dt>
                            <dd class="font-semibold text-slate-700">Rp
                                {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-base font-semibold text-slate-800">
                        <dt>Total</dt>
                        <dd class="text-indigo-600">Rp {{ number_format($transaction->total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Dibayar</dt>
                        <dd class="font-semibold text-slate-700">Rp
                            {{ number_format($transaction->amount_paid, 0, ',', '.') }}</dd>
                    </div>
                    <!-- <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Kembalian</dt>
                        <dd class="font-semibold text-slate-700">Rp
                            {{ number_format($transaction->change_due, 0, ',', '.') }}</dd>
                    </div> -->
                    @if($transaction->remaining_amount > 0)
                        <div
                            class="flex items-center justify-between text-red-600 font-bold border-t border-dashed border-red-200 pt-2 mt-2">
                            <dt>Kekurangan</dt>
                            <dd>Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if(auth()->user()->hasPermission('view_profit'))
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Profit</dt>
                            <dd class="font-semibold text-emerald-600">Rp {{ number_format($transaction->profit, 0, ',', '.') }}
                            </dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-3">
                        <dt class="text-slate-500">Status Pembayaran</dt>
                        <dd class="font-bold {{ match($transaction->payment_status) {
                            'paid' => 'text-emerald-600',
                            'dp', 'unpaid', 'pending' => 'text-red-600',
                            default => 'text-amber-600'
                        } }}">
                            {{ $transaction->payment_status_label }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Metode</dt>
                        <dd class="font-semibold text-slate-700">{{ strtoupper($transaction->payment_method) }}</dd>
                    </div>
                </dl>
                <div class="mt-6 flex flex-col gap-3">
                    @if($transaction->remaining_amount > 0)
                        <button type="button" onclick="document.getElementById('payment-modal').classList.remove('hidden')" 
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500">
                             Bayar / Pelunasan
                        </button>
                    @endif
                    @if($transaction->payment_status === 'paid')
                        <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                            Cetak Struk
                        </a>
                    @else
                        <a href="{{ route('transactions.invoice_a5', $transaction) }}" target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                            Cetak Invoice
                        </a>
                    @endif
                    <a href="{{ route('transactions.spk', $transaction) }}" target="_blank"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cetak SPK (Thermal)
                    </a>
                </div>
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
    {{-- Payment Modal --}}
    @if($transaction->remaining_amount > 0)
    <div id="payment-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('payment-modal').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Pelunasan Transaksi</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Sisa Tagihan: <span class="font-bold text-red-600">Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</span></p>
                    </div>
                </div>

                <form action="{{ route('transactions.payments.store', $transaction) }}" method="POST" class="mt-4">
                    @csrf
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Jumlah Pembayaran</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" name="amount" id="payment-amount" 
                                class="currency-input focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2" 
                                placeholder="0" 
                                value="{{ old('amount', number_format($transaction->remaining_amount, 0, ',', '.')) }}"
                                required>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                            Bayar
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm" onclick="document.getElementById('payment-modal').classList.add('hidden')">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Currency formatter for modal input
            const input = document.getElementById('payment-amount');
            if (input) {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value === '') {
                        this.value = '';
                        return;
                    }
                    this.value = new Intl.NumberFormat('id-ID').format(value);
                });
            }
        });
    </script>
    @endpush
    @endif
@endsection