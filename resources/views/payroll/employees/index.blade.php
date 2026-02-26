@extends('layouts.app')

@section('title', 'Daftar Pegawai')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-auto">
            <form action="{{ route('employees.index') }}" method="GET" class="flex gap-2 w-full md:w-auto flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pegawai..."
                    class="w-full md:w-64 rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <select name="employee_type" class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Tipe</option>
                    @foreach(\App\Models\Employee::EMPLOYEE_TYPES as $value => $label)
                        <option value="{{ $value }}" {{ request('employee_type') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 shrink-0">
                    Cari
                </button>
            </form>
        </div>

        @can('create_employees')
            <a href="{{ route('employees.create') }}"
                class="w-full md:w-auto justify-center inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Pegawai</span>
            </a>
        @endcan
    </div>

    <!-- Desktop View -->
    <div class="hidden md:block bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">No ID</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Jabatan</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Gaji</th>
                        <th class="px-4 py-3">Tgl Bergabung</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employees as $employee)
                        @php
                            $badgeColors = [
                                'permanent' => 'bg-blue-100 text-blue-800',
                                'intern' => 'bg-amber-100 text-amber-800',
                                'internship' => 'bg-green-100 text-green-800',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $employee->employee_id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $employee->name }}</div>
                                <div class="text-xs text-slate-500">{{ $employee->bank_name }} - {{ $employee->bank_account }}
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $employee->position }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$employee->employee_type] ?? 'bg-slate-100 text-slate-800' }}">
                                    {{ $employee->employee_type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-slate-900">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</div>
                                <div class="text-xs text-slate-500">per bulan</div>
                            </td>
                            <td class="px-4 py-3">{{ $employee->join_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                @if($employee->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('edit_employees')
                                        <a href="{{ route('employees.edit', $employee) }}"
                                            class="text-slate-600 hover:text-indigo-600" title="Edit">
                                            ✏️
                                        </a>
                                    @endcan

                                    @can('delete_employees')
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                                            class="inline delete-form" data-message="Hapus data pegawai {{ $employee->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-red-600" title="Hapus">
                                                🗑️
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View -->
    <div class="md:hidden space-y-4">
        @forelse($employees as $employee)
            @php
                $badgeColors = [
                    'permanent' => 'bg-blue-100 text-blue-800',
                    'intern' => 'bg-amber-100 text-amber-800',
                    'internship' => 'bg-green-100 text-green-800',
                ];
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $employee->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $employee->position }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1 {{ $badgeColors[$employee->employee_type] ?? 'bg-slate-100 text-slate-800' }}">
                            {{ $employee->employee_type_label }}
                        </span>
                    </div>
                    <div class="text-right">
                        @if($employee->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Nonaktif
                            </span>
                        @endif
                        <p class="text-xs text-slate-400 mt-1">ID: {{ $employee->employee_id }}</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Gaji:</span>
                        <span class="font-medium">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }} / bulan</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Bank:</span>
                        <span class="font-medium text-right">{{ $employee->bank_name }} - {{ $employee->bank_account }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Bergabung:</span>
                        <span>{{ $employee->join_date->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="mt-4 flex gap-2 pt-3 border-t border-slate-100">
                    @can('edit_employees')
                        <a href="{{ route('employees.edit', $employee) }}"
                            class="flex-1 text-center bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-indigo-50 border border-slate-200 transition">
                            ✏️ Edit
                        </a>
                    @endcan

                    @can('delete_employees')
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                            class="flex-1 delete-form" data-message="Hapus data pegawai {{ $employee->name }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-50 text-red-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-100 border border-red-200 transition">
                                🗑️ Hapus
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="text-center p-8 text-slate-500 bg-white rounded-xl border border-slate-200">
                Belum ada data pegawai.
            </div>
        @endforelse
    </div>

    @if($employees->hasPages())
        <div class="mt-4">
            {{ $employees->withQueryString()->links() }}
        </div>
    @endif
@endsection