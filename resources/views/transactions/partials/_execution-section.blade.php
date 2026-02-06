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
            <option value="">-- Pilih Eksekutor --</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('eksekutor_id') == $employee->id)>
                    {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>