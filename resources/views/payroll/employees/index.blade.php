@extends('layouts.app')

@section('title', 'Daftar Pegawai')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex gap-2">
            <form action="{{ route('employees.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pegawai..."
                    class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
                    Cari
                </button>
            </form>
        </div>

        @can('create_employees')
            <a href="{{ route('employees.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                <span>➕</span> Tambah Pegawai
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">No ID</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Jabatan</th>
                        <th class="px-4 py-3">Gaji Pokok</th>
                        <th class="px-4 py-3">Tgl Bergabung</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $employee->employee_id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $employee->name }}</div>
                                <div class="text-xs text-slate-500">{{ $employee->bank_name }} - {{ $employee->bank_account }}
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $employee->position }}</td>
                            <td class="px-4 py-3">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</td>
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
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $employees->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection