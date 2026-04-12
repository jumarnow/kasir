{{-- Customer Section --}}
<div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50">
        <div>
            <h2 class="text-sm font-semibold text-slate-700">Detail Pelanggan</h2>
            <p class="text-[11px] text-slate-400">Kosongkan jika pelanggan umum</p>
        </div>
    </div>
    <div id="customer-section-body">
        <div class="grid grid-cols-[10rem_1fr] items-center border-b border-slate-100 last:border-0">
            <div class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-50/60 flex items-center justify-between gap-2">
                <span>Pelanggan</span>
                <button type="button" id="btn-quick-customer"
                    class="text-[10px] font-bold text-indigo-500 hover:text-indigo-700 shrink-0">+ Baru</button>
            </div>
            <div class="px-3 py-1.5">
                <select name="customer_id" id="customer-select"
                    class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-sm focus:ring-0 text-slate-700">
                    <option value="" data-price-tier="1">— Umum —</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" data-price-tier="{{ $customer->price_tier ?? 1 }}"
                            @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-[10rem_1fr] items-center">
            <div class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-50/60">
                Catatan
            </div>
            <div class="px-3 py-1.5">
                <input type="text" name="notes" value="{{ old('notes') }}"
                    class="w-full border-none bg-transparent px-0 py-1 text-sm focus:ring-0 text-slate-700 placeholder:text-slate-300"
                    placeholder="Catatan khusus...">
            </div>
        </div>
    </div>
</div>