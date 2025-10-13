@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('subtitle', 'Analisis penjualan berdasarkan rentang waktu')

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('reports.sales') }}" class="grid gap-4 lg:grid-cols-5">
            <div>
                <label class="text-xs uppercase text-slate-500">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? $report['range']['start'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="text-xs uppercase text-slate-500">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? $report['range']['end'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="text-xs uppercase text-slate-500">Group By</label>
                <select name="group_by" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="day" @selected(($filters['group_by'] ?? $report['range']['group_by']) === 'day')>Harian</option>
                    <option value="week" @selected(($filters['group_by'] ?? $report['range']['group_by']) === 'week')>Mingguan</option>
                    <option value="month" @selected(($filters['group_by'] ?? $report['range']['group_by']) === 'month')>Bulanan</option>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-slate-500">Kasir</label>
                <select name="user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Semua Kasir</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Terapkan</button>
            </div>
        </form>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <p class="text-xs uppercase text-slate-500">Total Penjualan</p>
            <p class="mt-2 text-3xl font-semibold text-indigo-600">Rp {{ number_format($report['summary']['sales'], 0, ',', '.') }}</p>
            <p class="mt-3 text-xs text-slate-400">Periode {{ $report['range']['start'] }} - {{ $report['range']['end'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <p class="text-xs uppercase text-slate-500">Total Profit</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-500">Rp {{ number_format($report['summary']['profit'], 0, ',', '.') }}</p>
            <p class="mt-3 text-xs text-slate-400">Setelah diskon & biaya modal</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <p class="text-xs uppercase text-slate-500">Total Transaksi</p>
            <p class="mt-2 text-3xl font-semibold text-slate-700">{{ $report['summary']['transactions'] }}</p>
            <p class="mt-3 text-xs text-slate-400">Rata-rata Rp {{ $report['summary']['transactions'] ? number_format($report['summary']['sales'] / max(1, $report['summary']['transactions']), 0, ',', '.') : 0 }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Grafik Penjualan</h2>
                <p class="text-sm text-slate-500">Trend penjualan & profit</p>
            </div>
            @if (!empty($filters['user_id']))
                @php
                    $selectedUser = $users->firstWhere('id', (int) $filters['user_id']);
                @endphp
                @if ($selectedUser)
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                        Kasir: {{ $selectedUser->name }}
                    </span>
                @endif
            @endif
        </div>
        <div class="mt-6">
            <canvas id="salesReportChart" height="140"></canvas>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm border border-slate-200 overflow-hidden">
        <h2 class="text-lg font-semibold text-slate-800">Detail Laporan</h2>
        <table class="mt-4 w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3 text-right">Penjualan</th>
                    <th class="px-4 py-3 text-right">Profit</th>
                    <th class="px-4 py-3 text-right">Transaksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($report['data'] as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-700">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">Rp {{ number_format($row['sales'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $row['transactions'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Performa Kasir</h2>
                <p class="text-sm text-slate-500">Total penjualan & profit per kasir</p>
            </div>
        </div>
        <table class="mt-4 w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kasir</th>
                    <th class="px-4 py-3 text-right">Transaksi</th>
                    <th class="px-4 py-3 text-right">Penjualan</th>
                    <th class="px-4 py-3 text-right">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($report['cashiers'] as $cashier)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-700">{{ $cashier['name'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $cashier['transactions'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">Rp {{ number_format($cashier['sales'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($cashier['profit'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">Belum ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        const salesReportData = @json($report['data']);
        if (salesReportData.length) {
            const ctx = document.getElementById('salesReportChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: salesReportData.map(item => item.label),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Penjualan',
                            data: salesReportData.map(item => item.sales),
                            borderColor: '#4F46E5',
                            backgroundColor: 'rgba(99,102,241,0.12)',
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y',
                        },
                        {
                            type: 'bar',
                            label: 'Profit',
                            data: salesReportData.map(item => item.profit),
                            backgroundColor: 'rgba(16,185,129,0.5)',
                            borderRadius: 8,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            position: 'left',
                            ticks: {
                                callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
                            },
                            grid: { color: 'rgba(226,232,240,0.6)' }
                        },
                        y1: {
                            position: 'right',
                            ticks: {
                                callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
                            },
                            grid: { drawOnChartArea: false }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(context.parsed.y)}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
