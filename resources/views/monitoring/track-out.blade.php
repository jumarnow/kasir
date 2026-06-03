@extends('layouts.app')

@section('title', 'Track Out Order')
@section('subtitle', 'Selesaikan pengerjaan produksi atau desain')

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
            <h3 class="font-bold text-slate-800">Detail Order <span id="detail-invoice" class="text-emerald-600"></span></h3>
            <span id="detail-status" class="px-3 py-1 rounded-full text-xs font-semibold"></span>
        </div>
        <div class="p-6">
            <form id="track-out-form" action="{{ route('monitoring.track-out.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_number" id="form-invoice-number">
                <input type="hidden" name="track_type" id="form-track-type">
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
                    <p class="text-sm text-slate-500 mb-2">Pilih Item yang akan di Track Out</p>
                    <div class="bg-slate-50 rounded-lg border border-slate-200 overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2 w-10 text-center">
                                        <input type="checkbox" id="check-all" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
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
                
                @can('monitoring_admin_out')
                <div id="pickup-method-container" class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-100 hidden">
                    <label class="block text-sm font-bold text-indigo-900 mb-3">Status Pengambilan (Khusus Admin)</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pickup_method" value="customer" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700">Diambil Customer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pickup_method" value="kurir" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700">Diambil Kurir</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pickup_method" value="diantar" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700">Diantar ke Lokasi</span>
                        </label>
                    </div>
                </div>
                @endcan

                <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="btn-cancel" class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    
                    <button type="button" id="btn-track-design" class="px-6 py-2.5 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 transition-colors flex items-center gap-2 shadow-sm shadow-violet-200" onclick="submitTrackOut('design')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        TRACK OUT DESIGN
                    </button>
                    
                    <button type="button" id="btn-track-production" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors flex items-center gap-2 shadow-sm shadow-emerald-200" onclick="submitTrackOut('production')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        TRACK OUT PRODUKSI
                    </button>
                    
                    @can('monitoring_admin_out')
                    <button type="button" id="btn-track-admin" class="px-6 py-2.5 bg-sky-600 text-white rounded-lg font-medium hover:bg-sky-700 transition-colors flex items-center gap-2 shadow-sm shadow-sky-200" onclick="submitTrackOut('admin')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        TRACK OUT ADMIN (FINAL)
                    </button>
                    @endcan
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
                    
                    let canDesignOutAny = false;
                    let canProductionOutAny = false;
                    let canAdminOutAny = false;
                    
                    res.items.forEach(item => {
                        const dim = item.dimensions ? `<br><span class="text-xs text-slate-500">${item.dimensions}</span>` : '';
                        const custom = item.custom_name ? ` - ${item.custom_name}` : '';
                        
                        let checkHtml = '';
                        // Can check if at least one tracking out action is possible
                        if (item.can_design_out || item.can_production_out || item.can_admin_out) {
                            checkHtml = `<input type="checkbox" name="item_ids[]" value="${item.id}" 
                                data-can-design-out="${item.can_design_out}" 
                                data-can-production-out="${item.can_production_out}" 
                                data-can-admin-out="${item.can_admin_out}" 
                                class="item-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">`;
                            
                            if (item.can_design_out) canDesignOutAny = true;
                            if (item.can_production_out) canProductionOutAny = true;
                            if (item.can_admin_out) canAdminOutAny = true;
                        } else {
                            checkHtml = `<input type="checkbox" disabled class="rounded border-slate-200 bg-slate-100">`;
                        }

                        let statusBadge = `<span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">${item.status}</span>`;

                        tbody.append(`
                            <tr class="${(item.can_design_out || item.can_production_out || item.can_admin_out) ? 'hover:bg-emerald-50/30' : 'bg-slate-50/50 opacity-75'}">
                                <td class="px-4 py-3 text-center">${checkHtml}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">${item.product_name}${custom}${dim}</td>
                                <td class="px-4 py-3 text-slate-600">${item.qty}</td>
                                <td class="px-4 py-3">${statusBadge}</td>
                            </tr>
                        `);
                    });

                    // Update Check All Logic
                    $('#check-all').prop('checked', false).prop('disabled', (!canDesignOutAny && !canProductionOutAny && !canAdminOutAny));
                    
                    // Initial buttons state
                    updateActionButtons();
                    
                    const statusBadge = $('#detail-status');
                    
                    $('#form-invoice-number').val(res.invoice_number);
                    
                    // Uncheck radios
                    $('input[name="pickup_method"]').prop('checked', false);

                    $('#order-detail').removeClass('hidden').hide().fadeIn();
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
            updateActionButtons();
        });

        $(document).on('change', '.item-checkbox', function() {
            const total = $('.item-checkbox:not(:disabled)').length;
            const checked = $('.item-checkbox:checked').length;
            $('#check-all').prop('checked', total === checked && total > 0);
            updateActionButtons();
        });

        function updateActionButtons() {
            let canDesign = true;
            let canProduction = true;
            let canAdmin = true;
            let countChecked = 0;

            $('.item-checkbox:checked').each(function() {
                countChecked++;
                if ($(this).data('can-design-out') !== true) canDesign = false;
                if ($(this).data('can-production-out') !== true) canProduction = false;
                if ($(this).data('can-admin-out') !== true) canAdmin = false;
            });

            if (countChecked === 0) {
                canDesign = false;
                canProduction = false;
                canAdmin = false;
            }

            $('#btn-track-design').prop('disabled', !canDesign).toggleClass('opacity-50 cursor-not-allowed', !canDesign);
            $('#btn-track-production').prop('disabled', !canProduction).toggleClass('opacity-50 cursor-not-allowed', !canProduction);
            $('#btn-track-admin').prop('disabled', !canAdmin).toggleClass('opacity-50 cursor-not-allowed', !canAdmin);
        }

        $('#btn-cancel').on('click', function() {
            $('#order-detail').fadeOut(function() {
                $(this).addClass('hidden');
                $('#pickup-method-container').addClass('hidden');
            });
            barcodeInput.focus();
        });
    });

    function submitTrackOut(type) {
        if ($('.item-checkbox:checked').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Pilih minimal satu item untuk di-Track Out!'
            });
            return;
        }

        if (type === 'admin') {
            const pickupContainer = $('#pickup-method-container');
            if (pickupContainer.hasClass('hidden')) {
                // Tampilkan opsi pickup method
                pickupContainer.removeClass('hidden').hide().slideDown();
                // Scroll to pickup method
                $('html, body').animate({
                    scrollTop: pickupContainer.offset().top - 100
                }, 300);
                return;
            }

            // Validasi radio button
            if ($('input[name="pickup_method"]:checked').length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Status Pengambilan',
                    text: 'Silakan pilih metode pengambilan untuk Track Out Final!'
                });
                return;
            }
        }

        $('#form-track-type').val(type);
        $('#track-out-form').submit();
    }
</script>
@endpush
