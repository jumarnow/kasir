<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Konversi filter bulan → rentang tanggal, default bulan ini
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $query = Expense::with(['category', 'user'])
            ->orderBy('expense_date', 'desc')
            ->betweenDates($startDate, $endDate);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by vendor name
        if ($request->filled('vendor_name')) {
            $query->byVendor($request->vendor_name);
        }

        $expenses = $query->paginate(20)->appends($request->query());
        $categories = ExpenseCategory::active()->orderBy('name')->get();

        // Calculate total for current filter
        $totalExpenses = Expense::with([])
            ->betweenDates($startDate, $endDate)
            ->when($request->filled('category_id'), fn($q) => $q->byCategory($request->category_id))
            ->when($request->filled('vendor_name'), fn($q) => $q->byVendor($request->vendor_name))
            ->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'totalExpenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('expenses.form', ['expense' => null, 'categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'vendor_name' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'receipt_image' => 'nullable|image|max:2048', // 2MB max
        ]);

        $validated['user_id'] = Auth::id();

        // Handle image upload
        if ($request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('receipts', 'public');
            $validated['receipt_image'] = $path;
        }

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('expenses.form', ['expense' => $expense, 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'vendor_name' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('receipt_image')) {
            // Delete old image if exists
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }

            $path = $request->file('receipt_image')->store('receipts', 'public');
            $validated['receipt_image'] = $path;
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        // Delete image if exists
        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
