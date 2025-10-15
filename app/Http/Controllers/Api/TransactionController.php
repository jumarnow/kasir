<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
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
        $perPage = (int) $request->query('per_page', 15);

        $transactions = Transaction::query()
            ->with(['customer', 'user'])
            ->betweenDates($request->query('start_date'), $request->query('end_date'))
            ->when($request->query('q'), function ($query, $term) {
                $query->where('invoice_number', 'like', '%' . $term . '%');
            })
            ->orderByDesc('created_at')
            ->paginate($perPage > 0 ? $perPage : 15)
            ->withQueryString();

        return TransactionResource::collection($transactions);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);

        return new TransactionResource($transaction);
    }

    public function store(StoreTransactionRequest $request)
    {
        $transaction = $this->transactionService->create($request->user(), $request->validated());

        return (new TransactionResource($transaction->load(['items.product', 'customer', 'user'])))
            ->additional([
                'message' => 'Transaksi berhasil dibuat.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}
