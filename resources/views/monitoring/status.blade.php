@extends('layouts.app')

@section('title', 'Status Order')
@section('subtitle', 'Monitoring status pengerjaan seluruh order')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4 uppercase tracking-wider">Monitoring Order</h2>
        <p class="text-sm text-slate-500 mb-6">Scan barcode, pilih produk, atau cari secara manual</p>
        
        <form action="{{ route('monitoring.status') }}" method="GET" class="flex flex-col md:flex-row gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">NAMA PRODUK / NAMA CUST / INVOICE</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full form-input rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Scan...">
                    <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="h-5 w-5 text-slate-400 hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">PILIH CUSTOMER</label>
                <select name="customer_id" class="w-full form-select rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-none flex items-end">
                @if(request()->hasAny(['search', 'customer_id']))
                    <a href="{{ route('monitoring.status') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-y border-slate-200">
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Nama Customer</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Deskripsi Produk</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Tanggal Pembuatan<br>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Tanggal Design<br>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Tanggal Produksi<br>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Tanggal Selesai<br>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">STATUS<br>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $trx)
                    @foreach($trx->items as $item)
                        @php
                            $designIn = $item->trackings->where('type', \App\Models\ProductionTracking::TYPE_DESIGN_IN)->last();
                            $productionIn = $item->trackings->where('type', \App\Models\ProductionTracking::TYPE_PRODUCTION_IN)->last();
                            $productionOut = $item->trackings->where('type', \App\Models\ProductionTracking::TYPE_PRODUCTION_OUT)->last();
                            $adminOut = $item->trackings->where('type', \App\Models\ProductionTracking::TYPE_ADMIN_OUT)->last();
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors group {{ !$loop->first ? 'border-t border-slate-100/50' : '' }}">
                            @if($loop->first)
                            <td class="px-4 py-4 align-top bg-white border-b border-slate-200" rowspan="{{ count($trx->items) }}">
                                <div class="font-bold text-slate-800">{{ $trx->customer ? $trx->customer->name : 'Walk-in Customer' }}</div>
                                <div class="text-xs text-slate-500 mt-1">{{ $trx->invoice_number }}</div>
                            </td>
                            @endif
                            <td class="px-4 py-4 align-top">
                                <div class="text-sm text-indigo-600 font-medium">{{ $item->product?->name ?? 'Produk Tidak Diketahui' }}</div>
                                @if($item->custom_name) <div class="text-xs text-slate-500">{{ $item->custom_name }}</div> @endif
                                <div class="text-xs text-slate-500 mt-1">Qty: {{ $item->quantity }}</div>
                            </td>
                            <td class="px-4 py-4 align-top text-sm">
                                <div class="text-slate-700">{{ $trx->created_at->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-4 py-4 align-top text-sm">
                                @if($designIn)
                                    <div class="text-slate-700 font-medium">{{ $designIn->tracked_at->format('d M Y, H:i') }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $designIn->user->name ?? 'Designer' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-top text-sm">
                                @if($productionIn)
                                    <div class="text-slate-700 font-medium">{{ $productionIn->tracked_at->format('d M Y, H:i') }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $productionIn->user->name ?? 'Produksi' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-top text-sm">
                                @if($productionOut)
                                    <div class="text-slate-700 font-medium">{{ $productionOut->tracked_at->format('d M Y, H:i') }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $productionOut->user->name ?? 'Produksi' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-top">
                                @if($item->status === 'finished' || $item->pickup_method)
                                    <div class="font-bold text-slate-800 text-sm mb-1">
                                        @if($item->pickup_method === 'customer')
                                            Diambil Customer
                                        @elseif($item->pickup_method === 'kurir')
                                            Diambil Kurir
                                        @elseif($item->pickup_method === 'diantar')
                                            Diantar ke Lokasi
                                        @else
                                            Selesai
                                        @endif
                                    </div>
                                    @if($item->picked_up_at)
                                        <div class="text-sm text-indigo-600 font-medium">{{ \Carbon\Carbon::parse($item->picked_up_at)->format('d M Y, H:i') }}</div>
                                    @endif
                                    @if($item->checked_by)
                                        <div class="text-xs text-slate-500 mt-1">Oleh: {{ $item->checkedBy->name ?? '' }}</div>
                                    @endif
                                @else
                                    @php
                                        $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                        if ($item->status === 'pending') $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                        elseif ($item->status === 'designing' || $item->status === 'production') $statusClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                        elseif ($item->status === 'completed') $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusClass }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                            Tidak ada data orderan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
