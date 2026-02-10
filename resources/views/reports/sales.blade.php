@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('subtitle', 'Analisis penjualan berdasarkan rentang waktu')

@section('content')
    <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('reports.sales') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
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

    <div class="mt-6 grid gap-3 md:gap-6 sm:grid-cols-2">
        <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
            <p class="text-xs uppercase text-slate-500">Total Penjualan</p>
            <p class="mt-2 text-3xl font-semibold text-indigo-600">Rp {{ number_format($report['summary']['sales'], 0, ',', '.') }}</p>
            <p class="mt-3 text-xs text-slate-400">Periode {{ $report['range']['start'] }} - {{ $report['range']['end'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
            <p class="text-xs uppercase text-slate-500">Total Transaksi</p>
            <p class="mt-2 text-3xl font-semibold text-slate-700">{{ $report['summary']['transactions'] }}</p>
            <p class="mt-3 text-xs text-slate-400">Rata-rata Rp {{ $report['summary']['transactions'] ? number_format($report['summary']['sales'] / max(1, $report['summary']['transactions']), 0, ',', '.') : 0 }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Grafik Penjualan</h2>
                <p class="text-sm text-slate-500">Trend penjualan per periode</p>
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
        <div class="mt-6 relative" style="min-height: 250px;">
            <canvas id="salesReportChart"></canvas>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">Detail Laporan</h2>
            <a href="{{ route('reports.sales.export', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Excel
            </a>
        </div>
        
        <!-- Desktop Detail Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="mt-4 w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3 text-right">Penjualan</th>
                        <th class="px-4 py-3 text-right">Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['data'] as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $row['label'] }}</td>
                            <td class="px-4 py-3 text-right text-slate-600">Rp {{ number_format($row['sales'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-slate-600">{{ $row['transactions'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-slate-500">Tidak ada data untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Detail Cards -->
        <div class="mt-4 md:hidden space-y-3">
            @forelse ($report['data'] as $row)
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                        <span class="font-bold text-slate-800">{{ $row['label'] }}</span>
                        <span class="text-xs text-indigo-600 font-semibold">{{ $row['transactions'] }} Tx</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-slate-400">Penjualan</p>
                        <p class="text-sm font-semibold text-slate-700">Rp {{ number_format($row['sales'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-slate-500 py-4">Tidak ada data.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Performa Kasir</h2>
                <p class="text-sm text-slate-500">Total penjualan per kasir</p>
            </div>
        </div>

        <!-- Desktop Cashier Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="mt-4 w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kasir</th>
                        <th class="px-4 py-3 text-right">Transaksi</th>
                        <th class="px-4 py-3 text-right">Penjualan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['cashiers'] as $cashier)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $cashier['name'] }}</td>
                            <td class="px-4 py-3 text-right text-slate-600">{{ $cashier['transactions'] }}</td>
                            <td class="px-4 py-3 text-right text-slate-600">Rp {{ number_format($cashier['sales'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-slate-500">Belum ada transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cashier Cards -->
        <div class="mt-4 md:hidden space-y-3">
            @forelse ($report['cashiers'] as $cashier)
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                        <span class="font-bold text-slate-800">{{ $cashier['name'] }}</span>
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600">
                            {{ $cashier['transactions'] }} Trx
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-slate-400">Penjualan</p>
                        <p class="text-sm font-semibold text-slate-700">Rp {{ number_format($cashier['sales'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-slate-500 py-4">Belum ada data kasir.</p>
            @endforelse
        </div>
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
                            type: 'bar',
                            label: 'Penjualan',
                            data: salesReportData.map(item => item.sales),
                            backgroundColor: 'rgba(79,70,229,0.7)',
                            borderColor: '#4F46E5',
                            borderWidth: 1,
                            borderRadius: 8,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            ticks: {
                                callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
                            },
                            grid: { color: 'rgba(226,232,240,0.6)' }
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
