{{-- Execution Section --}}
<div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
        <h2 class="text-sm font-semibold text-slate-700">Detail Eksekusi</h2>
        <p class="text-[11px] text-slate-400">Penanggung jawab pesanan</p>
    </div>

    {{-- Desainer --}}
    <div class="grid grid-cols-[10rem_1fr] items-center border-b border-slate-100">
        <div class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-50/60 flex items-center gap-1">
            Desainer
            <span class="normal-case font-normal text-slate-300 text-[10px]">opt.</span>
        </div>
        <div class="px-3 py-1.5">
            <select name="desainer_id" id="desainer-select"
                class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-sm focus:ring-0 text-slate-700">
                <option value="">— Pilih Desainer —</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('desainer_id', isset($transaction) ? $transaction->desainer_id : null) == $employee->id)>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Eksekutor 1 --}}
    <div class="grid grid-cols-[10rem_1fr] items-center border-b border-slate-100">
        <div class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-50/60">
            Eksekutor 1
        </div>
        <div class="px-3 py-1.5">
            <select name="eksekutor_id" id="eksekutor-select"
                class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-sm focus:ring-0 text-slate-700">
                <option value="">— Pilih Eksekutor 1 —</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('eksekutor_id', isset($transaction) ? $transaction->eksekutor_id : null) == $employee->id)>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Eksekutor 2 --}}
    <div class="grid grid-cols-[10rem_1fr] items-center {{ isset($transaction) ? 'border-b border-slate-100' : '' }}">
        <div class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-50/60 flex items-center gap-1">
            Eksekutor 2
            <span class="normal-case font-normal text-slate-300 text-[10px]">opt.</span>
        </div>
        <div class="px-3 py-1.5">
            <select name="eksekutor_2_id" id="eksekutor-2-select"
                class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-sm focus:ring-0 text-slate-700">
                <option value="">— Pilih Eksekutor 2 —</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('eksekutor_2_id', isset($transaction) ? $transaction->eksekutor_2_id : null) == $employee->id)>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tanggal Transaksi (edit mode only) --}}
    @if(isset($transaction))
        <div class="grid grid-cols-[10rem_1fr] items-center">
            <div class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-50/60">
                Tgl. Transaksi
            </div>
            <div class="px-3 py-1.5">
                @if(auth()->user()->hasRole('finance') || auth()->user()->hasRole('manager'))
                    <input type="datetime-local" name="created_at"
                        value="{{ $transaction->created_at->format('Y-m-d\TH:i') }}"
                        class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-sm focus:ring-0 text-slate-700">
                @else
                    <span class="text-sm text-slate-600">{{ $transaction->created_at->format('d M Y H:i') }}</span>
                @endif
            </div>
        </div>
    @endif
</div>