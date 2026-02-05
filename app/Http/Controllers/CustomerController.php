<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_customers');

        $customers = Customer::query()
            ->when($request->query('q'), function ($query, $term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'search' => $request->query('q'),
        ]);
    }

    public function create()
    {
        $this->authorize('create_customers');

        return view('customers.form');
    }

    public function store(CustomerRequest $request)
    {
        $this->authorize('create_customers');

        $customer = Customer::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json($customer);
        }

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        $this->authorize('edit_customers');

        return view('customers.form', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $this->authorize('edit_customers');

        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete_customers');

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
