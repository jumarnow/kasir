<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function index(Request $request)
    {
        $transactions = Transaction::with(['customer', 'user'])
            ->when($request->query('start_date'), fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('end_date'), fn($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($request->query('customer_id'), fn($query, $customerId) => $query->where('customer_id', $customerId))
            ->when($request->query('payment_status'), fn($query, $status) => $query->where('payment_status', $status))
            ->when($request->query('q'), fn($query, $term) => $query->where('invoice_number', 'like', '%' . $term . '%'))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $customers = \App\Models\Customer::orderBy('name')->get(['id', 'name']);

        return view('transactions.index', [
            'transactions' => $transactions,
            'customers' => $customers,
            'filters' => $request->only(['start_date', 'end_date', 'q', 'customer_id', 'payment_status']),
        ]);
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'price_tier']);
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->take(50)
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'price_2', 'price_3', 'cost_price', 'stock', 'stock_alert', 'pricing_type', 'price_per_meter', 'price_unit', 'min_width', 'min_length']);

        $finishings = \App\Models\Finishing::all();
        $displays = \App\Models\Display::all();
        $materials = \App\Models\Material::all();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('transactions.create', compact('customers', 'products', 'finishings', 'displays', 'materials', 'users'));
    }

    public function store(StoreTransactionRequest $request)
    {
        $shouldPrintInvoice = $request->boolean('print_invoice');
        $shouldPrintShippingLabel = $request->boolean('print_shipping_label');
        $user = $request->user() ?? auth()->user() ?? \App\Models\User::firstOrFail();

        $transaction = $this->transactionService->create($user, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibuat.',
                'transaction_id' => $transaction->id,
                'print_invoice' => $shouldPrintInvoice,
                'print_shipping_label' => $shouldPrintShippingLabel,
                'print_receipt' => $request->boolean('print_receipt'),
                'print_spk' => $request->boolean('print_spk'),
            ]);
        }

        return redirect()->route('transactions.create')
            ->with('success', 'Transaksi berhasil dibuat.')
            ->with('print_invoice', $shouldPrintInvoice)
            ->with('print_shipping_label', $shouldPrintShippingLabel)
            ->with('printed_transaction_id', $transaction->id);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.finishing', 'items.material', 'items.display', 'customer', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    public function spk(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user', 'files']);

        return view('transactions.spk_thermal', compact('transaction'));
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);
        $settings = \App\Models\Setting::pluck('value', 'key')->all();

        return view('transactions.receipt_thermal', compact('transaction', 'settings'));
    }

    public function invoiceA5(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);
        $settings = \App\Models\Setting::pluck('value', 'key')->all();

        return view('transactions.invoice_a5', compact('transaction', 'settings'));
    }

    public function invoice(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);

        return view('transactions.invoice', compact('transaction'));
    }

    public function shippingLabel(Transaction $transaction)
    {
        $transaction->load(['customer', 'user']);

        return view('transactions.shipping_label', compact('transaction'));
    }

    public function lookupByBarcode(Request $request)
    {
        $product = Product::where('is_active', true)
            ->where(function ($query) use ($request) {
                $barcode = $request->query('barcode');
                $query->where('barcode', $barcode)
                    ->orWhere('sku', $barcode);
            })
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'price' => (float) $product->price,
            'price_2' => (float) ($product->price_2 ?? 0),
            'price_3' => (float) ($product->price_3 ?? 0),
            'stock' => (int) $product->stock,
            'stock_alert' => (int) ($product->stock_alert ?? 0),
            'cost_price' => (float) $product->cost_price,
            'pricing_type' => $product->pricing_type ?? 'per_unit',
            'price_per_meter' => (float) ($product->price_per_meter ?? 0),
            'price_unit' => $product->price_unit ?? 'per_m2',
            'min_width' => (float) ($product->min_width ?? 0),
            'min_length' => (float) ($product->min_length ?? 0),
        ]);
    }
    public function storePayment(Request $request, Transaction $transaction)
    {
        $amount = toNumeric($request->input('amount', 0));

        if ($amount <= 0) {
            return back()->with('error', 'Jumlah pembayaran tidak valid.');
        }

        if ($amount > $transaction->remaining_amount) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        // Record payment
        $transaction->amount_paid += $amount;
        $transaction->save(); // Model's saving event will handle status update and remaining amount calculation

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function edit(Transaction $transaction)
    {
        $this->authorize('edit_transactions');



        $transaction->load(['items.product', 'items.finishing', 'items.material', 'items.display', 'customer']);

        $customers = Customer::orderBy('name')->get(['id', 'name', 'price_tier']);
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->take(50)
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'price_2', 'price_3', 'cost_price', 'stock', 'stock_alert', 'pricing_type', 'price_per_meter', 'price_unit', 'min_width', 'min_length']);

        $finishings = \App\Models\Finishing::all();
        $displays = \App\Models\Display::all();
        $materials = \App\Models\Material::all();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('transactions.edit', compact('transaction', 'customers', 'products', 'finishings', 'displays', 'materials', 'users'));
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('edit_transactions');



        // Restore stock from old items
        foreach ($transaction->items as $item) {
            if ($item->product) {
                $item->product->incrementStock($item->quantity);
            }
        }

        // Delete old items
        $transaction->items()->delete();

        // Use transaction service to update
        $user = $request->user() ?? auth()->user();
        $this->transactionService->update($transaction, $user, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diperbarui.',
                'transaction_id' => $transaction->id,
            ]);
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete_transactions');



        // Restore stock
        foreach ($transaction->items as $item) {
            if ($item->product) {
                $item->product->incrementStock($item->quantity);
            }
        }

        $transaction->delete(); // Soft delete

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dibatalkan dan stok dikembalikan.');
    }
}
