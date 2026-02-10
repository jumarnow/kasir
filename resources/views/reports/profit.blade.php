@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Laporan Profit</h1>
            <a href="{{ route('reports.profit.export', ['month' => $month]) }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </a>
        </div>

        <!-- Month Selector -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('reports.profit') }}" class="flex items-center gap-4">
                <label class="text-sm font-medium text-slate-700">Pilih Bulan:</label>
                <input type="month" name="month" value="{{ $month }}" class="px-3 py-2 border border-slate-300 rounded-lg">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Tampilkan
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Total Sales -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium opacity-90">Penjualan Kotor</h3>
                    <i class="fas fa-shopping-cart text-2xl opacity-75"></i>
                </div>
                <div class="text-3xl font-bold">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                <p class="text-xs opacity-75 mt-2">{{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}</p>
            </div>

            <!-- Total Expenses -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium opacity-90">Total Modal Usaha</h3>
                    <i class="fas fa-wallet text-2xl opacity-75"></i>
                </div>
                <div class="text-3xl font-bold">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
                <p class="text-xs opacity-75 mt-2">Semua Pengeluaran</p>
            </div>

            <!-- Net Profit -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium opacity-90">Profit Bersih</h3>
                    <i class="fas fa-chart-line text-2xl opacity-75"></i>
                </div>
                <div class="text-3xl font-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                <p class="text-xs opacity-75 mt-2">
                    {{ $netProfit >= 0 ? 'Untung' : 'Rugi' }}
                    ({{ $totalSales > 0 ? number_format(($netProfit / $totalSales) * 100, 1) : 0 }}%)
                </p>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">Rincian Modal Usaha per Kategori</h2>
            </div>

            <div class="p-6">
                @if($expensesByCategory->count() > 0)
                    <div class="space-y-4">
                        @foreach($expensesByCategory as $category)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                            @if($category['type'] === 'daily') bg-blue-100
                                                            @elseif($category['type'] === 'monthly') bg-purple-100
                                                            @elseif($category['type'] === 'payroll') bg-orange-100
                                                            @else bg-green-100
                                                            @endif">
                                        <i class="fas 
                                                                @if($category['type'] === 'daily') fa-calendar-day text-blue-600
                                                                @elseif($category['type'] === 'monthly') fa-calendar-alt text-purple-600
                                                                @elseif($category['type'] === 'payroll') fa-money-bill-wave text-orange-600
                                                                @else fa-box text-green-600
                                                                @endif"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800">{{ $category['name'] }}</div>
                                        <div class="text-xs text-slate-500">
                                            @if($category['type'] === 'daily') Harian
                                            @elseif($category['type'] === 'monthly') Bulanan
                                            @elseif($category['type'] === 'payroll') Gaji Karyawan
                                            @else Bahan Baku
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-slate-800">
                                        Rp {{ number_format($category['total'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $totalExpenses > 0 ? number_format(($category['total'] / $totalExpenses) * 100, 1) : 0 }}%
                                        dari total
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Total Row -->
                    <div class="mt-6 pt-4 border-t-2 border-slate-300">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-slate-800">Total Modal Usaha</span>
                            <span class="text-2xl font-bold text-slate-900">
                                Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-slate-500">
                        <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                        <p>Belum ada data pengeluaran untuk bulan ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Profit Summary Box -->
        <div class="mt-6 bg-gradient-to-r from-slate-800 to-slate-900 rounded-lg shadow-lg p-6 text-white">
            <div class="text-center">
                <div class="text-sm font-medium opacity-75 mb-2">PROFIT BERSIH BULAN INI</div>
                <div class="text-4xl font-bold mb-1">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </div>
                <div class="text-sm opacity-75">
                    = Penjualan Kotor (Rp {{ number_format($totalSales, 0, ',', '.') }})
                    - Modal Usaha (Rp {{ number_format($totalExpenses, 0, ',', '.') }})
                </div>
            </div>
        </div>
    </div>
@endsection