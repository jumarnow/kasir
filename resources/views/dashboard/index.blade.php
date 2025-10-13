@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan penjualan dan performa toko')

@section('content')
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
                <p class="text-xs text-slate-500">Penjualan Hari Ini</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-600">
                    Rp {{ number_format($data['today']['sales'], 0, ',', '.') }}
                </p>
                <p class="mt-2 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600">
                    {{ $data['today']['transactions'] }} transaksi
                </p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
                <p class="text-xs text-slate-500">Profit Hari Ini</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">
                    Rp {{ number_format($data['today']['profit'], 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-emerald-600">Profit bersih setelah diskon</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
                <p class="text-xs text-slate-500">Rata-rata Transaksi</p>
                <p class="mt-2 text-2xl font-semibold text-slate-700">
                    Rp {{ $data['today']['transactions'] ? number_format($data['today']['sales'] / max(1, $data['today']['transactions']), 0, ',', '.') : 0 }}
                </p>
                <p class="mt-2 text-xs text-slate-500">Nominal per transaksi</p>
            </div>
        </div>
        <div class="lg:col-span-1 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 p-5 text-white shadow-lg">
            <p class="text-xs opacity-80">Quick Insight</p>
            <h2 class="mt-3 text-xl font-semibold">Penjualan stabil</h2>
            <p class="mt-2 text-xs opacity-80">
                Pantau performa kasir, stok menipis, dan produk terlaris melalui dashboard interaktif.
            </p>
            <ul class="mt-4 space-y-2 text-xs">
                <li class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                    Penjualan 7 hari diringkas
                </li>
                <li class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                    Alert stok produk kritis
                </li>
            </ul>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Grafik Penjualan 7 Hari</h2>
                    <p class="text-xs text-slate-500">Tren penjualan harian minggu ini</p>
                </div>
            </div>
            <div class="mt-4">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <h2 class="text-base font-semibold text-slate-800">Produk Terlaris</h2>
            <p class="text-xs text-slate-500">Periode 30 hari terakhir</p>
            <ul class="mt-3 space-y-3">
                @forelse ($data['top_products'] as $product)
                    <li class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2.5">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $product['name'] }}</p>
                            <p class="text-xs text-slate-400">SKU: {{ $product['sku'] }}</p>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600">
                            {{ $product['quantity'] }} terjual
                        </span>
                    </li>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data transaksi.</p>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <h2 class="text-base font-semibold text-slate-800">Stok Produk</h2>
            <p class="text-xs text-slate-500">Pantau stok kritis dan stok aman</p>
            <div class="mt-3 space-y-3">
                @forelse ($data['stock_alerts'] as $product)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2.5">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $product['name'] }}</p>
                            <p class="text-xs text-slate-400">SKU: {{ $product['sku'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ $product['is_low'] ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $product['stock'] }} stok
                            </p>
                            <p class="text-xs text-slate-400">
                                Alert: {{ $product['stock_alert'] ?: '-' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data stok.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <h2 class="text-base font-semibold text-slate-800">Catatan</h2>
            <p class="text-xs text-slate-500">Tips operasional kasir hari ini</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li class="flex items-start gap-2">
                    <span class="mt-0.5 text-indigo-500">•</span>
                    Gunakan fitur pemindaian barcode untuk mempercepat transaksi.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-0.5 text-indigo-500">•</span>
                    Pastikan stok produk ter-update setelah setiap transaksi.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-0.5 text-indigo-500">•</span>
                    Manfaatkan laporan penjualan untuk melihat performa kasir.
                </li>
            </ul>
            <a href="{{ route('reports.sales') }}" class="mt-4 inline-flex items-center gap-2 rounded-full bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                Lihat laporan penjualan
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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
                                callback: function(value) {
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
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
