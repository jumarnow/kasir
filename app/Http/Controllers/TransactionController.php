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
            ->when($request->query('start_date'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('end_date'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($request->query('q'), fn ($query, $term) => $query->where('invoice_number', 'like', '%' . $term . '%'))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'filters' => $request->only(['start_date', 'end_date', 'q']),
        ]);
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->take(50)
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'price_2', 'price_3', 'cost_price', 'stock']);

        return view('transactions.create', compact('customers', 'products'));
    }

    public function store(StoreTransactionRequest $request)
    {
        $shouldPrintInvoice = $request->boolean('print_invoice');
        $user = $request->user() ?? auth()->user() ?? \App\Models\User::firstOrFail();

        $transaction = $this->transactionService->create($user, $request->validated());

        return redirect()->route('transactions.create')
            ->with('success', 'Transaksi berhasil dibuat.')
            ->with('print_invoice', $shouldPrintInvoice)
            ->with('printed_transaction_id', $transaction->id);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    public function invoice(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);

        return view('transactions.invoice', compact('transaction'));
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

        if (! $product) {
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
            'cost_price' => (float) $product->cost_price,
        ]);
    }
}
