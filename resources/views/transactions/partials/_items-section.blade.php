{{-- Items Section --}}
<div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-slate-800">Item Transaksi</h2>
            <p class="text-xs md:text-sm text-slate-500">Scan barcode, pilih produk, atau tambah manual</p>
        </div>

        {{-- Mode Toggle --}}
        <div class="flex items-center gap-2 rounded-xl bg-slate-100 p-1">
            <button type="button" id="mode-product" class="item-mode-btn active rounded-lg px-3 py-1.5 text-xs font-semibold transition-all" data-mode="product">
                📦 Produk
            </button>
            <button type="button" id="mode-custom" class="item-mode-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all" data-mode="custom">
                ✏️ Manual
            </button>
        </div>
    </div>

    {{-- Product Mode --}}
    <div id="product-mode-inputs" class="mt-4">
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

    {{-- Custom/Manual Mode --}}
    <div id="custom-mode-inputs" class="mt-4 hidden">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-[2]">
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Nama Produk</label>
                <input type="text" id="custom-product-name"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Ketik nama produk...">
            </div>
            <div class="flex-1">
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Qty</label>
                <input type="number" id="custom-qty" min="1" value="1"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="1">
            </div>
            <div class="flex-1">
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Harga</label>
                <input type="text" id="custom-price" inputmode="numeric" autocomplete="off"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-base md:text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="0">
            </div>
            <button type="button" id="add-custom-product"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50"
                disabled>
                + Tambah
            </button>
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

<style>
    .item-mode-btn {
        color: #64748b;
        background: transparent;
    }
    .item-mode-btn.active {
        color: #fff;
        background: #4f46e5;
        box-shadow: 0 1px 3px rgba(79, 70, 229, 0.3);
    }
</style>
