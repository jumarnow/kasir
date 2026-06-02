@extends('layouts.app')

@section('title', 'Track In Order')
@section('subtitle', 'Mulai pengerjaan produksi atau desain')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Scan Barcode / Input Manual</h2>
            <form id="lookup-form" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Invoice (Barcode)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <input type="text" id="barcode-input" class="pl-10 form-input w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Scan barcode di sini..." autofocus>
                    </div>
                </div>
                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-2.5 rounded-lg font-medium transition-colors">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <div id="order-detail" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hidden">
        <div class="border-b border-slate-200 px-6 py-4 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800">Detail Order <span id="detail-invoice" class="text-indigo-600"></span></h3>
            <span id="detail-status" class="px-3 py-1 rounded-full text-xs font-semibold"></span>
        </div>
        <div class="p-6">
            <form action="{{ route('monitoring.track-in.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_number" id="form-invoice-number">
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                    <p class="text-sm text-slate-500 mb-1">Customer</p>
                    <p class="font-medium text-slate-900" id="detail-customer"></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Tanggal Pesan</p>
                    <p class="font-medium text-slate-900" id="detail-date"></p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-slate-500 mb-2">Pilih Item yang akan di Track In</p>
                    <div class="bg-slate-50 rounded-lg border border-slate-200 overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2 w-10 text-center">
                                        <input type="checkbox" id="check-all" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-4 py-2 font-semibold text-slate-700">Produk</th>
                                    <th class="px-4 py-2 font-semibold text-slate-700">Qty</th>
                                    <th class="px-4 py-2 font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody id="detail-items-table" class="divide-y divide-slate-100">
                                <!-- Items injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="btn-cancel" class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm shadow-indigo-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                        TRACK IN SEKARANG
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const barcodeInput = $('#barcode-input');
        
        // Auto submit if length matches typical invoice length (optional, depending on scanner behavior)
        let typingTimer;
        barcodeInput.on('keyup', function () {
            clearTimeout(typingTimer);
            if (barcodeInput.val().length > 5) {
                typingTimer = setTimeout(() => $('#lookup-form').submit(), 500);
            }
        });

        barcodeInput.on('keydown', function () {
            clearTimeout(typingTimer);
        });

        $('#lookup-form').on('submit', function(e) {
            e.preventDefault();
            const barcode = barcodeInput.val().trim();
            if (!barcode) return;

            // Loading state
            const btn = $(this).find('button[type="submit"]');
            const originalText = btn.text();
            btn.text('Mencari...').prop('disabled', true);

            $.ajax({
                url: '{{ route("monitoring.lookup") }}',
                data: { barcode: barcode },
                success: function(res) {
                    $('#detail-invoice').text(res.invoice_number);
                    $('#detail-customer').text(res.customer_name);
                    $('#detail-date').text(res.created_at);
                    
                    const tbody = $('#detail-items-table');
                    tbody.empty();
                    
                    let canTrackInAny = false;
                    
                    res.items.forEach(item => {
                        const dim = item.dimensions ? `<br><span class="text-xs text-slate-500">${item.dimensions}</span>` : '';
                        const custom = item.custom_name ? ` - ${item.custom_name}` : '';
                        
                        let checkHtml = '';
                        if (item.can_track_in) {
                            checkHtml = `<input type="checkbox" name="item_ids[]" value="${item.id}" class="item-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">`;
                            canTrackInAny = true;
                        } else {
                            checkHtml = `<input type="checkbox" disabled class="rounded border-slate-200 bg-slate-100">`;
                        }

                        let statusBadge = `<span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">${item.status}</span>`;

                        tbody.append(`
                            <tr class="${item.can_track_in ? 'hover:bg-indigo-50/30' : 'bg-slate-50/50 opacity-75'}">
                                <td class="px-4 py-3 text-center">${checkHtml}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">${item.product_name}${custom}${dim}</td>
                                <td class="px-4 py-3 text-slate-600">${item.qty}</td>
                                <td class="px-4 py-3">${statusBadge}</td>
                            </tr>
                        `);
                    });

                    // Update Check All Logic
                    $('#check-all').prop('checked', false).prop('disabled', !canTrackInAny);
                    
                    const statusBadge = $('#detail-status');

                    $('#form-invoice-number').val(res.invoice_number);
                    $('#order-detail').removeClass('hidden').hide().fadeIn();
                    
                    // Reset input
                    barcodeInput.val('');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON?.message || 'Gagal mencari transaksi'
                    });
                    $('#order-detail').addClass('hidden');
                },
                complete: function() {
                    btn.text(originalText).prop('disabled', false);
                    barcodeInput.focus();
                }
            });
        });

        // Check all functionality
        $(document).on('change', '#check-all', function() {
            $('.item-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        });

        $(document).on('change', '.item-checkbox', function() {
            const total = $('.item-checkbox:not(:disabled)').length;
            const checked = $('.item-checkbox:checked').length;
            $('#check-all').prop('checked', total === checked && total > 0);
        });

        $('#btn-cancel').on('click', function() {
            $('#order-detail').fadeOut(function() {
                $(this).addClass('hidden');
            });
            barcodeInput.focus();
        });
    });
</script>
@endpush
