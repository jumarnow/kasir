@extends('layouts.app')

@section('title', 'Transaksi Baru')
@section('subtitle', 'Proses penjualan dengan pemindaian barcode dan perhitungan otomatis')

@section('content')
    <form action="{{ route('transactions.store') }}" method="POST" id="transaction-form">
        @csrf
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
                                <button type="button" id="add-product" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
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
                                <input type="number" min="0" step="100" name="discount_amount" id="discount-amount" value="{{ old('discount_amount', 0) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
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
                            <input type="number" min="0" step="100" name="amount_paid" id="amount-paid" value="{{ old('amount_paid', 0) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Kembalian</span>
                            <span id="summary-change" class="font-semibold text-slate-700">Rp 0</span>
                        </div>

                    <div class="mt-6 flex flex-col gap-3">
                        <button type="submit" class="rounded-full bg-emerald-500 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400">
                            Simpan & Cetak Invoice
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const productsData = @json($products);
        const cart = [];

        function formatCurrency(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
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

        function updateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discountPercent = parseFloat($('#discount-percent').val()) || 0;
            const discountAmountInput = parseFloat($('#discount-amount').val()) || 0;
            const discountFromPercent = subtotal * (discountPercent / 100);
            const totalDiscount = Math.min(subtotal, discountAmountInput + discountFromPercent);
            const total = Math.max(subtotal - totalDiscount, 0);
            const amountPaid = parseFloat($('#amount-paid').val()) || 0;
            const change = Math.max(amountPaid - total, 0);

            $('#summary-subtotal').text(formatCurrency(subtotal));
            $('#summary-discount').text(formatCurrency(totalDiscount));
            $('#summary-total').text(formatCurrency(total));
            $('#summary-change').text(formatCurrency(change));
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
            $barcodeInput.trigger('focus');
            setTimeout(() => $barcodeInput.trigger('focus'), 200);

            $('#add-product').on('click', function () {
                const productId = $('#product-select').val();
                if (!productId) {
                    alert('Pilih produk terlebih dahulu.');
                    return;
                }
                const product = productsData.find(p => p.id == productId);
                if (product) {
                    addProductToCart(product);
                    $('#product-select').val('');
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

            $('#discount-percent, #discount-amount, #amount-paid').on('input', updateSummary);

            $('#transaction-form').on('submit', function () {
                if (cart.length === 0) {
                    alert('Tambahkan minimal satu produk.');
                    return false;
                }
                updateSummary();
                return true;
            });
        });
    </script>
@endpush
