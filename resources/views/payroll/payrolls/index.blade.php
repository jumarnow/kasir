@extends('layouts.app')

@section('title', 'Daftar Slip Gaji')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex gap-2 flex-wrap">
            <form action="{{ route('payrolls.index') }}" method="GET" class="flex gap-2 flex-wrap">
                <select name="month"
                    class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select name="year"
                    class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Tahun</option>
                    @foreach(range(date('Y'), 2024) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <select name="employee_id"
                    class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Pegawai</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
                    Filter
                </button>
            </form>
        </div>

        @can('create_payrolls')
            <a href="{{ route('payrolls.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                <span>➕</span> Buat Slip Gaji
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3">Pegawai</th>
                        <th class="px-4 py-3">Gaji Pokok</th>
                        <th class="px-4 py-3">Tunjangan</th>
                        <th class="px-4 py-3">Potongan</th>
                        <th class="px-4 py-3 font-bold">Gaji Bersih</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($payrolls as $payroll)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">
                                {{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }}
                                {{ $payroll->period_year }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $payroll->employee->name }}</div>
                                <div class="text-xs text-slate-500">{{ $payroll->employee->employee_id }}</div>
                            </td>
                            <td class="px-4 py-3">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-green-600">
                                + Rp
                                {{ number_format($payroll->tunjangan_makan + $payroll->tunjangan_transport + $payroll->tunjangan_jabatan + $payroll->bonus_kehadiran + $payroll->bonus_target, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-red-600">
                                - Rp {{ number_format($payroll->potongan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($payroll->status == 'paid')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Sudah Dibayar
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('payrolls.show', $payroll) }}"
                                        class="text-slate-600 hover:text-indigo-600" title="Detail">
                                        👁️
                                    </a>

                                    @if($payroll->status == 'draft')
                                        @can('edit_payrolls')
                                            <a href="{{ route('payrolls.edit', $payroll) }}"
                                                class="text-slate-600 hover:text-indigo-600" title="Edit">
                                                ✏️
                                            </a>
                                        @endcan

                                        @can('delete_payrolls')
                                            <form action="{{ route('payrolls.destroy', $payroll) }}" method="POST"
                                                class="inline delete-form" data-message="Hapus slip gaji ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-400 hover:text-red-600" title="Hapus">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <a href="{{ route('payrolls.print', $payroll) }}" target="_blank"
                                            class="text-slate-600 hover:text-indigo-600" title="Cetak PDF">
                                            🖨️
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data slip gaji.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payrolls->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $payrolls->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection