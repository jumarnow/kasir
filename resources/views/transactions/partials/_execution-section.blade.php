{{-- Execution Section --}}
<div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-800">Detail Eksekusi</h2>
            <p class="text-xs text-slate-500">Pilih penanggung jawab pesanan ini</p>
        </div>
    </div>
    <div class="mt-4">
        <select name="eksekutor_id" id="eksekutor-select"
            class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-base md:text-sm focus:ring-0">
            <option value="">-- Pilih Eksekutor 1 --</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('eksekutor_id') == $employee->id)>
                    {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mt-4 border-t border-dashed border-slate-200 pt-4">
        <select name="eksekutor_2_id" id="eksekutor-2-select"
            class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-base md:text-sm focus:ring-0">
            <option value="">-- Pilih Eksekutor 2 (Opsional) --</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('eksekutor_2_id') == $employee->id)>
                    {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>

    @if(isset($transaction))
        <div class="mt-4 border-t border-dashed border-slate-200 pt-4">
            <label class="text-[10px] md:text-xs uppercase font-bold text-slate-400">Tanggal Transaksi</label>
            @if(auth()->user()->hasRole('finance') || auth()->user()->hasRole('manager'))
                <input type="datetime-local" name="created_at" value="{{ $transaction->created_at->format('Y-m-d\TH:i') }}"
                    class="w-full rounded-lg border-none bg-transparent px-0 py-1 text-base md:text-sm focus:ring-0">
            @else
                <div class="w-full px-0 py-1 text-base md:text-sm text-slate-600">
                    {{ $transaction->created_at->format('d M Y H:i') }}
                </div>
            @endif
        </div>
    @endif
</div>