@extends('layouts.app')

@section('title', 'Transaksi Baru')
@section('subtitle', 'Proses penjualan dengan pemindaian barcode dan perhitungan otomatis')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.css">
    <link rel="stylesheet" href="{{ asset('css/transaction-create.css') }}">
@endpush

@if ((session('print_invoice') || session('print_shipping_label')) && session('printed_transaction_id'))
    @push('scripts')
        <script>
            window.addEventListener('load', function () {
                if (sessionStorage.getItem('kasirInvoicePrintRequested') !== '1' && sessionStorage.getItem('kasirShippingLabelPrintRequested') !== '1') {
                    return;
                }

                const transactionId = '{{ session('printed_transaction_id') }}';
                const printInvoice = sessionStorage.getItem('kasirInvoicePrintRequested') === '1';
                const printShipping = sessionStorage.getItem('kasirShippingLabelPrintRequested') === '1';

                try {
                    sessionStorage.removeItem('kasirInvoicePrintRequested');
                    sessionStorage.removeItem('kasirShippingLabelPrintRequested');
                } catch (error) { }

                const features = 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes';

                if (printInvoice) {
                    const invoiceWindow = window.open('{{ route('transactions.invoice', ['transaction' => session('printed_transaction_id')]) }}', 'invoice-print', features);
                    if (invoiceWindow) invoiceWindow.focus();
                }

                if (printShipping) {
                    const shippingWindow = window.open('{{ route('transactions.shipping_label', ['transaction' => session('printed_transaction_id')]) }}', 'shipping-print', 'width=400,height=600');
                    if (shippingWindow) shippingWindow.focus();
                }
            });
        </script>
    @endpush
@endif

@section('content')
    <form action="{{ route('transactions.store') }}" method="POST" id="transaction-form">
        @csrf
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
                    store: '{{ route('transactions.store') }}',
                    lookup: '{{ route('transactions.lookup') }}',
                    customersStore: '{{ route('customers.store') }}'
                },
                existingDueDate: '{{ old('due_date', date('Y-m-d')) }}'
            });
        });
    </script>
@endpush