@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan penjualan dan performa toko')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Dashboard</h2>
            <p class="text-sm text-slate-500">Ringkasan penjualan dan performa toko hari ini</p>
        </div>
        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            @if ($permissions['transactions'] ?? false)
                <a href="{{ route('transactions.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all">
                    + Transaksi Baru
                </a>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-3 md:gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 grid gap-3 md:gap-4 grid-cols-1 sm:grid-cols-3">
            <div
                class="rounded-2xl bg-white p-4 md:p-5 shadow-sm border border-slate-200 hover:border-indigo-100 transition-all">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Penjualan Hari Ini</p>
                <p class="mt-3 text-2xl font-bold text-indigo-600">
                    {{ $data['today']['transactions'] }} transaksi
                </p>
                @php
                    $change = $data['today']['percentage_change'];
                    $isIncrease = $change > 0;
                    $isDecrease = $change < 0;
                    $isNoChange = $change == 0;
                @endphp
                <div class="mt-4 flex items-center gap-2">
                    @if ($isIncrease)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            +{{ number_format(abs($change), 1) }}%
                        </span>
                    @elseif ($isDecrease)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-red-600">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ number_format(abs($change), 1) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-500">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 10a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            0%
                        </span>
                    @endif
                    <span class="text-[10px] sm:text-xs text-slate-500 font-medium">
                        vs kemarin ({{ $data['today']['yesterday_transactions'] }} transaksi)
                    </span>
                </div>
            </div>

            @if ($canViewProfit)
                <div
                    class="rounded-2xl bg-white p-4 md:p-5 shadow-sm border border-slate-200 hover:border-{{ $data['today']['profit'] >= 0 ? 'emerald' : 'red' }}-100 transition-all">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Profit Hari Ini</p>
                    <p class="mt-3 text-2xl font-bold text-{{ $data['today']['profit'] >= 0 ? 'emerald' : 'red' }}-600">
                        Rp {{ number_format($data['today']['profit'], 0, ',', '.') }}
                    </p>
                    <p
                        class="mt-4 text-[10px] sm:text-xs text-{{ $data['today']['profit'] >= 0 ? 'emerald' : 'red' }}-600 font-medium">
                        {{ $data['today']['profit'] >= 0 ? 'Profit bersih (untung)' : 'Rugi hari ini' }}
                    </p>
                </div>
            @endif

            <div
                class="rounded-2xl bg-white p-4 md:p-5 shadow-sm border border-slate-200 hover:border-slate-300 transition-all">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Rata-rata Transaksi</p>
                <p class="mt-3 text-2xl font-bold text-slate-800">
                    Rp {{ number_format($data['today']['average_transaction'], 0, ',', '.') }}
                </p>
                <p class="mt-4 text-[10px] sm:text-xs text-slate-500 font-medium">Nominal per transaksi</p>
            </div>
        </div>


        <div
            class="lg:col-span-1 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-lg shadow-indigo-100 relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-bold uppercase tracking-wider opacity-70">Quick Insight</p>
                <h2 class="mt-4 text-xl font-bold">Performa Bisnis</h2>
                <p class="mt-2 text-sm opacity-85 leading-relaxed">
                    Pantau grafik penjualan, stok produk kritis, dan performa SPK harian secara real-time.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <span
                        class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-tight backdrop-blur-md">
                        7 Hari Terakhir
                    </span>
                    <span
                        class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-tight backdrop-blur-md">
                        Stok Alert
                    </span>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 text-white/10 transform -rotate-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z" />
                </svg>
            </div>
        </div>

    </div>


    @if ($canViewProfit || $canViewSpkChart)

        @php
            $gridCols = ($canViewProfit && $canViewSpkChart) ? 'lg:grid-cols-3' : 'lg:grid-cols-1';
            $salesSpan = ($canViewProfit && $canViewSpkChart) ? 'lg:col-span-2' : 'lg:col-span-1';
            $spkSpan = ($canViewProfit && $canViewSpkChart) ? 'lg:col-span-1' : 'lg:col-span-1';
        @endphp
        <div class="mt-6 grid gap-3 md:gap-6 {{ $gridCols }}">
            @if ($canViewProfit)
            <div class="{{ $salesSpan }} rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Grafik Penjualan</h2>
                        <p class="text-xs text-slate-500">Tren penjualan 7 hari terakhir</p>
                    </div>
                    <div class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></div>
                </div>
                <div class="relative">
                    <canvas id="salesChart" height="140"></canvas>
                </div>
            </div>
            @endif

            @if ($canViewSpkChart)
            <div class="{{ $spkSpan }} rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-slate-800">Grafik SPK (Hari Ini)</h2>
                    <p class="text-xs text-slate-500">Performa Design & Produksi</p>
                </div>
                <div class="relative h-[240px] w-full">
                    <canvas id="spkChart"></canvas>
                </div>
            </div>
            @endif
        </div>

    @endif

    @if ($canViewSpkChart)
        <div class="mt-6">
            <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-slate-800">Grafik SPK ( 3 hari Terakhir )</h2>
                    <p class="text-xs text-slate-500">Total performa karyawan</p>
                </div>
                <div class="relative h-[300px] w-full">
                    <canvas id="spk3DaysChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-6 grid gap-3 md:gap-6 lg:grid-cols-3" id="stock-alerts-section">
        <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Stok Produk</h2>
                    <p class="text-xs text-slate-500">Pantau produk dengan stok kritis</p>
                </div>
                <a href="{{ route('products.index') }}"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-500">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($data['stock_alerts'] as $product)
                    <div
                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:bg-white hover:border-slate-200 hover:shadow-sm">
                        <div class="flex-1 min-w-0 mr-4">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $product['name'] }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">SKU:
                                {{ $product['sku'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $product['is_low'] ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $product['stock'] }} unit
                            </p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">
                                Alert: {{ $product['stock_alert'] ?: '-' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500 italic">Semua stok dalam kondisi aman.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Stok Bahan Baku</h2>
                    <p class="text-xs text-slate-500">Pantau bahan baku dengan stok kritis</p>
                </div>
                <a href="{{ route('raw-materials.index') }}"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-500">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($data['raw_material_alerts'] as $material)
                    <div
                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:bg-white hover:border-slate-200 hover:shadow-sm">
                        <div class="flex-1 min-w-0 mr-4">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $material['name'] }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">SKU:
                                {{ $material['sku'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $material['is_low'] ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $material['stock'] }} {{ $material['unit'] }}
                            </p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">
                                Min: {{ $material['min_stock'] ?: '-' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500 italic">Semua stok bahan baku aman.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 md:p-6 shadow-sm border border-slate-200 relative overflow-hidden">
            <div class="mb-6 relative z-10">
                <h2 class="text-base font-bold text-slate-800">Aksi & Laporan</h2>
                <p class="text-xs text-slate-500">Akses cepat ke menu utama</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 relative z-10">

                @if ($canViewProfit)
                    <a href="{{ route('reports.sales') }}"
                        class="flex flex-col p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-100 hover:shadow-md transition-all group">
                        <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">💰</span>
                        <span class="font-bold text-sm text-slate-800">Laporan Penjualan</span>
                        <span class="text-xs text-slate-500 mt-1">Analisis histori transaksi</span>
                    </a>
                    <a href="{{ route('reports.profit') }}"
                        class="flex flex-col p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-100 hover:shadow-md transition-all group">
                        <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">📈</span>
                        <span class="font-bold text-sm text-slate-800">Laporan Profit</span>
                        <span class="text-xs text-slate-500 mt-1">Pantau laba bersih harian</span>
                    </a>
                @endif

                <a href="{{ route('products.index') }}"
                    class="flex flex-col p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-100 hover:shadow-md transition-all group">
                    <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">📦</span>
                    <span class="font-bold text-sm text-slate-800">Kelola Produk</span>
                    <span class="text-xs text-slate-500 mt-1">Update harga & stok</span>
                </a>

                <a href="{{ route('raw-materials.index') }}"
                    class="flex flex-col p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-100 hover:shadow-md transition-all group">
                    <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">🏷️</span>
                    <span class="font-bold text-sm text-slate-800">Bahan Baku</span>
                    <span class="text-xs text-slate-500 mt-1">Stok: {{ number_format($data['raw_materials']['total_stock'] ?? 0) }} item</span>
                </a>

                <a href="{{ route('transactions.index') }}"
                    class="flex flex-col p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-100 hover:shadow-md transition-all group">
                    <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">🧾</span>
                    <span class="font-bold text-sm text-slate-800">Riwayat Transaksi</span>
                    <span class="text-xs text-slate-500 mt-1">Cetak ulang invoice</span>
                </a>
            </div>

            <div class="absolute -right-4 top-0 text-slate-50/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-48 w-48" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" />
                </svg>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if ($canViewProfit)
        const salesData = @json($data['chart']);
        if (salesData.length) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: salesData.map(item => item.label),
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: salesData.map(item => item.total),
                        fill: true,
                        tension: 0.4,
                        borderColor: '#6366F1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        pointBackgroundColor: '#4F46E5',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            },
                            grid: {
                                color: 'rgba(226, 232, 240, 0.6)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        datalabels: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ' Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif

        @if ($canViewSpkChart && !empty($data['spk_chart']))
        const spkData = @json($data['spk_chart']);
        if (spkData.labels.length) {
            const spkCtx = document.getElementById('spkChart').getContext('2d');
            new Chart(spkCtx, {
                type: 'bar',
                data: {
                    labels: spkData.labels,
                    datasets: [
                        {
                            label: 'Design',
                            data: spkData.design,
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderColor: 'rgb(217, 119, 6)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Produksi',
                            data: spkData.produksi,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgb(37, 99, 235)',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            display: false
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
        @endif

        @if ($canViewSpkChart && !empty($data['spk_3_days_chart']))
        const spk3DaysData = @json($data['spk_3_days_chart']);
        if (spk3DaysData.labels.length) {
            const spk3DaysCtx = document.getElementById('spk3DaysChart').getContext('2d');
            new Chart(spk3DaysCtx, {
                type: 'bar',
                data: {
                    labels: spk3DaysData.labels,
                    datasets: spk3DaysData.datasets
                },
                plugins: [ChartDataLabels],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        datalabels: {
                            color: 'white',
                            textStrokeColor: 'rgba(0, 0, 0, 0.4)',
                            textStrokeWidth: 2,
                            textShadowColor: 'rgba(0, 0, 0, 0.4)',
                            textShadowBlur: 4,
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            align: 'center',
                            anchor: 'center',
                            formatter: function(value, context) {
                                if (value > 0) {
                                    let name = context.dataset.label.split(' (')[0];
                                    return [name, value];
                                }
                                return '';
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false }
                        },
                        y: { 
                            stacked: true,
                            beginAtZero: true, 
                            ticks: { stepSize: 1 } 
                        }
                    }
                }
            });
        }
        @endif
    </script>

    <script>
        $(function () {
            const lowStockCount = {{ $data['low_stock_count'] ?? 0 }};
            if (lowStockCount > 0) {
                Swal.fire({
                    title: 'Peringatan Stok!',
                    text: `Ada ${lowStockCount} produk yang stoknya sudah menipis (di bawah batas alert).`,
                    icon: 'warning',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Oke',
                    footer: '<a href="#stock-alerts-section" style="color: #4f46e5; font-size: 12px; font-weight: 600;">Lihat daftar di bawah</a>'
                });
            }
        });
    </script>
@endpush