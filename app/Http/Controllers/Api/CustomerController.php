<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $customers = Customer::query()
            ->where('is_active', true)
            ->when($request->query('q'), function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                        ->orWhere('phone', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage > 0 ? $perPage : 20)
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer)
    {
        abort_unless($customer->is_active, 404);

        return new CustomerResource($customer);
    }
}
