@extends('layouts.app')

@section('title', 'Status Order')
@section('subtitle', 'Monitoring status pengerjaan seluruh order')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-800 mb-1 uppercase tracking-wider">Monitoring Order</h2>
                <p class="text-sm text-slate-500">Scan barcode, pilih produk, atau cari secara manual</p>
            </div>
            <a href="{{ route('monitoring.status.export', request()->query()) }}" class="mt-4 sm:mt-0 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Excel
            </a>
        </div>
        

        <form action="{{ route('monitoring.status') }}" method="GET" class="mb-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Pencarian</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full form-input text-sm rounded-md border-slate-300 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Invoice, Customer...">
                        <button type="submit" class="absolute inset-y-0 right-0 pr-2 flex items-center">
                            <svg class="h-4 w-4 text-slate-400 hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Customer</label>
                    <select name="customer_id" class="w-full form-select text-sm rounded-md border-slate-300 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Status</label>
                    <select name="status" class="w-full form-select text-sm rounded-md border-slate-300 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach(\App\Models\Transaction::getOrderStatusOptions() as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full form-input text-xs rounded-md border-slate-300 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full form-input text-xs rounded-md border-slate-300 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                </div>

                <div class="md:col-span-1 flex justify-end">
                    @if(request()->hasAny(['search', 'customer_id', 'status', 'start_date', 'end_date']) && (request('search') || request('customer_id') || request('status') || request('start_date') || request('end_date')))
                        <a href="{{ route('monitoring.status') }}" class="w-full bg-white hover:bg-slate-100 text-slate-700 py-1.5 rounded-md text-xs font-medium transition-colors text-center border border-slate-300 shadow-sm">
                            Reset
                        </a>
                    @else
                        <div class="h-[30px]"></div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-y border-slate-200">
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Nama Customer</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Deskripsi Produk</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Tanggal Pembuatan</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Start Design</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Finish Design</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Start Production</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Finish Production</th>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $trx)
                    @foreach($trx->items as $item)
                        @php
                            $designIn = $item->trackings->where('type', \App\Models\ProductionTracking::TYPE_DESIGN_IN)->last();
                            $designOut = $item->trackings->where('type', \App\Models\ProductionTracking::TYPE_DESIGN_OUT)->last();
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
                                <div class="text-sm text-indigo-600 font-medium">{{ $item->product?->name ?? $item->custom_name }}</div>
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
                                @if($designOut)
                                    <div class="text-slate-700 font-medium">{{ $designOut->tracked_at->format('d M Y, H:i') }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $designOut->user->name ?? 'Designer' }}</div>
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
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
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
