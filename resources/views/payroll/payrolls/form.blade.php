@extends('layouts.app')

@section('title', isset($payroll) ? 'Edit Slip Gaji' : 'Buat Slip Gaji')

@section('content')
    <div class="w-full">
        <div class="mb-6">
            <a href="{{ route('payrolls.index') }}"
                class="text-slate-500 hover:text-indigo-600 text-sm flex items-center gap-1 mb-2">
                ◀ Kembali ke Daftar
            </a>
            <h1 class="text-2xl font-bold text-slate-900">{{ isset($payroll) ? 'Edit Slip Gaji' : 'Buat Slip Gaji Baru' }}
            </h1>
            @if(isset($payroll))
                <p class="text-slate-500 text-sm">
                    {{ $payroll->employee->name }} - {{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }}
                    {{ $payroll->period_year }}
                </p>
            @endif
        </div>

        <form action="{{ isset($payroll) ? route('payrolls.update', $payroll) : route('payrolls.store') }}" method="POST">
            @csrf
            @if(isset($payroll))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column: Basic Info & Salary -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Data Pegawai & Periode (Only for Create) & Gaji Pokok -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h3 class="font-semibold text-slate-800 border-b pb-2 mb-4">Informasi Dasar</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            @if(!isset($payroll))
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Pilih Pegawai <span
                                            class="text-red-500">*</span></label>
                                    <select name="employee_id" id="employee_select"
                                        class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" data-salary="{{ $emp->basic_salary }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }} ({{ $emp->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Bulan <span
                                            class="text-red-500">*</span></label>
                                    <select name="period_month"
                                        class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ old('period_month', date('n')) == $m ? 'selected' : '' }}>
                                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tahun <span
                                            class="text-red-500">*</span></label>
                                    <select name="period_year"
                                        class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                        @foreach(range(date('Y'), 2024) as $y)
                                            <option value="{{ $y }}" {{ old('period_year', date('Y')) == $y ? 'selected' : '' }}>
                                                {{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="{{ isset($payroll) ? 'md:col-span-1' : 'md:col-span-2' }}">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Gaji Pokok (Rp) <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500 font-bold">Rp</span>
                                    <input type="text" name="basic_salary" id="basic_salary"
                                        value="{{ old('basic_salary', isset($payroll) ? number_format($payroll->basic_salary, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 bg-slate-50 font-semibold text-slate-700 focus:ring-indigo-500 focus:border-indigo-500 currency-input"
                                        required readonly>
                                </div>
                                @if(!isset($payroll))
                                    <p class="text-xs text-slate-500 mt-1">Otomatis terisi dari data pegawai.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tunjangan & Bonus -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h3 class="font-semibold text-slate-800 border-b pb-2 mb-4">Tunjangan & Bonus</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tunjangan Makan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="tunjangan_makan"
                                        value="{{ old('tunjangan_makan', isset($payroll) ? number_format($payroll->tunjangan_makan, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input calc-input"
                                        placeholder="0">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tunjangan Transport</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="tunjangan_transport"
                                        value="{{ old('tunjangan_transport', isset($payroll) ? number_format($payroll->tunjangan_transport, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input calc-input"
                                        placeholder="0">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tunjangan Jabatan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="tunjangan_jabatan"
                                        value="{{ old('tunjangan_jabatan', isset($payroll) ? number_format($payroll->tunjangan_jabatan, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input calc-input"
                                        placeholder="0">
                                </div>
                            </div>

                            <div class="md:col-start-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Bonus Kehadiran</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="bonus_kehadiran"
                                        value="{{ old('bonus_kehadiran', isset($payroll) ? number_format($payroll->bonus_kehadiran, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input calc-input"
                                        placeholder="0">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Bonus Target</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="bonus_target"
                                        value="{{ old('bonus_target', isset($payroll) ? number_format($payroll->bonus_target, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input calc-input"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Potongan -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h3 class="font-semibold text-slate-800 border-b pb-2 mb-4">Potongan</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Total Potongan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="potongan"
                                        value="{{ old('potongan', isset($payroll) ? number_format($payroll->potongan, 0, ',', '.') : 0) }}"
                                        class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input calc-input"
                                        placeholder="0">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Potongan</label>
                                <input type="text" name="potongan_notes"
                                    value="{{ old('potongan_notes', isset($payroll) ? $payroll->potongan_notes : '') }}"
                                    class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Contoh: Kasbon, Terlambat">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Summary & Actions -->
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sticky top-6">
                        <h3 class="font-bold text-lg text-slate-800 mb-4">Ringkasan Gaji</h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Gaji Pokok</span>
                                <span class="font-medium" id="summary_basic">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>Total Tunjangan (+)</span>
                                <span class="font-medium" id="summary_allowance">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>Total Bonus (+)</span>
                                <span class="font-medium" id="summary_bonus">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-red-600">
                                <span>Total Potongan (-)</span>
                                <span class="font-medium" id="summary_deduction">Rp 0</span>
                            </div>
                            <div class="border-t pt-3 mt-3 flex justify-between font-bold text-lg text-indigo-700">
                                <span>Gaji Bersih</span>
                                <span id="summary_net">Rp 0</span>
                            </div>
                        </div>

                        <div class="mt-8 space-y-3">
                            @if(!isset($payroll))
                                <button type="submit" name="save_as_draft" value="1"
                                    class="w-full py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition">
                                    Simpan Draft
                                </button>
                            @else
                                <button type="submit"
                                    class="w-full py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition">
                                    Simpan Perubahan
                                </button>
                            @endif

                            <button type="submit" name="save_and_print" value="1"
                                class="w-full py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition shadow-sm">
                                Simpan & Lihat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                const formatCurrency = (num) => {
                    return new Intl.NumberFormat('id-ID').format(num);
                };

                const parseCurrency = (str) => {
                    if (!str) return 0;
                    return parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;
                };

                const calculateTotal = () => {
                    const basic = parseCurrency($('#basic_salary').val());

                    const makan = parseCurrency($('input[name="tunjangan_makan"]').val());
                    const transport = parseCurrency($('input[name="tunjangan_transport"]').val());
                    const jabatan = parseCurrency($('input[name="tunjangan_jabatan"]').val());

                    const kehadiran = parseCurrency($('input[name="bonus_kehadiran"]').val());
                    const target = parseCurrency($('input[name="bonus_target"]').val());

                    const potongan = parseCurrency($('input[name="potongan"]').val());

                    const allowance = makan + transport + jabatan;
                    const bonus = kehadiran + target;
                    const net = basic + allowance + bonus - potongan;

                    $('#summary_basic').text('Rp ' + formatCurrency(basic));
                    $('#summary_allowance').text('Rp ' + formatCurrency(allowance));
                    $('#summary_bonus').text('Rp ' + formatCurrency(bonus));
                    $('#summary_deduction').text('Rp ' + formatCurrency(potongan));
                    $('#summary_net').text('Rp ' + formatCurrency(net));
                };

                // Auto filter salary on employee select
                $('#employee_select').on('change', function () {
                    const salary = $(this).find(':selected').data('salary') || 0;
                    // Format for display input
                    $('#basic_salary').val(formatCurrency(salary));
                    calculateTotal();
                });

                // Recalculate on input change
                $(document).on('input', '.calc-input', function () {
                    calculateTotal();
                });

                // Initial calc
                calculateTotal();

                // Strip non-numeric chars on submit
                $('form').on('submit', function () {
                    $('.currency-input').each(function () {
                        let val = $(this).val().replace(/[^0-9]/g, '');
                        $(this).val(val);
                    });
                });
            });
        </script>
    @endpush
@endsection