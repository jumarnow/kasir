@extends('layouts.app')

@section('title', 'Transaksi Baru')
@section('subtitle', 'Proses penjualan dengan pemindaian barcode dan perhitungan otomatis')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
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
            font-size: 0.875rem;
            line-height: 1.25rem;
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
            font-size: 0.875rem;
        }
    </style>
@endpush

@if (session('print_invoice') && session('printed_transaction_id'))
    @push('scripts')
        <script>
            window.addEventListener('load', function () {
                if (sessionStorage.getItem('kasirInvoicePrintRequested') !== '1') {
                    return;
                }

                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
                } catch (error) {
                    // ignore storage errors
                }

                const features = 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes';
                const invoiceWindow = window.open('', 'invoice-print', features);

                if (invoiceWindow) {
                    invoiceWindow.location.replace('{{ route('transactions.invoice', ['transaction' => session('printed_transaction_id')]) }}');
                    invoiceWindow.focus();
                }
            });
        </script>
    @endpush
@endif

@section('content')
    <form action="{{ route('transactions.store') }}" method="POST" id="transaction-form">
        @csrf
        <input type="hidden" name="print_invoice" id="print-invoice" value="0">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">Detail Pelanggan</h2>
                    <p class="text-sm text-slate-500">Optional, kosongkan jika pelanggan umum</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs uppercase text-slate-500">Pelanggan</label>
                            <select name="customer_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="">Umum</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs uppercase text-slate-500">Catatan</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Catatan khusus">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800">Item Transaksi</h2>
                            <p class="text-sm text-slate-500">Gunakan barcode scanner atau pilih produk</p>
                        </div>
                        <div class="flex flex-col gap-3 md:flex-row">
                            <div class="relative">
                                <label class="text-xs uppercase text-slate-500">Barcode / SKU</label>
                                <input type="text" id="barcode-input" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Scan barcode..." autofocus>
                                <span class="absolute inset-y-0 right-3 top-6 flex items-center text-slate-400">📷</span>
                            </div>
                            <div class="flex items-end gap-2">
                                <div>
                                    <label class="text-xs uppercase text-slate-500">Pilih Produk</label>
                                    <select id="product-select" class="mt-1 w-60 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-cost="{{ $product->cost_price ?? $product->price }}" data-stock="{{ $product->stock }}">
                                                {{ $product->name }} (Stok: {{ $product->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="add-product" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Produk</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Harga</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items" class="divide-y divide-slate-100"></tbody>
                        </table>
                        <div class="p-4 text-center text-sm text-slate-400" id="empty-cart">Belum ada produk ditambahkan</div>
                    </div>
                    <div id="items-inputs"></div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">Ringkasan Pembayaran</h2>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Subtotal</span>
                            <span id="summary-subtotal" class="font-semibold text-slate-700">Rp 0</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs uppercase text-slate-500">Diskon (%)</label>
                                <input type="number" min="0" max="100" step="0.5" name="discount_percent" id="discount-percent" value="{{ old('discount_percent', 0) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div>
                                <label class="text-xs uppercase text-slate-500">Diskon (Rp)</label>
                                <input type="text" name="discount_amount" id="discount-amount" value="{{ old('discount_amount', 0) }}" class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Total Diskon</span>
                            <span id="summary-discount">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-base font-semibold text-slate-800">
                            <span>Total</span>
                            <span id="summary-total" class="text-indigo-600">Rp 0</span>
                        </div>
                        <div>
                            <label class="text-xs uppercase text-slate-500">Metode Pembayaran</label>
                            <select name="payment_method" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs uppercase text-slate-500">Jumlah Bayar (Rp)</label>
                            <input type="text" name="amount_paid" id="amount-paid" value="{{ old('amount_paid', 0) }}" class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Kembalian</span>
                            <span id="summary-change" class="font-semibold text-slate-700">Rp 0</span>
                        </div>

                    <div class="mt-6 flex flex-col gap-3">
                        <button type="submit" id="transaction-submit" class="rounded-full bg-emerald-500 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                            Simpan Transaksi
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="print-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Cetak invoice?</h3>
                    <p class="mt-1 text-sm text-slate-500">Transaksi akan disimpan terlebih dahulu. Lanjutkan ke cetak invoice dengan printer thermal?</p>
                </div>
                <button type="button" id="print-modal-close" class="text-slate-400 hover:text-slate-600">
                    <span class="sr-only">Tutup</span>
                    &times;
                </button>
            </div>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row-reverse sm:justify-end">
                <button type="button" id="print-modal-confirm" class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400">
                    Cetak Invoice
                </button>
                <button type="button" id="print-modal-skip" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Simpan Saja
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const productsData = @json($products);
        const cart = [];
        const printWindowFeatures = 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes';
        let $addProductButton;
        let $submitButton;
        let $productSelect;
        let $printInvoiceInput;
        let $printModal;
        let $printModalConfirm;
        let $printModalSkip;
        let $printModalClose;
        let printChoiceConfirmed = false;
        let pendingSubmitForm = null;
        let preOpenedPrintWindow = null;

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
            const emptyState = $('#empty-cart');
            const inputsWrapper = $('#items-inputs');
            tbody.empty();
            inputsWrapper.empty();

            if (cart.length === 0) {
                emptyState.show();
            } else {
                emptyState.hide();
            }

            cart.forEach((item, index) => {
                const subtotal = item.quantity * item.price;

                const row = $(`
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-700">${item.name}</p>
                            <p class="text-xs text-slate-400">Stok: ${item.stock}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="number" min="1" class="qty-input w-20 rounded-lg border border-slate-200 px-2 py-1 text-center text-sm" data-index="${index}" value="${item.quantity}">
                        </td>
                        <td class="px-4 py-3 text-right text-slate-600">
                            ${formatCurrency(item.price)}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-700">
                            ${formatCurrency(subtotal)}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="remove-item text-xs text-red-500 hover:text-red-600" data-index="${index}">Hapus</button>
                        </td>
                    </tr>
                `);

                tbody.append(row);

                inputsWrapper.append(`
                    <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}" class="item-quantity" data-index="${index}">
                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                    <input type="hidden" name="items[${index}][cost_price]" value="${item.cost_price}">
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
            const total = Math.max(subtotal - totalDiscount, 0);
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

        function addProductToCart(product) {
            const existing = cart.find(item => item.id === product.id);
            if (existing) {
                if (existing.quantity + 1 > product.stock) {
                    alert('Stok produk tidak mencukupi.');
                    return;
                }
                existing.quantity += 1;
            } else {
                if (product.stock < 1) {
                    alert('Stok produk habis.');
                    return;
                }
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: Number(product.price),
                    cost_price: Number(product.cost_price ?? product.price),
                    stock: product.stock,
                    quantity: 1,
                });
            }
            renderCart();
        }

        $(function () {
            const $barcodeInput = $('#barcode-input');
            $productSelect = $('#product-select').select2({
                placeholder: '-- Pilih Produk --',
                allowClear: true,
                width: 'resolve',
            });
            $addProductButton = $('#add-product');
            $submitButton = $('#transaction-submit');
            $printInvoiceInput = $('#print-invoice');
            $printModal = $('#print-confirm-modal');
            $printModalConfirm = $('#print-modal-confirm');
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

            $addProductButton.on('click', function () {
                const productId = $productSelect.val();
                if (!productId) {
                    alert('Pilih produk terlebih dahulu.');
                    return;
                }
                const product = productsData.find(p => p.id == productId);
                if (product) {
                    addProductToCart(product);
                    $productSelect.val(null).trigger('change');
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

            $('#cart-items').on('change', '.qty-input', function () {
                const index = $(this).data('index');
                const quantity = Number($(this).val());
                if (quantity < 1) {
                    $(this).val(cart[index].quantity);
                    return;
                }
                if (quantity > cart[index].stock) {
                    alert('Stok tidak mencukupi.');
                    $(this).val(cart[index].stock);
                    return;
                }
                cart[index].quantity = quantity;
                renderCart();
            });

            $('#cart-items').on('click', '.remove-item', function () {
                const index = $(this).data('index');
                cart.splice(index, 1);
                renderCart();
            });

            $(document).on('input', '#discount-percent, #discount-amount, #amount-paid', function () {
                const raf = window.requestAnimationFrame || function (cb) { return setTimeout(cb, 0); };
                raf(updateSummary);
            });

            $printModalConfirm.on('click', function () {
                if ($printInvoiceInput) {
                    $printInvoiceInput.val('1');
                }
                printChoiceConfirmed = true;
                hidePrintModal();
                try {
                    sessionStorage.setItem('kasirInvoicePrintRequested', '1');
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

            $printModalSkip.on('click', function () {
                if ($printInvoiceInput) {
                    $printInvoiceInput.val('0');
                }
                printChoiceConfirmed = true;
                hidePrintModal();
                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
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

            $('#transaction-form').on('submit', function (event) {
                const form = this;
                const { total, amountPaid } = calculateSummary();

                if (cart.length === 0) {
                    alert('Tambahkan minimal satu produk.');
                    event.preventDefault();
                    return false;
                }
                if (total > 0 && amountPaid <= 0) {
                    alert('Masukkan jumlah pembayaran.');
                    event.preventDefault();
                    return false;
                }
                if (amountPaid < total) {
                    alert('Jumlah pembayaran kurang dari total.');
                    event.preventDefault();
                    return false;
                }

                if (!printChoiceConfirmed) {
                    event.preventDefault();
                    pendingSubmitForm = form;
                    showPrintModal();
                    return false;
                }

                printChoiceConfirmed = false;
                pendingSubmitForm = null;

                updateSummary();
                return true;
            });
        });
    </script>
@endpush
