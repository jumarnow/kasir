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
                                            <option value="{{ $emp->id }}" 
                                                data-salary="{{ $emp->basic_salary }}"
                                                data-daily-salary="{{ $emp->daily_salary }}"
                                                data-employee-type="{{ $emp->employee_type }}"
                                                {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }} ({{ $emp->employee_id }}) - {{ $emp->employee_type_label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    
                                    <!-- Badge tipe karyawan -->
                                    <div id="employee_type_badge" class="mt-2 hidden">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" id="type_badge_text">
                                        </span>
                                    </div>
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

                            <!-- Input Hari Kerja (hanya tampil untuk karyawan harian) -->
                            <div id="working_days_container" class="md:col-span-2 {{ isset($payroll) && $payroll->isDailyPaid() ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Jumlah Hari Kerja <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-4">
                                    <input type="number" name="working_days" id="working_days"
                                        value="{{ old('working_days', isset($payroll) ? $payroll->working_days : 0) }}"
                                        class="w-32 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                        min="0" max="31">
                                    <span class="text-sm text-slate-500">hari</span>
                                    <span class="text-sm text-slate-500" id="daily_salary_info">
                                        @if(isset($payroll) && $payroll->isDailyPaid())
                                            × Rp {{ number_format($payroll->daily_salary, 0, ',', '.') }} per hari
                                        @endif
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    Masukkan jumlah hari kerja untuk periode ini
                                </p>
                            </div>

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
                                <p class="text-xs text-slate-500 mt-1" id="basic_salary_note">
                                    @if(isset($payroll) && $payroll->isDailyPaid())
                                        Otomatis: Hari Kerja × Gaji Harian
                                    @else
                                        Otomatis terisi dari data pegawai.
                                    @endif
                                </p>
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

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tunjangan Lembur</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                    <input type="text" name="tunjangan_lembur"
                                        value="{{ old('tunjangan_lembur', isset($payroll) ? number_format($payroll->tunjangan_lembur, 0, ',', '.') : 0) }}"
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
                        <div class="flex items-center justify-between border-b pb-2 mb-4">
                            <h3 class="font-semibold text-slate-800">Potongan</h3>
                            <button type="button" id="btn_add_potongan"
                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 rounded-lg px-3 py-1.5 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah Potongan
                            </button>
                        </div>

                        <div id="potongan_list" class="space-y-3">
                            @php
                                $potonganItems = old('potongan_items');
                                $potonganNotes = old('potongan_notes');
                                if (!$potonganItems) {
                                    if (isset($payroll) && $payroll->potongan_notes) {
                                        $parts = array_map('trim', explode(' | ', $payroll->potongan_notes));
                                        $amounts = [];
                                        $notes = [];
                                        foreach ($parts as $part) {
                                            if (preg_match('/^(.+?):\s*Rp\s*([\d.,]+)$/', $part, $m)) {
                                                $notes[] = trim($m[1]);
                                                $amounts[] = (int) str_replace(['.', ','], '', $m[2]);
                                            } else {
                                                $notes[] = $part;
                                                $amounts[] = 0;
                                            }
                                        }
                                        if (count($amounts) === 1 && $amounts[0] === 0 && $payroll->potongan > 0) {
                                            $amounts[0] = (int) $payroll->potongan;
                                        }
                                        $potonganItems = $amounts;
                                        $potonganNotes = $notes;
                                    } elseif (isset($payroll) && $payroll->potongan > 0) {
                                        $potonganItems = [(int) $payroll->potongan];
                                        $potonganNotes = [''];
                                    } else {
                                        $potonganItems = [0];
                                        $potonganNotes = [''];
                                    }
                                }
                            @endphp

                            @foreach($potonganItems as $i => $amount)
                                <div class="potongan-row flex items-start gap-2">
                                    <div class="relative flex-shrink-0 w-40">
                                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                                        <input type="text" name="potongan_items[]"
                                            value="{{ $amount > 0 ? number_format($amount, 0, ',', '.') : '' }}"
                                            class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input potongan-amount"
                                            placeholder="0">
                                    </div>
                                    <input type="text" name="potongan_notes_items[]"
                                        value="{{ $potonganNotes[$i] ?? '' }}"
                                        class="flex-1 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Catatan (Contoh: Kasbon)">
                                    <button type="button"
                                        class="btn-remove-potongan mt-1 p-2 text-slate-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition {{ count($potonganItems) <= 1 ? 'invisible' : '' }}"
                                        title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3 pt-3 border-t border-dashed border-slate-200 flex justify-between items-center text-sm">
                            <span class="text-slate-500">Total Potongan</span>
                            <span class="font-semibold text-red-600" id="total_potongan_display">Rp 0</span>
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
                                <span>Tunjangan Lembur (+)</span>
                                <span class="font-medium" id="summary_lembur">Rp 0</span>
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

                // Employee type badges
                const typeBadges = {
                    'permanent': { text: 'Karyawan Tetap', class: 'bg-blue-100 text-blue-800' },
                    'intern': { text: 'Karyawan Magang', class: 'bg-amber-100 text-amber-800' },
                    'internship': { text: 'Internship (PKL)', class: 'bg-green-100 text-green-800' }
                };

                let currentEmployeeType = 'permanent';
                let currentDailySalary = 0;

                const updateEmployeeUI = (type, dailySalary, basicSalary) => {
                    currentEmployeeType = type;
                    currentDailySalary = dailySalary;

                    const badge = typeBadges[type];
                    const $badge = $('#employee_type_badge');
                    const $badgeText = $('#type_badge_text');

                    // Update badge
                    if (badge) {
                        $badge.removeClass('hidden');
                        $badgeText.text(badge.text);
                        $badgeText.removeClass().addClass(
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + badge.class
                        );
                    }

                    // Show/hide working days input
                    const isDailyPaid = type === 'intern' || type === 'internship';
                    $('#working_days_container').toggleClass('hidden', !isDailyPaid);
                    
                    if (isDailyPaid) {
                        $('#daily_salary_info').text('× Rp ' + formatCurrency(dailySalary) + ' per hari');
                        $('#basic_salary_note').text('Otomatis: Hari Kerja × Gaji Harian');
                    } else {
                        $('#daily_salary_info').text('');
                        $('#basic_salary_note').text('Otomatis terisi dari data pegawai.');
                    }

                    // Calculate basic salary
                    if (isDailyPaid) {
                        const days = parseInt($('#working_days').val()) || 0;
                        const salary = dailySalary * days;
                        $('#basic_salary').val(formatCurrency(salary));
                    } else {
                        $('#basic_salary').val(formatCurrency(basicSalary));
                    }

                    calculateTotal();
                };

                const calculateTotal = () => {
                    const basic = parseCurrency($('#basic_salary').val());

                    const makan = parseCurrency($('input[name="tunjangan_makan"]').val());
                    const transport = parseCurrency($('input[name="tunjangan_transport"]').val());
                    const jabatan = parseCurrency($('input[name="tunjangan_jabatan"]').val());
                    const lembur = parseCurrency($('input[name="tunjangan_lembur"]').val());

                    const kehadiran = parseCurrency($('input[name="bonus_kehadiran"]').val());
                    const target = parseCurrency($('input[name="bonus_target"]').val());

                    let potongan = 0;
                    $('.potongan-amount').each(function () {
                        potongan += parseCurrency($(this).val());
                    });

                    const allowance = makan + transport + jabatan;
                    const bonus = kehadiran + target;
                    const net = basic + allowance + lembur + bonus - potongan;

                    $('#summary_basic').text('Rp ' + formatCurrency(basic));
                    $('#summary_allowance').text('Rp ' + formatCurrency(allowance));
                    $('#summary_lembur').text('Rp ' + formatCurrency(lembur));
                    $('#summary_bonus').text('Rp ' + formatCurrency(bonus));
                    $('#summary_deduction').text('Rp ' + formatCurrency(potongan));
                    $('#total_potongan_display').text('Rp ' + formatCurrency(potongan));
                    $('#summary_net').text('Rp ' + formatCurrency(net));
                };

                // On employee select (only for create mode)
                $('#employee_select').on('change', function () {
                    const $selected = $(this).find(':selected');
                    if (!$selected.val()) {
                        $('#employee_type_badge').addClass('hidden');
                        $('#working_days_container').addClass('hidden');
                        $('#basic_salary').val(0);
                        calculateTotal();
                        return;
                    }
                    
                    const basicSalary = parseFloat($selected.data('salary')) || 0;
                    const dailySalary = parseFloat($selected.data('daily-salary')) || 0;
                    const employeeType = $selected.data('employee-type') || 'permanent';

                    updateEmployeeUI(employeeType, dailySalary, basicSalary);
                });

                // On working days change
                $('#working_days').on('input', function () {
                    const days = parseInt($(this).val()) || 0;
                    const salary = currentDailySalary * days;
                    $('#basic_salary').val(formatCurrency(salary));
                    calculateTotal();
                });

                // Recalculate on input change
                $(document).on('input', '.calc-input', function () {
                    calculateTotal();
                });

                // Recalculate on potongan amount change
                $(document).on('input', '.potongan-amount', function () {
                    calculateTotal();
                });

                // Add potongan row
                $('#btn_add_potongan').on('click', function () {
                    const row = `
                        <div class="potongan-row flex items-start gap-2">
                            <div class="relative flex-shrink-0 w-40">
                                <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                                <input type="text" name="potongan_items[]"
                                    class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input potongan-amount"
                                    placeholder="0">
                            </div>
                            <input type="text" name="potongan_notes_items[]"
                                class="flex-1 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Catatan (Contoh: Kasbon)">
                            <button type="button"
                                class="btn-remove-potongan mt-1 p-2 text-slate-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition"
                                title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>`;
                    $('#potongan_list').append(row);
                    updateRemoveButtons();
                });

                // Remove potongan row
                $(document).on('click', '.btn-remove-potongan', function () {
                    $(this).closest('.potongan-row').remove();
                    updateRemoveButtons();
                    calculateTotal();
                });

                // Show/hide remove buttons based on row count
                const updateRemoveButtons = () => {
                    const rows = $('.potongan-row');
                    if (rows.length <= 1) {
                        rows.find('.btn-remove-potongan').addClass('invisible');
                    } else {
                        rows.find('.btn-remove-potongan').removeClass('invisible');
                    }
                };

                // Initialize for edit mode
                @if(isset($payroll))
                    currentEmployeeType = '{{ $payroll->employee_type }}';
                    currentDailySalary = {{ $payroll->daily_salary }};
                    calculateTotal();
                @else
                    // Trigger change for selected employee on page load
                    if ($('#employee_select').val()) {
                        $('#employee_select').trigger('change');
                    }
                @endif

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