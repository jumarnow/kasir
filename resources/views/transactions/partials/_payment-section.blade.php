{{-- Payment Section --}}
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
                <input type="number" min="0" max="100" step="0.5" name="discount_percent" id="discount-percent"
                    value="{{ old('discount_percent', 0) }}"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Diskon (Rp)</label>
                <input type="text" name="discount_amount" id="discount-amount" value="{{ old('discount_amount', 0) }}"
                    class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
        </div>
        <div class="flex items-center justify-between text-sm text-slate-500">
            <span>Total Diskon</span>
            <span id="summary-discount" class="font-medium">Rp 0</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Pengiriman</label>
                <select name="delivery_method" id="delivery-method"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Tidak Ada</option>
                    <option value="cod_kurir">COD Kurir</option>
                    <option value="kurir_online">Kurir Online</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Ongkir (Rp)</label>
                <input type="text" name="shipping_cost" id="shipping-cost" value="{{ old('shipping_cost', 0) }}"
                    class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
        </div>
        <div class="flex items-center justify-between text-sm text-slate-500">
            <span>Ongkir</span>
            <span id="summary-shipping" class="font-medium">Rp 0</span>
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
                <select name="payment_method" id="payment-method"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="lunas" {{ old('payment_method') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="dp" {{ old('payment_method') === 'dp' ? 'selected' : '' }}>DP</option>
                    <option value="pending" {{ old('payment_method') === 'pending' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="cod_kurir" {{ old('payment_method') === 'cod_kurir' ? 'selected' : '' }}>COD Kurir</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Dibayar (Rp)</label>
                <input type="text" name="amount_paid" id="amount-paid" value="{{ old('amount_paid', 0) }}"
                    class="currency-input mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>
        </div>

        <div id="due-date-container" class="hidden mt-4">
            <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Jatuh Tempo</label>
            <input type="date" name="due_date" id="due-date" value="{{ old('due_date', date('Y-m-d')) }}"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
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
