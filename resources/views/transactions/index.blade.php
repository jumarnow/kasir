@extends('layouts.app')

@section('title', 'Transaksi')
@section('subtitle', 'Pantau transaksi harian kasir')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Riwayat Transaksi</h2>
            <p class="text-sm text-slate-500">Filter dan cari transaksi</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('transactions.export', request()->query()) }}"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                Export Excel
            </a>
            <a href="{{ route('transactions.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                + Transaksi Baru
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="mt-6">
        <div class="grid gap-4 grid-cols-2 md:grid-cols-6">
            <div>
                <label class="text-xs uppercase font-semibold text-slate-500">Pencarian Umum</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari invoice, pelanggan, produk..." autofocus
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="text-xs uppercase font-semibold text-slate-500">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="text-xs uppercase font-semibold text-slate-500">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="text-xs uppercase font-semibold text-slate-500">Status Pembayaran</label>
                <select name="payment_status"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ ($filters['payment_status'] ?? '') == 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="dp" {{ ($filters['payment_status'] ?? '') == 'dp' ? 'selected' : '' }}>DP (Kurang Bayar)</option>
                    <option value="unpaid" {{ ($filters['payment_status'] ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid (Belum Dibayar)</option>
                    <option value="cod_kurir" {{ ($filters['payment_status'] ?? '') == 'cod_kurir' ? 'selected' : '' }}>COD Kurir</option>
                </select>
            </div>
            <div class="col-span-2 md:col-span-1 flex items-end gap-2">
                <button type="submit"
                    class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('transactions.index') }}"
                    class="shrink-0 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50" title="Reset Filter">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
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
                            @php
                                $isRejectedOrCanceled = $transaction->status === 'rejected';
                                $paymentLabel = match (true) {
                                    $transaction->payment_status === 'paid' => 'Paid (Lunas)',
                                    $transaction->payment_method === 'cod_kurir' => 'COD Kurir',
                                    $transaction->payment_status === 'dp' => 'DP (Kurang Bayar)',
                                    $transaction->payment_status === 'unpaid' => 'Unpaid (Belum Dibayar)',
                                    default => ucfirst($transaction->payment_status),
                                };

                                $paymentClass = match (true) {
                                    $transaction->payment_status === 'paid' => 'text-green-600',
                                    $transaction->payment_method === 'cod_kurir' => 'text-sky-500',
                                    in_array($transaction->payment_status, ['dp', 'unpaid']) => 'text-red-600',
                                    default => 'text-slate-600',
                                };
                            @endphp
                            <p class="font-semibold text-slate-800">{{ $transaction->invoice_number }}</p>
                            <p class="text-xs text-slate-500 mt-1">Status: 
                                <span class="font-bold {{ $paymentClass }}">{{ $paymentLabel }}</span>
                            </p>
                            @if($transaction->paymentUser)
                                <p class="text-[13px] font-medium text-emerald-600 mt-0.5">{{ $transaction->paymentUser->name }}</p>
                            @endif
                            @if($isRejectedOrCanceled && $transaction->reject_reason)
                                <p class="mt-1.5 text-[11px] text-red-600 italic bg-red-50/50 p-1.5 rounded-md border border-red-100/50">Problem : <br>"{{ $transaction->reject_reason }}"</p>
                            @endif
                            @if(in_array($transaction->payment_status, ['dp', 'unpaid']) && $transaction->due_date && !$isRejectedOrCanceled)
                                <p class="mt-1 text-xs font-medium text-red-600">
                                    Jatuh Tempo: {{ $transaction->due_date->format('d M Y') }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $transaction->user?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <div class="font-medium text-slate-900">{{ $transaction->customer?->name ?? 'Umum' }}</div>
                            <div class="mt-1 space-y-1 border-l-2 border-slate-100 pl-2">
                                @foreach($transaction->items as $item)
                                    <div class="text-xs">
                                        <div class="text-slate-600">
                                            {{ $item->custom_name ?? $item->product?->name ?? 'Produk terhapus' }}
                                            @if($item->notes)
                                                <span class="text-slate-400 italic">"{{ $item->notes }}"</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $transaction->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">Rp {{ number_format($transaction->total, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('transactions.show', $transaction) }}"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Detail
                                </a>
                                <button type="button"
                                    class="invoice-preview-trigger rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600"
                                    data-preview-url="{{ route('transactions.invoice_a5', $transaction) }}"
                                    data-spk-url="{{ route('transactions.spk', $transaction) }}">
                                    Invoice
                                </button>
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
                                <button type="button"
                                    onclick="openDeleteModal('{{ route('transactions.destroy', $transaction) }}', '{{ $transaction->invoice_number }}')"
                                    class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50">
                                    Hapus
                                </button>
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
                @php $isRejectedOrCanceled = $transaction->trashed() || $transaction->status === 'rejected'; @endphp
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $transaction->invoice_number }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                        @if($transaction->paymentUser)
                            <p class="text-xs font-medium text-emerald-600 mt-1">{{ $transaction->paymentUser->name }}</p>
                        @endif
                        @if(in_array($transaction->payment_status, ['dp', 'unpaid']) && $transaction->due_date && !$isRejectedOrCanceled)
                            <p class="mt-1 text-xs font-medium text-red-600">
                                Jatuh Tempo: {{ $transaction->due_date->format('d M Y') }}
                            </p>
                        @endif
                        @if($isRejectedOrCanceled && $transaction->reject_reason)
                            <p class="mt-2 text-[11px] text-red-600 italic bg-red-50/50 p-1.5 rounded-md border border-red-100/50">"{{ $transaction->reject_reason }}"</p>
                        @endif
                    </div>
                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $isRejectedOrCanceled ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }}">
                        {{ $isRejectedOrCanceled ? 'Dibatalkan / Reject' : ucfirst($transaction->status) }}
                    </span>
                </div>

                <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Kasir:</span>
                        <span class="font-medium">{{ $transaction->user?->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-start justify-between">
                        <span class="text-xs text-slate-400">Pelanggan:</span>
                        <div class="text-right">
                            <span class="font-medium block text-slate-800">{{ $transaction->customer?->name ?? 'Umum' }}</span>
                            <div class="mt-1 space-y-1">
                                @foreach($transaction->items as $item)
                                    <div class="text-xs text-slate-500">
                                        {{ $item->custom_name ?? $item->product?->name ?? 'Produk terhapus' }}
                                        @if($item->notes)
                                            <span class="text-slate-400 italic">"{{ $item->notes }}"</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
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
                    <button type="button"
                        class="invoice-preview-trigger flex-1 min-w-[80px] rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-medium text-slate-600 hover:bg-slate-50"
                        data-preview-url="{{ route('transactions.invoice_a5', $transaction) }}"
                        data-spk-url="{{ route('transactions.spk', $transaction) }}">
                        Invoice
                    </button>
                    <a target="_blank" href="{{ route('transactions.spk', $transaction) }}"
                        class="flex-1 min-w-[80px] rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-600 hover:bg-emerald-100">
                        SPK
                    </a>
                    @if(!$isRejectedOrCanceled)
                        @can('edit_transactions')
                        <a href="{{ route('transactions.edit', $transaction) }}"
                            class="flex-1 min-w-[80px] rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-center text-xs font-medium text-amber-600 hover:bg-amber-100">
                            Edit
                        </a>
                        @endcan
                        @can('delete_transactions')
                        <button type="button"
                            onclick="openDeleteModal('{{ route('transactions.destroy', $transaction) }}', '{{ $transaction->invoice_number }}')"
                            class="flex-1 min-w-[80px] rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-center text-xs font-medium text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                        @endcan
                    @endif
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

    <div id="print-preview-modal"
        class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 px-4 backdrop-blur-sm">
        <div class="flex h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                    <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Pratinjau Cetak
                </h3>
                <button type="button" id="close-print-preview"
                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-600">
                    <span class="sr-only">Tutup preview</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="relative flex-1 bg-slate-100">
                <iframe id="print-preview-frame" class="h-full w-full border-0" src=""></iframe>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4">
                <button type="button" id="print-spk-action"
                    class="hidden rounded-lg border border-slate-800 px-6 py-2.5 text-sm font-bold text-slate-800 transition-colors hover:bg-slate-100">
                    CETAK SPK
                </button>
                <button type="button" id="print-preview-action"
                    class="flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-500 hover:shadow-indigo-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak Dokumen
                </button>
                <button type="button" id="close-print-preview-secondary"
                    class="rounded-lg border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="delete-confirm-modal"
        class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/60 px-4 backdrop-blur-sm transition-all duration-300">
        <div id="delete-confirm-box"
            class="w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all duration-300 scale-95 opacity-0">
            <div class="flex flex-col items-center text-center">
                {{-- Warning Icon --}}
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-bold text-slate-800">Hapus Transaksi?</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Transaksi <span id="delete-invoice-label" class="font-semibold text-slate-700"></span>
                    akan dihapus dan stok produk akan dikembalikan.
                </p>
                <p class="mt-1 text-xs text-red-500 font-medium">Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                    Batal
                </button>
                <form id="delete-confirm-form" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-200 transition-all hover:bg-red-500 hover:shadow-red-300">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const previewModal = document.getElementById('print-preview-modal');
            const previewFrame = document.getElementById('print-preview-frame');
            const closeButton = document.getElementById('close-print-preview');
            const closeSecondaryButton = document.getElementById('close-print-preview-secondary');
            const printButton = document.getElementById('print-preview-action');
            const printSpkButton = document.getElementById('print-spk-action');

            if (!previewModal || !previewFrame) {
                return;
            }

            const openPreview = (url, spkUrl) => {
                previewFrame.src = url;
                if (printSpkButton && spkUrl) {
                    printSpkButton.dataset.spkUrl = spkUrl;
                    printSpkButton.classList.remove('hidden');
                } else if (printSpkButton) {
                    printSpkButton.classList.add('hidden');
                }
                previewModal.classList.remove('hidden');
                previewModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closePreview = () => {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
                previewFrame.src = '';
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('.invoice-preview-trigger').forEach((trigger) => {
                trigger.addEventListener('click', function () {
                    const url = this.dataset.previewUrl;
                    const spkUrl = this.dataset.spkUrl;
                    if (url) {
                        openPreview(url, spkUrl);
                    }
                });
            });

            closeButton?.addEventListener('click', closePreview);
            closeSecondaryButton?.addEventListener('click', closePreview);

            printButton?.addEventListener('click', function () {
                previewFrame.contentWindow?.print();
            });

            printSpkButton?.addEventListener('click', function () {
                const spkUrl = this.dataset.spkUrl;
                if (spkUrl) {
                    window.open(spkUrl, '_blank');
                }
            });

            previewModal.addEventListener('click', function (event) {
                if (event.target === previewModal) {
                    closePreview();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    if (!previewModal.classList.contains('hidden')) {
                        closePreview();
                    }
                    closeDeleteModal();
                }
            });
        });

        // Delete Confirmation Modal
        function openDeleteModal(actionUrl, invoiceNumber) {
            const modal = document.getElementById('delete-confirm-modal');
            const box = document.getElementById('delete-confirm-box');
            const form = document.getElementById('delete-confirm-form');
            const label = document.getElementById('delete-invoice-label');

            form.action = actionUrl;
            label.textContent = invoiceNumber;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            // Animate in
            requestAnimationFrame(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            });

            // Close on backdrop click
            modal.onclick = function (e) {
                if (e.target === modal) closeDeleteModal();
            };
        }

        function closeDeleteModal() {
            const modal = document.getElementById('delete-confirm-modal');
            const box = document.getElementById('delete-confirm-box');

            if (!modal || modal.classList.contains('hidden')) return;

            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }, 200);
        }
    </script>
@endpush
