@extends('layouts.app')

@section('title', 'Dashboard Performa')

@section('head')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Dashboard Performa</h2>
            <p class="text-sm text-slate-500">Analisis penjualan, pengeluaran, dan performa karyawan</p>
        </div>
        
        <form action="{{ route('reports.performance') }}" method="GET" class="flex gap-2">
            <select name="period" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white pl-4 pr-8 py-2 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Harian (30 Hari Terakhir)</option>
                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Bulanan (12 Bulan Terakhir)</option>
                <option value="quadmester" {{ $period == 'quadmester' ? 'selected' : '' }}>Per 4 Bulan (Kuartalan)</option>
                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Tahunan (5 Tahun Terakhir)</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales vs Expense Chart -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Grafik Penjualan vs Pengeluaran</h3>
            <div class="relative h-[300px] w-full">
                <canvas id="salesExpenseChart"></canvas>
            </div>
        </div>

        <!-- Employee Performance Chart -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Performa Karyawan (Berdasarkan Filter)</h3>
            <div class="relative h-[300px] w-full">
                <canvas id="employeePerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Employee Trans Count Chart -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-800 mb-4">Jumlah Transaksi Karyawan (Berdasarkan Filter)</h3>
        <div class="relative h-[300px] w-full">
            <canvas id="employeeTransChart"></canvas>
        </div>
    </div>

    <!-- Employee SPK Performance Chart -->
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-800 mb-4">Performa Pegawai (SPK - Design & Produksi)</h3>
        <div class="relative h-[300px] w-full">
            <canvas id="employeeSpkChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for Sales vs Expense
        const labels = {!! json_encode($labels) !!};
        const salesData = {!! json_encode($salesData) !!};
        const expenseData = {!! json_encode($expenseData) !!};

        const ctx1 = document.getElementById('salesExpenseChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan',
                        data: salesData,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)', // Indigo-500
                        borderColor: 'rgb(79, 70, 229)', // Indigo-600
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Pengeluaran',
                        data: expenseData,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)', // Red-500
                        borderColor: 'rgb(220, 38, 38)', // Red-600
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                    notation: "compact",
                                    compactDisplay: "short"
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Data for Employee Performance (Sales Amount)
        const empLabels = {!! json_encode($empLabels) !!};
        const empSalesData = {!! json_encode($empSalesData) !!};
        
        const ctx2 = document.getElementById('employeePerformanceChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: empLabels,
                datasets: [{
                    data: empSalesData,
                    backgroundColor: [
                        '#6366f1', // Indigo
                        '#14b8a6', // Teal
                        '#f59e0b', // Amber
                        '#ec4899', // Pink
                        '#8b5cf6', // Violet
                        '#0ea5e9', // Sky
                        '#10b981', // Emerald
                    ],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Data for Employee Performance (Transaction Count)
        const empTransData = {!! json_encode($empTransData) !!};

        const ctx3 = document.getElementById('employeeTransChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: empLabels,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: empTransData,
                    backgroundColor: 'rgba(20, 184, 166, 0.8)', // Teal-500
                    borderColor: 'rgb(13, 148, 136)', // Teal-600
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Data for Employee SPK Performance
        const spkLabels = {!! json_encode($spkLabels ?? []) !!};
        const spkDesignData = {!! json_encode($spkDesignData ?? []) !!};
        const spkProduksiData = {!! json_encode($spkProduksiData ?? []) !!};

        if (document.getElementById('employeeSpkChart')) {
            const ctx4 = document.getElementById('employeeSpkChart').getContext('2d');
            new Chart(ctx4, {
                type: 'bar',
                data: {
                    labels: spkLabels,
                    datasets: [
                        {
                            label: 'Design',
                            data: spkDesignData,
                            backgroundColor: 'rgba(245, 158, 11, 0.8)', // Amber-500
                            borderColor: 'rgb(217, 119, 6)', // Amber-600
                            borderWidth: 1,
                        },
                        {
                            label: 'Produksi',
                            data: spkProduksiData,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)', // Blue-500
                            borderColor: 'rgb(37, 99, 235)', // Blue-600
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
