@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('subtitle', 'Ringkasan transaksi dan invoice')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
        {{-- Left Column (70%) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Header Invoice Card --}}
            <div class="rounded-2xl bg-white p-6 md:p-8 shadow-sm border border-slate-200/80">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 text-xs uppercase tracking-wider text-slate-500 font-semibold mb-2">
                            <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Invoice
                        </div>
                        <h2 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $transaction->invoice_number }}</h2>
                        <div class="flex flex-col gap-1 mt-2">
                            <p class="text-sm text-slate-500">Dibuat pada {{ $transaction->created_at->format('d M Y, H:i') }}</p>
                            @if($transaction->payment_status !== 'paid' && $transaction->due_date)
                                <p class="text-sm font-medium text-red-600 flex items-center gap-1.5 mt-1">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Jatuh Tempo: {{ $transaction->due_date->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Roles Badges --}}
                    <div class="flex flex-wrap md:flex-col gap-2 justify-end">
                        <div class="flex items-center gap-3 bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 w-16">Kasir</span>
                            <span class="text-sm font-medium text-slate-800">{{ $transaction->user?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-3 bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 w-16">Desainer</span>
                            <span class="text-sm font-medium text-slate-800">{{ $transaction->desainer?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-start gap-3 bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 w-16 mt-0.5">Eksekutor</span>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-800">{{ $transaction->eksekutor?->name ?? '—' }}</span>
                                @if($transaction->eksekutorTwo)
                                    <span class="text-sm font-medium text-slate-800">{{ $transaction->eksekutorTwo->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Item List Card --}}
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-white">
                    <h3 class="text-lg font-bold text-slate-800">Daftar Item</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-4 border-b border-slate-100">Produk</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-center">Qty</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-right">Harga</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($transaction->items as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-slate-800">
                                                {{ $item->custom_name ?? $item->product?->name ?? 'Produk terhapus' }}
                                            </p>
                                            @if($item->custom_name && !$item->product_id)
                                                <span class="inline-flex items-center rounded-md bg-stone-100 px-2 py-0.5 text-[10px] font-medium text-stone-600">Manual</span>
                                            @endif
                                        </div>
                                        @if($item->product)
                                            <p class="text-[13px] text-slate-400 mt-0.5">SKU: {{ $item->product->sku ?? '-' }}</p>
                                        @endif
                                        @if($item->product?->pricing_type === 'per_dimension')
                                            <p class="text-[13px] text-slate-500 mt-1 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" /></svg>
                                                {{ $item->width + 0 }} x {{ $item->length + 0 }} cm
                                            </p>
                                        @endif
                                        @if($item->finishing || $item->material || $item->display)
                                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                                @if($item->material)
                                                    <span class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[11px] font-medium text-slate-600 border border-slate-200">
                                                        Mat: {{ $item->material->name }}
                                                    </span>
                                                @endif
                                                @if($item->finishing)
                                                    <span class="inline-flex items-center rounded-md bg-indigo-50/50 px-2 py-1 text-[11px] font-medium text-indigo-700 border border-indigo-100">
                                                        Fin: {{ $item->finishing->name }}
                                                    </span>
                                                @endif
                                                @if($item->display)
                                                    <span class="inline-flex items-center rounded-md bg-blue-50/50 px-2 py-1 text-[11px] font-medium text-blue-700 border border-blue-100">
                                                        Disp: {{ $item->display->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center align-top pt-5">
                                        <span class="text-slate-600 font-medium">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right align-top pt-5">
                                        <span class="text-slate-500">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right align-top pt-5">
                                        <span class="font-semibold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column (30%) --}}
        <div class="space-y-6">
            {{-- Payment Summary Card --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        Pembayaran
                    </h3>
                    <div class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                        {{ match($transaction->payment_status) {
                            'paid' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                            'dp', 'unpaid', 'pending' => 'bg-red-50 text-red-700 border border-red-200/60',
                            default => 'bg-amber-50 text-amber-700 border border-amber-200/60'
                        } }}">
                        {{ $transaction->payment_status_label }}
                    </div>
                </div>

                <div class="space-y-3.5 text-[15px]">
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-800">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->discount_amount > 0)
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Diskon ({{ $transaction->discount_percent }}%)</span>
                            <span class="font-medium text-red-600">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($transaction->shipping_cost > 0)
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Biaya Kirim</span>
                            <span class="font-medium text-slate-800">Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <div class="flex items-end justify-between">
                            <span class="text-base font-bold text-slate-800 tracking-tight">Total</span>
                            <span class="text-2xl font-black text-indigo-600 tracking-tight">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 space-y-3.5 border-t border-slate-100">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Telah Dibayar</span>
                            <span class="font-semibold text-emerald-600">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
                        </div>
                        @if($transaction->remaining_amount > 0)
                            <div class="flex items-center justify-between bg-red-50/50 p-3 rounded-xl border border-red-100 mt-2">
                                <span class="font-semibold text-red-800">Sisa Tagihan</span>
                                <span class="font-bold text-red-700">Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-3">
                    @php 
                        $isCanceled = $transaction->trashed(); 
                        $isRejected = $transaction->status === 'rejected'; 
                    @endphp
                    @if($transaction->remaining_amount > 0 && !$isCanceled)
                        <button type="button" onclick="document.getElementById('payment-modal').classList.remove('hidden')" 
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                             Bayar / Pelunasan
                        </button>
                    @endif

                    @if($transaction->payment_status === 'paid')
                        <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.728 3.49l-2.071 2.072M17.272 3.49l2.071 2.072M12 3v12m0 0l-3-3m3 3l3-3m-6.75 6.75h10.5m-15 0h19.5" /></svg>
                            Cetak Struk
                        </a>
                    @else
                        <a href="{{ route('transactions.invoice_a5', $transaction) }}" target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
                            <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.728 3.49l-2.071 2.072M17.272 3.49l2.071 2.072M12 3v12m0 0l-3-3m3 3l3-3m-6.75 6.75h10.5m-15 0h19.5" /></svg>
                            Cetak Invoice
                        </a>
                    @endif
                    <a href="{{ route('transactions.spk', $transaction) }}" target="_blank"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
                        <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        Cetak SPK
                    </a>
                    
                    @if(!$isCanceled && !$isRejected)
                        @can('delete_transactions')
                        <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')" 
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 transition-all shadow-sm">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z" />
                            </svg>
                            Tambah Keterangan Transaksi
                        </button>
                        @endcan
                    @elseif($isCanceled)
                        <div class="inline-flex w-full flex-col items-center justify-center gap-1.5 rounded-xl bg-red-50/80 px-4 py-3 text-sm border border-red-100">
                            <span class="font-bold text-red-600">Status: Dibatalkan</span>
                        </div>
                    @elseif($isRejected)
                        <div class="inline-flex w-full flex-col items-center justify-center gap-1.5 rounded-xl bg-orange-50/80 px-4 py-3 text-sm border border-orange-100">
                            <span class="font-bold text-orange-600">Catatan Masalah (Reject)</span>
                            @if($transaction->reject_reason)
                                <span class="text-xs text-orange-600 text-center italic">"{{ $transaction->reject_reason }}"</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Card --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80">
                <div class="flex items-center gap-2 mb-5">
                    <svg class="w-5 h-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <h3 class="text-lg font-bold text-slate-800">Pelanggan</h3>
                </div>
                
                <div class="bg-slate-50/80 rounded-xl p-4 md:p-5 border border-slate-100">
                    <p class="font-bold text-slate-900 text-base">{{ $transaction->customer?->name ?? 'Umum' }}</p>
                    @if ($transaction->customer)
                        <div class="mt-4 space-y-3 text-[13px] text-slate-500">
                            @if($transaction->customer->email)
                            <p class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                {{ $transaction->customer->email }}
                            </p>
                            @endif
                            @if($transaction->customer->phone)
                            <p class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.48-4.18-7.076-7.076l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                                {{ $transaction->customer->phone }}
                            </p>
                            @endif
                            @if($transaction->customer->address)
                            <p class="flex items-start gap-2.5 mt-3 pt-3 border-t border-slate-200/60">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                <span class="leading-relaxed">{{ $transaction->customer->address }}</span>
                            </p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-400 mt-2">Tidak ada data detail pelanggan.</p>
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
                    <div class="space-y-4">
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

                        <div>
                            <label for="cash_received" class="block text-sm font-medium text-gray-700">Uang yang Dibayarkan (Opsional)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="text" id="payment-cash-received" 
                                    class="currency-input focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2" 
                                    placeholder="0">
                            </div>
                        </div>

                        <div id="payment-change-container" class="hidden rounded-md bg-emerald-50 p-3 border border-emerald-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-emerald-800">Kembalian</h3>
                                    <div class="mt-1 text-lg font-bold text-emerald-900" id="payment-change-amount">
                                        Rp 0
                                    </div>
                                </div>
                            </div>
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
    @endif
    
    {{-- Reject Modal --}}
    @php $isRejectedOrCanceled = $transaction->trashed() || $transaction->status === 'rejected'; @endphp
    @if(!$isRejectedOrCanceled && auth()->user()->can('delete_transactions'))
    <div id="reject-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('reject-modal').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6 border border-slate-100">
                <div class="flex items-start gap-4">
                    <div class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-1 sm:mt-0 text-left w-full">
                        <h3 class="text-base font-bold leading-6 text-slate-900" id="modal-title">Tambah Keterangan Transaksi</h3>
                        <div class="mt-2 text-sm text-slate-500">
                            <p>Tindakan ini akan menambah keterangan transaksi. Keterangan akan ditampilkan di histori transaksi.</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('transactions.reject', $transaction) }}" method="POST" class="mt-5 sm:ml-14 sm:mt-4 sm:pl-0">
                    @csrf
                    <div>
                        <label for="reject_reason" class="block text-sm font-medium text-slate-700">Keterangan Reject</label>
                        <div class="mt-2">
                            <textarea id="reject_reason" name="reject_reason" rows="3" class="block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-red-600 sm:text-sm sm:leading-6" required placeholder="Tuliskan keterangan transaksi ini..."></textarea>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse sm:gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto">Simpan</button>
                        <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Kembali</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Currency formatter for modal input
            const amountInput = document.getElementById('payment-amount');
            const cashInput = document.getElementById('payment-cash-received');
            const changeContainer = document.getElementById('payment-change-container');
            const changeAmountDisplay = document.getElementById('payment-change-amount');

            function formatCurrency(input) {
                let value = input.value.replace(/\D/g, '');
                if (value === '') {
                    input.value = '';
                    calculateChange();
                    return;
                }
                input.value = new Intl.NumberFormat('id-ID').format(value);
                calculateChange();
            }

            function calculateChange() {
                if (!amountInput || !cashInput || !changeContainer || !changeAmountDisplay) return;

                const amount = parseInt(amountInput.value.replace(/\D/g, '')) || 0;
                const cash = parseInt(cashInput.value.replace(/\D/g, '')) || 0;

                if (cash > 0 && cash >= amount) {
                    const change = cash - amount;
                    changeAmountDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(change);
                    changeContainer.classList.remove('hidden');
                } else {
                    changeContainer.classList.add('hidden');
                }
            }

            if (amountInput) {
                amountInput.addEventListener('input', function(e) {
                    formatCurrency(this);
                });
            }

            if (cashInput) {
                cashInput.addEventListener('input', function(e) {
                    formatCurrency(this);
                });
            }
        });
    </script>
    @endpush
@endsection