@extends('layouts.app')

@section('title', 'Edit Transaksi')
@section('subtitle', 'Perbarui data transaksi #' . $transaction->invoice_number)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.css">
    <link rel="stylesheet" href="{{ asset('css/transaction-create.css') }}">
@endpush

@section('content')
    <form action="{{ route('transactions.update', $transaction) }}" method="POST" id="transaction-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="print_invoice" id="print-invoice" value="0">
        <input type="hidden" name="print_shipping_label" id="print-shipping-label" value="0">

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Left Column: Customer, Execution, Items --}}
            <div class="lg:col-span-2 space-y-6">
                @include('transactions.partials._customer-section')
                @include('transactions.partials._execution-section')
                @include('transactions.partials._items-section')
            </div>

            {{-- Right Column: Payment Summary --}}
            <div class="space-y-6">
                @include('transactions.partials._payment-section')
            </div>
        </div>
    </form>

    {{-- Modal Dialogs --}}
    @include('transactions.partials._modals')
@endsection

@php
    $existingItems = $transaction->items->map(function ($item) {
        return [
            'id' => $item->product_id,
            'name' => $item->product->name ?? 'Unknown',
            'price' => (float) $item->price,
            'price_1' => (float) ($item->product->price ?? $item->price),
            'price_2' => (float) ($item->product->price_2 ?? 0),
            'price_3' => (float) ($item->product->price_3 ?? 0),
            'cost_price' => (float) $item->cost_price,
            'stock' => $item->product->stock ?? 0,
            'stock_alert' => $item->product->stock_alert ?? 0,
            'quantity' => $item->quantity,
            'pricing_type' => $item->product->pricing_type ?? 'per_unit',
            'price_per_meter' => (float) ($item->product->price_per_meter ?? 0),
            'price_unit' => $item->product->price_unit ?? 'per_m2',
            'width' => (float) $item->width,
            'length' => (float) $item->length,
            'area' => (float) ($item->width * $item->length),
            'finishing_id' => $item->finishing_id,
            'material_id' => $item->material_id,
            'material_price_tier' => '1',
            'product_price_tier' => '1',
            'display_id' => $item->display_id,
        ];
    });
@endphp

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.min.js"></script>
    <script src="{{ asset('js/transaction-create.js') }}"></script>
    <script>
        $(function () {
            // Initialize the Transaction App with server-side data
            initTransactionApp({
                products: @json($products),
                customers: @json($customers),
                finishings: @json($finishings ?? []),
                displays: @json($displays ?? []),
                materials: @json($materials ?? []),
                csrfToken: '{{ csrf_token() }}',
                routes: {
                    store: '{{ route('transactions.update', $transaction) }}',
                    lookup: '{{ route('transactions.lookup') }}',
                    customersStore: '{{ route('customers.store') }}'
                },
                // Pre-load existing items for edit mode
                existingItems: @json($existingItems),
                existingCustomerId: {{ $transaction->customer_id ?? 'null' }},
                existingEksekutorId: {{ $transaction->eksekutor_id ?? 'null' }},
                existingPaymentMethod: '{{ $transaction->payment_method }}',
                existingNotes: @json($transaction->notes ?? ''),
                existingDiscountPercent: {{ $transaction->discount_percent ?? 0 }},
                existingDiscountAmount: {{ $transaction->discount_amount ?? 0 }},
                existingShippingCost: {{ $transaction->shipping_cost ?? 0 }},
                existingAmountPaid: {{ $transaction->amount_paid ?? 0 }},
                existingDueDate: '{{ $transaction->due_date ? $transaction->due_date->format('Y-m-d') : '' }}'
            });
        });
    </script>
@endpush