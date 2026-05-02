{{-- All Modal Dialogs --}}

{{-- Print Confirm Modal --}}
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

{{-- Quick Customer Modal --}}
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

{{-- Print Preview Modal --}}
<div id="print-preview-modal"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 px-4 backdrop-blur-sm">
    <div class="w-full max-w-4xl h-[85vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Pratinjau Cetak
            </h3>
            <button type="button" id="close-print-preview"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-colors">
                <span class="sr-only">Tutup & Selesai</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 bg-slate-100 relative">
            <iframe id="print-preview-frame" class="w-full h-full border-0" src=""></iframe>
        </div>
        <div class="border-t border-slate-200 px-6 py-4 bg-white flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('print-preview-frame').contentWindow.print()"
                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 hover:shadow-indigo-300 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Cetak Dokumen
            </button>
            <button type="button" onclick="$('#close-print-preview').trigger('click')"
                class="rounded-lg border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Selesai & Transaksi Baru
            </button>
        </div>
    </div>
</div>

{{-- Item Detail Modal --}}
<div id="item-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-800" id="modal-item-name">Detail Item</h3>
                <p class="text-xs text-slate-500">Sesuaikan spesifikasi produk</p>
            </div>
            <button type="button" id="close-item-detail" class="text-slate-400 hover:text-slate-600">
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>
        <form id="item-detail-form">
            <input type="hidden" id="modal-item-index">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Quantity</label>
                    <input type="number" id="modal-qty" min="1"
                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div id="modal-product-price-tier-wrapper" class="col-span-2 hidden">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Harga Satuan</label>
                    <select id="modal-product-price-tier"
                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <!-- Options populated via JS -->
                    </select>
                </div>

                <!-- Dimensions -->
                <div id="modal-dimensions-wrapper" class="col-span-2 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Panjang (cm)</label>
                        <input type="number" id="modal-length" min="0" step="0.1"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Lebar (cm)</label>
                        <input type="number" id="modal-width" min="0" step="0.1"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                </div>

                <!-- Finishing (selalu muncul untuk semua tipe produk) -->
                <div id="modal-finishing-wrapper" class="col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Finishing</label>
                    <select id="modal-finishing"
                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">- Pilih Finishing -</option>
                        @foreach($finishings as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Options (khusus per_dimension) -->
                <!-- <div id="modal-options-wrapper" class="col-span-2 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Material</label>
                        <select id="modal-material"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">- Pilih Material -</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="modal-material-tier-wrapper" class="hidden">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Harga Material</label>
                        <select id="modal-material-tier"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="1">Harga Utama</option>
                            <option value="2">Harga 2</option>
                            <option value="3">Harga 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Display</label>
                        <select id="modal-display"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">- Pilih Display -</option>
                            @foreach($displays as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> -->

                <div class="col-span-2 pt-2 border-t border-slate-100 mt-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-slate-600">Estimasi Harga</span>
                        <span class="text-lg font-bold text-indigo-600" id="modal-price-display">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="cancel-item-detail"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Note Modal --}}
<div id="note-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4 mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Catatan Item</h3>
            <button type="button" id="close-note-modal" class="text-slate-400 hover:text-slate-600">
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>
        <form id="note-form">
            <input type="hidden" id="note-item-index">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Catatan Khusus</label>
                    <textarea id="modal-note-text" rows="3"
                        class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                        placeholder="Contoh: Warna merah, Jangan dilipat, dll"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="cancel-note-modal"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>