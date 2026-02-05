{{-- Items Section --}}
<div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-slate-800">Item Transaksi</h2>
            <p class="text-xs md:text-sm text-slate-500">Scan barcode atau pilih produk</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Barcode / SKU</label>
                <input type="text" id="barcode-input"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Scan..." autofocus>
                <span class="absolute inset-y-0 right-3 top-7 flex items-center text-slate-400">📷</span>
            </div>
            <div class="flex items-end gap-2 flex-[2]">
                <div class="flex-1">
                    <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Pilih Produk</label>
                    <select id="product-select"
                        class="mt-1 w-full lg:w-60 rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                data-price-2="{{ $product->price_2 ?? 0 }}" data-price-3="{{ $product->price_3 ?? 0 }}"
                                data-cost="{{ $product->cost_price ?? $product->price }}" data-stock="{{ $product->stock }}"
                                data-stock-alert="{{ $product->stock_alert ?? 0 }}"
                                data-pricing-type="{{ $product->pricing_type }}"
                                data-price-per-meter="{{ $product->price_per_meter ?? 0 }}"
                                data-price-unit="{{ $product->price_unit ?? 'per_m2' }}"
                                data-min-width="{{ $product->min_width ?? 0 }}"
                                data-min-length="{{ $product->min_length ?? 0 }}">
                                {{ $product->name }} (Stok: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Extra inputs removed - moved to modal -->
                <div id="dimension-inputs" class="hidden"></div>
                <div id="extra-inputs" class="hidden"></div>
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
                    <th class="px-4 py-3 text-center">Tipe Harga</th>
                    <th class="px-4 py-3 text-center">Harga</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-right">Subtotal</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
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