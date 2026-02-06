@extends('layouts.app')

@section('title', 'Detail Slip Gaji')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('payrolls.index') }}"
                    class="text-slate-500 hover:text-indigo-600 text-sm flex items-center gap-1 mb-2">
                    ◀ Kembali ke Daftar
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Detail Slip Gaji</h1>
            </div>
            <div class="flex gap-2">
                @if($payroll->status == 'draft')
                    @can('edit_payrolls')
                        <form action="{{ route('payrolls.mark-paid', $payroll) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                                ✓ Tandai Dibayar
                            </button>
                        </form>
                        <a href="{{ route('payrolls.edit', $payroll) }}"
                            class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                            Edit
                        </a>
                    @endcan
                @else
                    <button disabled
                        class="bg-slate-100 text-slate-500 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                        Sudah Dibayar ({{ $payroll->paid_at->format('d/m/Y') }})
                    </button>
                @endif

                <a href="{{ route('payrolls.print', $payroll) }}" target="_blank"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                    🖨️ Cetak PDF
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">
            <!-- Header Slip -->
            <div class="border-b border-slate-200 bg-slate-50 p-6 flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-500 tracking-wider uppercase mb-1">SLIP GAJI KARYAWAN</p>
                    <h2 class="text-xl font-bold text-indigo-700">Periode:
                        {{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }}
                        {{ $payroll->period_year }}</h2>
                </div>
                <div class="text-right">
                    <p class="font-bold text-slate-900">{{ $payroll->employee->name }}</p>
                    <p class="text-sm text-slate-500">{{ $payroll->employee->employee_id }}</p>
                    <p class="text-sm text-slate-500">{{ $payroll->employee->position }}</p>
                    @if($payroll->employee->join_date)
                        <p class="text-xs text-slate-400 mt-1">Bergabung: {{ $payroll->employee->join_date->format('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 gap-x-12 gap-y-6">
                    <!-- Pendapatan -->
                    <div>
                        <h3 class="font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4">PENDAPATAN</h3>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Gaji Pokok</span>
                                <span class="font-medium text-slate-900">Rp
                                    {{ number_format($payroll->basic_salary, 0, ',', '.') }}</span>
                            </div>

                            @if($payroll->tunjangan_makan > 0)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Tunjangan Makan</span>
                                    <span class="font-medium text-slate-900">Rp
                                        {{ number_format($payroll->tunjangan_makan, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if($payroll->tunjangan_transport > 0)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Tunjangan Transport</span>
                                    <span class="font-medium text-slate-900">Rp
                                        {{ number_format($payroll->tunjangan_transport, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if($payroll->tunjangan_jabatan > 0)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Tunjangan Jabatan</span>
                                    <span class="font-medium text-slate-900">Rp
                                        {{ number_format($payroll->tunjangan_jabatan, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if($payroll->bonus_kehadiran > 0)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Bonus Kehadiran</span>
                                    <span class="font-medium text-slate-900">Rp
                                        {{ number_format($payroll->bonus_kehadiran, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if($payroll->bonus_target > 0)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Bonus Target</span>
                                    <span class="font-medium text-slate-900">Rp
                                        {{ number_format($payroll->bonus_target, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="border-t border-slate-100 pt-2 mt-2 flex justify-between font-bold text-slate-800">
                                <span>Total Pendapatan</span>
                                <span>Rp
                                    {{ number_format($payroll->basic_salary + $payroll->tunjangan_makan + $payroll->tunjangan_transport + $payroll->tunjangan_jabatan + $payroll->bonus_kehadiran + $payroll->bonus_target, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Potongan -->
                    <div>
                        <h3 class="font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4">POTONGAN</h3>

                        <div class="space-y-3">
                            @if($payroll->potongan > 0)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Potongan Lainnya</span>
                                    <span class="font-medium text-red-600">- Rp
                                        {{ number_format($payroll->potongan, 0, ',', '.') }}</span>
                                </div>
                                @if($payroll->potongan_notes)
                                    <p class="text-xs text-slate-400 italic">{{ $payroll->potongan_notes }}</p>
                                @endif
                            @else
                                <p class="text-sm text-slate-400 italic">Tidak ada potongan</p>
                            @endif

                            <div class="border-t border-slate-100 pt-2 mt-2 flex justify-between font-bold text-slate-800">
                                <span>Total Potongan</span>
                                <span>Rp {{ number_format($payroll->potongan, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grand Total -->
                <div class="mt-8 bg-indigo-50 rounded-lg p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-indigo-900 uppercase tracking-wider">GAJI BERSIH / TAKE HOME PAY
                        </p>
                        <p class="text-xs text-indigo-700 md:w-64">
                            Diterimakan kepada {{ $payroll->employee->name }}
                            @if($payroll->status == 'paid') pada tanggal
                            {{ $payroll->paid_at ? $payroll->paid_at->format('d/m/Y') : '-' }} @endif
                        </p>
                    </div>
                    <div class="text-3xl font-bold text-indigo-700">
                        Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection