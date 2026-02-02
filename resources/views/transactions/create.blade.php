@extends('layouts.app')

@section('title', 'Transaksi Baru')
@section('subtitle', 'Proses penjualan dengan pemindaian barcode dan perhitungan otomatis')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.css">
    <style>
        /* Select2 Custom Styling */
        .select2-container {
            width: 100% !important;
        }

        /* Slim Select Custom Styling */
        .ss-main {
            padding: 0.15rem 0.5rem !important;
            border-radius: 0.75rem !important;
            border: 1px solid rgb(226 232 240) !important;
            min-height: 42px;
        }

        .ss-main:focus {
            box-shadow: 0 0 0 2px rgb(199 210 254) !important;
            border-color: rgb(99 102 241) !important;
        }

        .ss-content {
            border-radius: 0.75rem !important;
            border: 1px solid rgb(226 232 240) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .ss-list .ss-option:hover {
            background-color: rgb(248 250 252) !important;
            color: rgb(79 70 229) !important;
        }

        .ss-list .ss-option.ss-selected {
            background-color: rgb(238 242 255) !important;
            color: rgb(79 70 229) !important;
        }

        .select2-container .select2-selection--single {
            height: auto;
            padding: 0.5rem 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid rgb(226 232 240);
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgb(51 65 85);
            font-size: 1rem;
            line-height: 1.5rem;
            padding: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 0.75rem;
        }

        .select2-dropdown {
            border-radius: 0.75rem;
            border: 1px solid rgb(226 232 240);
        }

        .select2-results__option {
            font-size: 1rem;
        }
    </style>
@endpush

@if ((session('print_invoice') || session('print_shipping_label')) && session('printed_transaction_id'))
    @push('scripts')
        <script>
            window.addEventListener('load', function () {
                if (sessionStorage.getItem('kasirInvoicePrintRequested') !== '1' && sessionStorage.getItem('kasirShippingLabelPrintRequested') !== '1') {
                    return;
                }

                const transactionId = '{{ session('printed_transaction_id') }}';
                const printInvoice = sessionStorage.getItem('kasirInvoicePrintRequested') === '1';
                const printShipping = sessionStorage.getItem('kasirShippingLabelPrintRequested') === '1';

                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
                    sessionStorage.removeItem('kasirShippingLabelPrintRequested');
                } catch (error) {
                    // ignore storage errors
                }

                const features = 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes';

                if (printInvoice) {
                    const invoiceWindow = window.open('{{ route('transactions.invoice', ['transaction' => session('printed_transaction_id')]) }}', 'invoice-print', features);
                    if (invoiceWindow) invoiceWindow.focus();
                }

                if (printShipping) {
                    const shippingWindow = window.open('{{ route('transactions.shipping_label', ['transaction' => session('printed_transaction_id')]) }}', 'shipping-print', 'width=400,height=600');
                    if (shippingWindow) shippingWindow.focus();
                }
            });
        </script>
    @endpush
@endif

@section('content')
    <form action="{{ route('transactions.store') }}" method="POST" id="transaction-form">
        @csrf
        <input type="hidden" name="print_invoice" id="print-invoice" value="0">
        <input type="hidden" name="print_shipping_label" id="print-shipping-label" value="0">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-200">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-800">Detail Pelanggan</h2>
                            <p class="text-xs text-slate-500">Optional, kosongkan jika pelanggan umum</p>
                        </div>
                        <button type="button" id="customer-section-toggle"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-500">
                            Tampilkan
                        </button>
                    </div>
                    <div id="customer-section-body" class="mt-4 hidden overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr class="flex flex-col md:table-row">
                                    <td
                                        class="w-full md:w-1/3 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 block md:table-cell">
                                        <div class="flex items-center justify-between">
                                            <span>Pelanggan</span>
                                            <button type="button" id="btn-quick-customer"
                                                class="text-[10px] font-bold text-indigo-600 hover:underline">
                                                + BARU
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 block md:table-cell">
                                        <select name="customer_id" id="customer-select"
                                            class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-base md:text-sm focus:ring-0">
                                            <option value="" data-price-tier="1">Umum</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    data-price-tier="{{ $customer->price_tier ?? 1 }}"
                                                    @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr class="flex flex-col md:table-row">
                                    <td
                                        class="w-full md:w-1/3 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 block md:table-cell">
                                        Catatan
                                    </td>
                                    <td class="px-4 py-2 block md:table-cell">
                                        <input type="text" name="notes" value="{{ old('notes') }}"
                                            class="w-full border-none bg-transparent px-0 py-1 text-base md:text-sm focus:ring-0"
                                            placeholder="Tambahkan catatan khusus transaksi...">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-base md:text-lg font-semibold text-slate-800">Item Transaksi</h2>
                            <p class="text-xs md:text-sm text-slate-500">Scan barcode atau pilih produk</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative flex-1">
                                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Barcode /
                                    SKU</label>
                                <input type="text" id="barcode-input"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                    placeholder="Scan..." autofocus>
                                <span class="absolute inset-y-0 right-3 top-7 flex items-center text-slate-400">📷</span>
                            </div>
                            <div class="flex items-end gap-2 flex-[2]">
                                <div class="flex-1">
                                    <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Pilih
                                        Produk</label>
                                    <select id="product-select"
                                        class="mt-1 w-full lg:w-60 rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                                data-price-2="{{ $product->price_2 ?? 0 }}"
                                                data-price-3="{{ $product->price_3 ?? 0 }}"
                                                data-cost="{{ $product->cost_price ?? $product->price }}"
                                                data-stock="{{ $product->stock }}"
                                                data-stock-alert="{{ $product->stock_alert ?? 0 }}"
                                                data-pricing-type="{{ $product->pricing_type }}"
                                                data-price-per-meter="{{ $product->price_per_meter ?? 0 }}"
                                                data-min-width="{{ $product->min_width ?? 0 }}"
                                                data-min-length="{{ $product->min_length ?? 0 }}">
                                                {{ $product->name }} (Stok: {{ $product->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="dimension-inputs" class="hidden flex gap-2">
                                    <div class="w-16">
                                        <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">P
                                            (cm)</label>
                                        <input type="number" id="input-length"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 text-sm text-center focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                            placeholder="0">
                                    </div>
                                    <div class="w-16">
                                        <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">L
                                            (cm)</label>
                                        <input type="number" id="input-width"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 text-sm text-center focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                            placeholder="0">
                                    </div>
                                </div>
                                <!-- Extra options wrapper -->
                                <div class="hidden flex gap-2" id="extra-inputs">
                                    <div class="w-24">
                                        <label
                                            class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Finishing</label>
                                        <select id="input-finishing"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                            <option value="">-</option>
                                            @foreach($finishings as $f)
                                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-24">
                                        <label
                                            class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Display</label>
                                        <select id="input-display"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                            <option value="">-</option>
                                            @foreach($displays as $d)
                                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="button" id="add-product"
                                    class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                    disabled>
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                        <!-- Desktop Table -->
                        <table class="min-w-full divide-y divide-slate-200 text-sm hidden md:table">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Produk</th>
                                    <th class="px-4 py-3 text-center">Harga</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items" class="divide-y divide-slate-100"></tbody>
                        </table>

                        <!-- Mobile List -->
                        <div id="cart-items-mobile" class="md:hidden divide-y divide-slate-100"></div>

                        <div class="p-8 text-center text-sm text-slate-400" id="empty-cart">
                            <div class="text-3xl mb-2">🛒</div>
                            Belum ada produk ditambahkan
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-5 md:p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">Ringkasan Pembayaran</h2>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Subtotal</span>
                            <span id="summary-subtotal" class="font-semibold text-slate-700">Rp 0</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Diskon (%)</label>
                                <input type="number" min="0" max="100" step="0.5" name="discount_percent"
                                    id="discount-percent" value="{{ old('discount_percent', 0) }}"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div>
                                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Diskon (Rp)</label>
                                <input type="text" name="discount_amount" id="discount-amount"
                                    value="{{ old('discount_amount', 0) }}"
                                    class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Total Diskon</span>
                            <span id="summary-discount" class="font-medium">Rp 0</span>
                        </div>
                        <div>
                            <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Ongkir (Rp)</label>
                            <input type="text" name="shipping_cost" id="shipping-cost" value="{{ old('shipping_cost', 0) }}"
                                class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>

                        <div class="py-3 border-y border-dashed border-slate-200">
                            <div class="flex items-center justify-between text-lg font-bold text-slate-800">
                                <span>Total</span>
                                <span id="summary-total" class="text-indigo-600">Rp 0</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Pembayaran</label>
                                <select name="payment_method"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                    <option value="cash">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Dibayar
                                    (Rp)</label>
                                <input type="text" name="amount_paid" id="amount-paid" value="{{ old('amount_paid', 0) }}"
                                    class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Kembalian</span>
                            <span id="summary-change" class="font-bold text-slate-800">Rp 0</span>
                        </div>

                        <div class="pt-2">
                            <button type="submit" id="transaction-submit"
                                class="w-full rounded-full bg-indigo-600 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 transition-all disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none"
                                disabled>
                                Simpan & Cetak Transaksi
                            </button>
                        </div>
                    </div>
                </div>
                <div id="items-inputs"></div>
            </div>
        </div>
    </form>

    <div id="print-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Cetak invoice?</h3>
                    <p class="mt-1 text-sm text-slate-500">Transaksi akan disimpan terlebih dahulu. Lanjutkan ke cetak
                        invoice dengan printer thermal?</p>
                </div>
                <button type="button" id="print-modal-close" class="text-slate-400 hover:text-slate-600">
                    <span class="sr-only">Tutup</span>
                    &times;
                </button>
            </div>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row-reverse sm:justify-end">
                <button type="button" id="print-modal-confirm"
                    class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400">
                    Cetak Invoice
                </button>
                <button type="button" id="print-modal-shipping"
                    class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    Cetak Resi
                </button>
                <button type="button" id="print-modal-skip"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Simpan Saja
                </button>
            </div>
        </div>
    </div>
    <div id="quick-customer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4 mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Pelanggan Baru</h3>
                <button type="button" id="close-quick-customer" class="text-slate-400 hover:text-slate-600">
                    <span class="sr-only">Tutup</span>
                    &times;
                </button>
            </div>
            <form id="quick-customer-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Nama Lengkap</label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                            placeholder="Nama pelanggan">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Nomor Telepon</label>
                        <input type="text" name="phone"
                            class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                            placeholder="08...">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Email (Opsional)</label>
                        <input type="email" name="email"
                            class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                            placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Alamat (Opsional)</label>
                        <textarea name="address" rows="2"
                            class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                            placeholder="Alamat lengkap"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="cancel-quick-customer"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.min.js"></script>
    <script>
        const productsData = @json($products);
        const customersData = @json($customers);
        const cart = [];
        const printWindowFeatures = 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes';
        let $addProductButton, $submitButton, $productSelect, productSlimSelect;
        let $printInvoiceInput;
        let $printShippingInput;
        let $printModal;
        let $printModalConfirm;
        let $printModalShipping;
        let $printModalSkip;
        let $printModalClose;
        let printChoiceConfirmed = false;
        let pendingSubmitForm = null;
        let preOpenedPrintWindow = null;
        let currentPriceTier = 1;

        // Quick Customer Modal Logic
        const $quickCustomerModal = $('#quick-customer-modal');
        const $btnQuickCustomer = $('#btn-quick-customer');
        const $btnCloseQuickCustomer = $('#close-quick-customer');
        const $btnCancelQuickCustomer = $('#cancel-quick-customer');
        const $quickCustomerForm = $('#quick-customer-form');

        function toggleQuickCustomerModal(show) {
            if (show) {
                $quickCustomerModal.removeClass('hidden').addClass('flex');
            } else {
                $quickCustomerModal.addClass('hidden').removeClass('flex');
                $quickCustomerForm[0].reset();
            }
        }

        $btnQuickCustomer.on('click', () => toggleQuickCustomerModal(true));
        $btnCloseQuickCustomer.on('click', () => toggleQuickCustomerModal(false));
        $btnCancelQuickCustomer.on('click', () => toggleQuickCustomerModal(false));

        $quickCustomerForm.on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const $submitBtn = $(this).find('button[type="submit"]');

            $submitBtn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: '{{ route('customers.store') }}',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    // Add to dropdown
                    const newOption = new Option(response.name, response.id, true, true);
                    $(newOption).data('price-tier', response.price_tier || 1);
                    $('#customer-select').append(newOption).trigger('change');

                    // Close modal
                    toggleQuickCustomerModal(false);

                    // Trigger tier update manually since trigger change might not suffice if logic depends on data attr
                    currentPriceTier = response.price_tier || 1;
                    if (cart.length > 0) updateCartPrices(currentPriceTier);

                    alert('Pelanggan berhasil ditambahkan!');
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan.';
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).flat().join('\n');
                    }
                    alert(msg);
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).text('Simpan');
                }
            });
        });

        function formatCurrency(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        }

        function parseCurrency(value) {
            if (value === null || value === undefined) {
                return 0;
            }

            if (typeof value !== 'string') {
                value = String(value);
            }

            const normalized = value
                .replace(/[^\d,.-]/g, '')
                .replace(/\.(?=\d{3}(?:[\.,]|$))/g, '')
                .replace(',', '.');

            const parsed = parseFloat(normalized);

            return Number.isFinite(parsed) ? parsed : 0;
        }

        function renderCart() {
            const tbody = $('#cart-items');
            const mobileList = $('#cart-items-mobile');
            const emptyState = $('#empty-cart');
            const inputsWrapper = $('#items-inputs');
            tbody.empty();
            mobileList.empty();
            inputsWrapper.empty();

            if (cart.length === 0) {
                emptyState.show();
            } else {
                emptyState.hide();
            }

            cart.forEach((item, index) => {
                const subtotal = item.quantity * item.price;
                const isDimension = item.pricing_type === 'per_dimension';

                // Build price options (only show if not dimension pricing?)
                // Actually even for dimension pricing, base price per meter might change? 
                // Let's assume price selection is mainly for Unit Items (Tier 1, 2, 3)
                // For Dimension items, price is calculated, so maybe we show just text or allow override?
                // For simplicity: If dimension, show formatted price (read-only sort of). 
                // If unit, show dropdown.

                let priceField = '';
                let priceOptions = `<option value="${item.price_1}">${formatCurrency(item.price_1)}</option>`;
                if (item.price_2 > 0) priceOptions += `<option value="${item.price_2}">${formatCurrency(item.price_2)}</option>`;
                if (item.price_3 > 0) priceOptions += `<option value="${item.price_3}">${formatCurrency(item.price_3)}</option>`;

                priceField = `
                                     <select class="price-select w-28 rounded-lg border border-slate-200 px-2 py-1 text-sm ${isDimension ? 'bg-slate-100' : ''}" data-index="${index}" ${isDimension ? 'disabled' : ''}>
                                            ${priceOptions}
                                     </select>
                                `;


                // Helper to build options
                const buildOptions = (data, selected) => {
                    let html = '<option value="">-</option>';
                    data.forEach(opt => {
                        const isSel = opt.id == selected ? 'selected' : '';
                        html += `<option value="${opt.id}" ${isSel}>${opt.name}</option>`;
                    });
                    return html;
                };

                const finishingsOpts = buildOptions(finishingsData, item.finishing_id);
                const displaysOpts = buildOptions(displaysData, item.display_id);

                // Inputs for Dimensions
                // If not dimension type, disable or hide inputs? Let's hide or readonly.
                const dimInputStyle = "w-16 rounded-lg border border-slate-200 px-2 py-1 text-center text-sm";
                const pInput = isDimension
                    ? `<input type="number" min="0" class="item-length ${dimInputStyle}" data-index="${index}" value="${item.length || 0}">`
                    : `<input type="number" disabled class="${dimInputStyle} bg-slate-50 text-slate-400" value="">`;

                const lInput = isDimension
                    ? `<input type="number" min="0" class="item-width ${dimInputStyle}" data-index="${index}" value="${item.width || 0}">`
                    : `<input type="number" disabled class="${dimInputStyle} bg-slate-50 text-slate-400" value="">`;

                // Desktop Row
                const row = $(`
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-700">${item.name}</p>
                                        <p class="text-xs text-slate-400">Stok: ${item.stock}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        ${priceField}
                                    </td>
                                     <td class="px-4 py-3 text-center">${pInput}</td>
                                     <td class="px-4 py-3 text-center">${lInput}</td>
                                     <td class="px-4 py-3 text-center">
                                        <select class="item-finishing w-24 rounded-lg border border-slate-200 px-2 py-1 text-sm" data-index="${index}">${finishingsOpts}</select>
                                     </td>
                                     <td class="px-4 py-3 text-center">
                                        <select class="item-display w-24 rounded-lg border border-slate-200 px-2 py-1 text-sm" data-index="${index}">${displaysOpts}</select>
                                     </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" min="1" class="qty-input w-16 rounded-lg border border-slate-200 px-2 py-1 text-center text-sm" data-index="${index}" value="${item.quantity}">
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                        ${formatCurrency(subtotal)}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" class="remove-item text-xs text-red-500 hover:text-red-600" data-index="${index}">Hapus</button>
                                    </td>
                                </tr>
                            `);

                // Set selected price for non-dimension items (visually)
                if (!isDimension) {
                    row.find('.price-select').val(item.price);
                } else {
                    // For dimension, price is calculated, so we might want to show text instead of select?
                    // Or just show total unit price
                    // Let's replace the select with text for dimension items to avoid confusion
                    row.find('.price-select').parent().html(`<span class="text-sm">${formatCurrency(item.price)}</span>`);
                }

                tbody.append(row);

                // Mobile Item - simplified for now, maybe add an "Edit" button for details? 
                // Or just show inputs linearly.
                const mobileItem = $(`
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-medium text-slate-700">${item.name}</p>
                                        </div>
                                        <button type="button" class="remove-item text-xs text-red-500 hover:text-red-600 font-medium" data-index="${index}">Hapus</button>
                                    </div>
                                     <div class="grid grid-cols-2 gap-2 mb-2">
                                        ${isDimension
                        ? `<div><label class="text-[10px] text-slate-400">P</label><input type="number" class="item-length w-full border rounded px-2 py-1" data-index="${index}" value="${item.length || 0}"></div>
                                               <div><label class="text-[10px] text-slate-400">L</label><input type="number" class="item-width w-full border rounded px-2 py-1" data-index="${index}" value="${item.width || 0}"></div>`
                        : ''}
                                        <div><label class="text-[10px] text-slate-400">Finishing</label><select class="item-finishing w-full border rounded px-1 py-1" data-index="${index}">${finishingsOpts}</select></div>
                                        <div><label class="text-[10px] text-slate-400">Display</label><select class="item-display w-full border rounded px-1 py-1" data-index="${index}">${displaysOpts}</select></div>
                                     </div>

                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-xs text-slate-500">Harga</label>
                                             <span class="text-sm font-medium text-slate-700">${formatCurrency(item.price)}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <label class="text-xs text-slate-500">Qty</label>
                                            <input type="number" min="1" class="qty-input w-20 rounded-lg border border-slate-200 px-2 py-1 text-right text-sm" data-index="${index}" value="${item.quantity}">
                                        </div>
                                        <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                                            <span class="text-xs font-semibold text-slate-500">Subtotal</span>
                                            <span class="font-semibold text-slate-700">${formatCurrency(subtotal)}</span>
                                        </div>
                                    </div>
                                </div>
                            `);
                mobileList.append(mobileItem);

                inputsWrapper.append(`
                                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                                <input type="hidden" name="items[${index}][price]" value="${item.price}">
                                <input type="hidden" name="items[${index}][cost_price]" value="${item.cost_price}">
                                <input type="hidden" name="items[${index}][width]" value="${item.width || 0}">
                                <input type="hidden" name="items[${index}][length]" value="${item.length || 0}">
                                <input type="hidden" name="items[${index}][finishing_id]" value="${item.finishing_id || ''}">
                                <input type="hidden" name="items[${index}][display_id]" value="${item.display_id || ''}">
                            `);
            });

            updateSummary();
        }

        function updateSubmitButton(total, amountPaid) {
            if (!$submitButton) {
                return;
            }

            const hasPayment = amountPaid > 0 || total === 0;
            const isPaymentSufficient = amountPaid >= total;
            const canSubmit = cart.length > 0 && hasPayment && isPaymentSufficient;

            $submitButton.prop('disabled', !canSubmit);
        }

        function calculateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discountPercent = parseFloat($('#discount-percent').val()) || 0;
            const discountAmountInput = parseCurrency($('#discount-amount').val());
            const discountFromPercent = subtotal * (discountPercent / 100);
            const totalDiscount = Math.min(subtotal, discountAmountInput + discountFromPercent);
            const shippingCost = parseCurrency($('#shipping-cost').val());
            const total = Math.max(subtotal - totalDiscount + shippingCost, 0);
            const amountPaid = parseCurrency($('#amount-paid').val());
            const change = Math.max(amountPaid - total, 0);
            return { subtotal, totalDiscount, total, amountPaid, change };
        }

        function updateSummary() {
            const { subtotal, totalDiscount, total, amountPaid, change } = calculateSummary();

            $('#summary-subtotal').text(formatCurrency(subtotal));
            $('#summary-discount').text(formatCurrency(totalDiscount));
            $('#summary-total').text(formatCurrency(total));
            $('#summary-change').text(formatCurrency(change));
            updateSubmitButton(total, amountPaid);
        }

        function showPrintModal() {
            if (!$printModal) {
                return;
            }

            $printModal.removeClass('hidden').addClass('flex');
            $('body').addClass('overflow-hidden');
        }

        function hidePrintModal() {
            if (!$printModal) {
                return;
            }

            $printModal.addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }

        function updateCartPrices(tier) {
            cart.forEach(item => {
                if (tier === 2 && item.price_2 > 0) {
                    item.price = item.price_2;
                } else if (tier === 3 && item.price_3 > 0) {
                    item.price = item.price_3;
                } else {
                    item.price = item.price_1;
                }
            });
            renderCart();
        }

        function addProductToCart(product) {

            // Allow multiple rows for flexible customization
            /* 
               If we wanted to stack, we'd check cart.find() here.
               But since we edit dimensions inline, better to always push new row for simplicity 
               OR check if there is an existing row with 0 dimensions/default specs.
               For now, let's just push to allow duplicates which user can customize differently.
            */

            const stockAlert = Number(product.stock_alert || 0);

            if (product.stock < 1) {
                alert('Stok produk habis.');
                return;
            }

            let selectedPrice = Number(product.price);
            if (currentPriceTier === 2 && product.price_2 > 0) {
                selectedPrice = Number(product.price_2);
            } else if (currentPriceTier === 3 && product.price_3 > 0) {
                selectedPrice = Number(product.price_3);
            }

            // If per_dimension, initial price might be 0 until dimensions entered? 
            // Or we use base price if it represents 1m x 1m? 
            // Usually if width=0, length=0, area=0 -> price=0.
            if (product.pricing_type === 'per_dimension') {
                selectedPrice = 0;
            }

            cart.push({
                id: product.id,
                name: product.name,
                price: selectedPrice,
                price_1: Number(product.price),
                price_2: Number(product.price_2 || 0),
                price_3: Number(product.price_3 || 0),
                cost_price: Number(product.cost_price ?? product.price),
                stock: product.stock,
                stock_alert: stockAlert,
                quantity: 1,

                // New Fields
                pricing_type: product.pricing_type,
                price_per_meter: Number(product.price_per_meter || product.price),
                width: 0,
                length: 0,
                area: 0,
                finishing_id: null,
                display_id: null
            });

            if (stockAlert > 0 && (product.stock - 1) <= stockAlert) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: `Stok menipis: ${product.name}`,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            renderCart();
        }

        const finishingsData = @json($finishings ?? []);
        const displaysData = @json($displays ?? []);

        $(function () {
            const $barcodeInput = $('#barcode-input');

            // Initialize Slim Select for product select
            productSlimSelect = new SlimSelect({
                select: '#product-select',
                settings: {
                    placeholderText: '-- Pilih Produk --',
                    allowDeselect: true,
                },
                events: {
                    afterChange: (newVal) => {
                        toggleAddButton();
                    }
                }
            });

            $productSelect = $('#product-select');

            $('#customer-select').select2({
                placeholder: '-- Pilih Pelanggan --',
                allowClear: true,
                width: '100%',
            });
            $addProductButton = $('#add-product');
            $submitButton = $('#transaction-submit');
            $printInvoiceInput = $('#print-invoice');
            $printShippingInput = $('#print-shipping-label');
            $printModal = $('#print-confirm-modal');
            $printModalConfirm = $('#print-modal-confirm');
            $printModalShipping = $('#print-modal-shipping');
            $printModalSkip = $('#print-modal-skip');
            $printModalClose = $('#print-modal-close');

            const toggleAddButton = () => {
                const hasSelection = Boolean($productSelect.val());
                $addProductButton.prop('disabled', !hasSelection);
            };

            toggleAddButton();
            updateSummary();

            $productSelect.on('change', toggleAddButton);
            $productSelect.on('select2:clear', toggleAddButton);

            $barcodeInput.trigger('focus');
            setTimeout(() => $barcodeInput.trigger('focus'), 200);

            const $customerToggle = $('#customer-section-toggle');
            const $customerBody = $('#customer-section-body');
            const $customerSelectDropdown = $('#customer-select');
            let customerSectionCollapsed = true;

            $customerToggle.on('click', function () {
                customerSectionCollapsed = !customerSectionCollapsed;
                if (customerSectionCollapsed) {
                    $customerBody.slideUp(150);
                    $customerToggle.text('Tampilkan');
                } else {
                    $customerBody.slideDown(150);
                    $customerToggle.text('Sembunyikan');
                }
            });

            // Event handler untuk perubahan pelanggan - update tier harga
            $customerSelectDropdown.on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const priceTier = parseInt(selectedOption.data('price-tier')) || 1;
                currentPriceTier = priceTier;

                // Update semua harga di cart berdasarkan tier baru
                if (cart.length > 0) {
                    updateCartPrices(priceTier);
                }
            });

            $addProductButton.on('click', function () {
                const productId = $productSelect.val();
                if (!productId) {
                    alert('Pilih produk terlebih dahulu.');
                    return;
                }
                const product = productsData.find(p => p.id == productId);

                if (product) {
                    addProductToCart(product);
                    productSlimSelect.setSelected('');
                    toggleAddButton();
                }
            });

            $('#barcode-input').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (!barcode) return;
                    $.get('{{ route('transactions.lookup') }}', { barcode })
                        .done(function (data) {
                            addProductToCart(data);
                            $('#barcode-input').val('');
                        })
                        .fail(function () {
                            alert('Produk tidak ditemukan.');
                        });
                }
            });

            // Event listeners for inline table inputs
            $('#cart-items, #cart-items-mobile').on('change input', '.item-length, .item-width', function () {
                const index = $(this).data('index');

                // Get values
                const w = parseFloat($(`.item-width[data-index="${index}"]`).val()) || 0;
                const l = parseFloat($(`.item-length[data-index="${index}"]`).val()) || 0;

                const item = cart[index];
                item.width = w;
                item.length = l;

                // Recalculate price if dimension type
                if (item.pricing_type === 'per_dimension') {
                    // Assuming price_per_meter was stored in item during addProductToCart
                    // If not, we might need to rely on base 'price' if it's treated as per meter.
                    // Let's ensure addProductToCart stores 'price_per_meter'.
                    // If stored 'price' is the base meter price, we use that.
                    // But 'price' usually holds the Final Calculated Price.
                    const pricePerMeter = item.price_per_meter || item.price_1; // Fallback? Item.price_per_meter should be set.
                    const area = (w / 100) * (l / 100);
                    item.area = area;
                    item.price = Math.round(pricePerMeter * area);
                }

                const subtotal = item.quantity * item.price;

                // Update DOM elements manually to avoid full re-render (which kills focus)
                // Update Price
                const row = $('#cart-items tr').eq(index);
                // For non-dimension items, price is select. For dimension, it's text.
                // We targeted the span in renderCart for dimension items.
                if (item.pricing_type === 'per_dimension') {
                    row.find('td:nth-child(2) span').text(formatCurrency(item.price));
                    // Mobile
                    const mobItem = $('#cart-items-mobile > div').eq(index);
                    mobItem.find('span:contains("Harga")').next().text(formatCurrency(item.price));
                }

                // Update Subtotal (Desktop)
                row.find('td:nth-last-child(2)').text(formatCurrency(subtotal));
                // Update Subtotal (Mobile)
                $('#cart-items-mobile > div').eq(index).find('span:contains("Subtotal")').next().text(formatCurrency(subtotal));

                // Update hidden inputs
                $(`#items-inputs input[name="items[${index}][width]"]`).val(w);
                $(`#items-inputs input[name="items[${index}][length]"]`).val(l);
                $(`#items-inputs input[name="items[${index}][price]"]`).val(item.price);

                updateSummary();
            });

            $('#cart-items, #cart-items-mobile').on('change', '.item-finishing', function () {
                const index = $(this).data('index');
                cart[index].finishing_id = $(this).val();
                $(`#items-inputs input[name="items[${index}][finishing_id]"]`).val($(this).val());
            });

            $('#cart-items, #cart-items-mobile').on('change', '.item-display', function () {
                const index = $(this).data('index');
                cart[index].display_id = $(this).val();
                $(`#items-inputs input[name="items[${index}][display_id]"]`).val($(this).val());
            });

            $('#cart-items, #cart-items-mobile').on('change', '.qty-input', function () {
                const index = $(this).data('index');
                const quantity = Number($(this).val());
                const item = cart[index];
                const stockAlert = Number(item.stock_alert || 0);

                if (quantity < 1) { $(this).val(item.quantity); return; }
                if (quantity > item.stock) {
                    alert('Stok tidak mencukupi.');
                    $(this).val(item.stock);
                    return;
                }
                item.quantity = quantity;
                if (stockAlert > 0 && (item.stock - item.quantity) <= stockAlert) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: `Stok menipis: ${item.name}`, showConfirmButton: false, timer: 3000 });
                }

                // Since this updates subtotal and hidden inputs, simpler to re-render OR update manually. 
                // renderCart() is safer but loses focus. qty input usually OK to lose focus.
                renderCart();
            });

            $('#cart-items, #cart-items-mobile').on('click', '.remove-item', function () {
                const index = $(this).data('index');
                cart.splice(index, 1);
                renderCart();
            });

            $(document).on('input', '#discount-percent, #discount-amount, #amount-paid, #shipping-cost', function () {
                const raf = window.requestAnimationFrame || function (cb) { return setTimeout(cb, 0); };
                raf(updateSummary);
            });

            $printModalConfirm.on('click', function () {
                if ($printInvoiceInput) $printInvoiceInput.val('1');
                if ($printShippingInput) $printShippingInput.val('0');

                printChoiceConfirmed = true;
                hidePrintModal();
                try {
                    sessionStorage.setItem('kasirInvoicePrintRequested', '1');
                    sessionStorage.removeItem('kasirShippingLabelPrintRequested');
                } catch (error) {
                    // ignore storage failures
                }

                if (!preOpenedPrintWindow || preOpenedPrintWindow.closed) {
                    preOpenedPrintWindow = window.open('', 'invoice-print', printWindowFeatures);
                } else {
                    preOpenedPrintWindow.focus();
                }
                if (preOpenedPrintWindow) {
                    preOpenedPrintWindow.document.title = 'Invoice';
                    preOpenedPrintWindow.document.body.innerHTML = '<div style="font-family: sans-serif; padding: 16px; font-size: 14px;">Menunggu invoice...</div>';
                }
                if (pendingSubmitForm) {
                    $(pendingSubmitForm).trigger('submit');
                }
            });

            $printModalShipping.on('click', function () {
                if ($printInvoiceInput) $printInvoiceInput.val('0');
                if ($printShippingInput) $printShippingInput.val('1');

                printChoiceConfirmed = true;
                hidePrintModal();
                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
                    sessionStorage.setItem('kasirShippingLabelPrintRequested', '1');
                } catch (error) {
                    // ignore storage failures
                }

                if (!preOpenedPrintWindow || preOpenedPrintWindow.closed) {
                    preOpenedPrintWindow = window.open('', 'shipping-print', 'width=400,height=600');
                } else {
                    preOpenedPrintWindow.focus();
                }
                if (preOpenedPrintWindow) {
                    preOpenedPrintWindow.document.title = 'Resi';
                    preOpenedPrintWindow.document.body.innerHTML = '<div style="font-family: sans-serif; padding: 16px; font-size: 14px;">Menunggu resi...</div>';
                }

                if (pendingSubmitForm) {
                    $(pendingSubmitForm).trigger('submit');
                }
            });

            $printModalSkip.on('click', function () {
                if ($printInvoiceInput) $printInvoiceInput.val('0');
                if ($printShippingInput) $printShippingInput.val('0');

                printChoiceConfirmed = true;
                hidePrintModal();
                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
                    sessionStorage.removeItem('kasirShippingLabelPrintRequested');
                } catch (error) {
                    // ignore storage failures
                }
                if (pendingSubmitForm) {
                    $(pendingSubmitForm).trigger('submit');
                }
            });

            $printModalClose.on('click', hidePrintModal);

            $printModal.on('click', function (event) {
                if (event.target === this) {
                    hidePrintModal();
                }
            });

            $(document).on('keydown', function (event) {
                if (event.key === 'Escape' && !$printModal.hasClass('hidden')) {
                    hidePrintModal();
                }
            });

            // Toggle Due Date base on Payment Method
            $('#payment-method').on('change', function () {
                if ($(this).val() === 'tempo') {
                    $('#due-date-wrapper').removeClass('hidden');
                    $('#due-date').prop('required', true);
                } else {
                    $('#due-date-wrapper').addClass('hidden');
                    $('#due-date').prop('required', false);
                }
            });

            $('#transaction-form').on('submit', function (event) {
                event.preventDefault();
                const form = this;
                const { total, amountPaid } = calculateSummary();
                const paymentMethod = $('#payment-method').val();

                if (cart.length === 0) { alert('Tambahkan minimal satu produk.'); return false; }
                if (amountPaid < total && paymentMethod !== 'tempo') {
                    // warn partial payment
                }

                const formData = $(this).serialize();
                $submitButton.prop('disabled', true).text('Memproses...');

                $.ajax({
                    url: '{{ route('transactions.store') }}',
                    method: 'POST',
                    data: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    success: function (response) {
                        if (response.status === 'success') {
                            const txId = response.transaction_id;
                            const features = 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes';

                            if (response.print_spk) setTimeout(() => window.open(`/transactions/${txId}/spk`, '_blank', features), 500);
                            if (response.print_receipt) setTimeout(() => window.open(`/transactions/${txId}/receipt`, '_blank', features), 1000);
                            if (response.print_invoice) setTimeout(() => window.open(`/transactions/${txId}/invoice-a5`, '_blank', features), 1000);

                            Swal.fire({
                                icon: 'success',
                                title: 'Transaksi Berhasil',
                                text: 'Transaksi disimpan dan dokumen sedang dicetak/dibuka.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function (xhr) {
                        $submitButton.prop('disabled', false).text('Simpan & Cetak');
                        let msg = 'Gagal menyimpan transaksi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        if (xhr.status === 422) {
                            const errs = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            msg += '\n' + errs;
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });
    </script>
@endpush