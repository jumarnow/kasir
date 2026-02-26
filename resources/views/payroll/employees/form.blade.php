@extends('layouts.app')

@section('title', isset($employee) ? 'Edit Pegawai' : 'Tambah Pegawai')

@section('content')
    <div class="w-full">
        <div class="mb-6">
            <a href="{{ route('employees.index') }}"
                class="text-slate-500 hover:text-indigo-600 text-sm flex items-center gap-1 mb-2">
                ◀ Kembali ke Daftar
            </a>
            <h1 class="text-2xl font-bold text-slate-900">
                {{ isset($employee) ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru' }}
            </h1>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <form action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}"
                method="POST">
                @csrf
                @if(isset($employee))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Info Dasar -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-slate-800 border-b pb-2">Informasi Dasar</h3>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">No ID Pegawai <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="employee_id"
                                value="{{ old('employee_id', isset($employee) ? $employee->employee_id : '') }}"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                required placeholder="Contoh: P001">
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name"
                                value="{{ old('name', isset($employee) ? $employee->name : '') }}"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="position"
                                value="{{ old('position', isset($employee) ? $employee->position : '') }}"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <!-- Tipe Karyawan -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Karyawan <span
                                    class="text-red-500">*</span></label>
                            <select name="employee_type" id="employee_type"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                                @foreach(\App\Models\Employee::EMPLOYEE_TYPES as $value => $label)
                                    <option value="{{ $value }}" {{ old('employee_type', $employee->employee_type ?? 'permanent') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1" id="type_hint">Karyawan tetap dibayar bulanan</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Bergabung <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="join_date"
                                value="{{ old('join_date', isset($employee) ? $employee->join_date->format('Y-m-d') : '') }}"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="flex items-center gap-2 cursor-pointer mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('is_active', isset($employee) ? $employee->is_active : 1) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-700">Pegawai Aktif</span>
                            </label>
                        </div>
                    </div>

                    <!-- Info Gaji & Bank -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-slate-800 border-b pb-2">Gaji & Rekening</h3>

                        <!-- Gaji Bulanan (untuk permanent) -->
                        <div id="monthly_salary_container">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gaji Bulanan (Rp) <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                <input type="text" name="basic_salary" id="basic_salary_input"
                                    value="{{ old('basic_salary', isset($employee) ? number_format($employee->basic_salary, 0, ',', '.') : '') }}"
                                    class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input"
                                    placeholder="0">
                            </div>
                            @error('basic_salary')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gaji Harian (untuk intern/internship) -->
                        <div id="daily_salary_container" class="hidden">
                            <label id="daily_salary_label" class="block text-sm font-medium text-slate-700 mb-1">Gaji Harian
                                (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-slate-500">Rp</span>
                                <input type="text" name="daily_salary" id="daily_salary_input"
                                    value="{{ old('daily_salary', isset($employee) ? number_format($employee->daily_salary, 0, ',', '.') : '') }}"
                                    class="w-full pl-10 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 currency-input"
                                    placeholder="0">
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Gaji akan dikalikan dengan jumlah hari kerja</p>
                            @error('daily_salary')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name"
                                value="{{ old('bank_name', isset($employee) ? $employee->bank_name : '') }}"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Contoh: BCA">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">No. Rekening</label>
                            <input type="text" name="bank_account"
                                value="{{ old('bank_account', isset($employee) ? $employee->bank_account : '') }}"
                                class="w-full rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="1234567890">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('employees.index') }}"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-lg border border-transparent hover:border-slate-200 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                        {{ isset($employee) ? 'Update Data' : 'Simpan Data' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const typeHints = {
                    'permanent': 'Karyawan tetap dibayar bulanan',
                    'intern': 'Karyawan magang dibayar harian',
                    'internship': 'Internship (PKL) dibayar harian'
                };

                const toggleSalaryFields = function () {
                    const type = $('#employee_type').val();
                    const isDaily = type === 'intern' || type === 'internship';

                    $('#monthly_salary_container').toggleClass('hidden', isDaily);
                    $('#daily_salary_container').toggleClass('hidden', !isDaily);
                    $('#type_hint').text(typeHints[type] || '');

                    // Update label berdasarkan tipe
                    if (type === 'internship' || type === 'intern') {
                        $('#daily_salary_label').html('Tunjangan Magang (Rp) <span class="text-red-500">*</span>');
                    } else {
                        $('#daily_salary_label').html('Gaji Harian (Rp) <span class="text-red-500">*</span>');
                    }
                };

                $('#employee_type').on('change', toggleSalaryFields);
                toggleSalaryFields(); // Initialize

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