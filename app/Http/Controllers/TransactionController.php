<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'search', 'payment_status', 'order_status', 'delivery_method', 'user_id']);

        $transactions = $this->filteredTransactionsQuery($filters)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $customers = \App\Models\Customer::orderBy('name')->get(['id', 'name']);
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('transactions.index', [
            'transactions' => $transactions,
            'customers' => $customers,
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'search', 'payment_status', 'order_status', 'delivery_method', 'user_id']);
        $filename = 'transaksi-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new TransactionsExport($filters), $filename);
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'price_tier']);
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'price_2', 'price_3', 'cost_price', 'stock', 'stock_alert', 'pricing_type', 'price_per_meter', 'price_unit', 'min_width', 'min_length', 'min_qty']);

        $finishings = \App\Models\Finishing::all();
        $displays = \App\Models\Display::all();
        $materials = \App\Models\Material::all();
        $employees = \App\Models\Employee::active()->orderBy('name')->get(['id', 'name']);

        return view('transactions.create', compact('customers', 'products', 'finishings', 'displays', 'materials', 'employees'));
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
        $transaction->load(['items.product', 'items.finishing', 'items.material', 'items.display', 'customer', 'user', 'paymentUser']);

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
            'min_qty' => (int) ($product->min_qty ?? 1),
        ]);
    }
    public function storePayment(Request $request, Transaction $transaction)
    {
        $amount = toNumeric($request->input('amount', 0));
        $cashReceived = toNumeric($request->input('cash_received', 0));

        if ($amount <= 0) {
            return back()->with('error', 'Jumlah pembayaran tidak valid.');
        }

        if ($amount > $transaction->remaining_amount) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        $paymentType = $request->input('payment_type', $transaction->payment_type);
        if ($paymentType === 'tunai' && $cashReceived > $amount) {
            $actualPaid = $cashReceived;
        } else {
            $actualPaid = $amount;
        }

        // Record payment
        $transaction->amount_paid += $actualPaid;
        $transaction->change_due = max(0, $transaction->amount_paid - $transaction->total);

        if ($request->has('payment_type')) {
            $transaction->payment_type = $request->input('payment_type');
        }
        $transaction->payment_user_id = $request->user()->id ?? auth()->id();
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
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'price_2', 'price_3', 'cost_price', 'stock', 'stock_alert', 'pricing_type', 'price_per_meter', 'price_unit', 'min_width', 'min_length']);

        $finishings = \App\Models\Finishing::all();
        $displays = \App\Models\Display::all();
        $materials = \App\Models\Material::all();
        $employees = \App\Models\Employee::active()->orderBy('name')->get(['id', 'name']);

        return view('transactions.edit', compact('transaction', 'customers', 'products', 'finishings', 'displays', 'materials', 'employees'));
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

    public function reject(Request $request, Transaction $transaction)
    {
        $this->authorize('delete_transactions');

        $request->validate([
            'reject_reason' => 'required|string|max:255'
        ]);

        $transaction->status = 'rejected';
        $transaction->reject_reason = $request->reject_reason;
        $transaction->save();
        
        return redirect()->route('transactions.index')
            ->with('success', 'Status transaksi berhasil diubah menjadi reject.');
    }

    private function filteredTransactionsQuery(array $filters)
    {
        return Transaction::with(['customer', 'user', 'items.product', 'paymentUser'])
            ->when($filters['start_date'] ?? null, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['end_date'] ?? null, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function ($query, $term) {
                if (strtolower($term) === 'problem') {
                    $query->where(function ($q) {
                        $q->where('status', 'rejected');
                    });
                } else {
                    $query->where(function ($q) use ($term) {
                        $q->where('invoice_number', 'like', '%' . $term . '%')
                          ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%' . $term . '%'))
                          ->orWhereHas('items', function ($iq) use ($term) {
                              $iq->where('custom_name', 'like', '%' . $term . '%')
                                 ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', '%' . $term . '%'));
                          });
                    });
                }
            })
            ->when($filters['payment_status'] ?? null, function ($query, $status) {
                if ($status === 'cod_kurir') {
                    return $query->where('payment_method', 'cod_kurir');
                }

                return $query->where('payment_status', $status)
                    ->where('payment_method', '!=', 'cod_kurir');
            })
            ->when($filters['order_status'] ?? null, fn($query, $status) => $query->where('order_status', $status))
            ->when($filters['delivery_method'] ?? null, function ($query, $method) {
                if ($method === 'none') {
                    return $query->whereNull('delivery_method')->orWhere('delivery_method', '');
                }
                return $query->where('delivery_method', $method);
            })
            ->when($filters['user_id'] ?? null, fn($query, $userId) => $query->where('user_id', $userId));
    }
}
