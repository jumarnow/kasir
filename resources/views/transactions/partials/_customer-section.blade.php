{{-- Customer Section --}}
<div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-800">Detail Pelanggan</h2>
            <p class="text-xs text-slate-500">Optional, kosongkan jika pelanggan umum</p>
        </div>
    </div>
    <div id="customer-section-body" class="mt-4 overflow-hidden rounded-xl border border-slate-200">
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
                                <option value="{{ $customer->id }}" data-price-tier="{{ $customer->price_tier ?? 1 }}"
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