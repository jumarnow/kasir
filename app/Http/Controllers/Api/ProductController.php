<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $products = Product::query()
            ->where('is_active', true)
            ->search($request->query('q'))
            ->orderBy('name')
            ->paginate($perPage > 0 ? $perPage : 20)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product);
    }

    public function lookup(Request $request)
    {
        $barcode = $request->query('barcode');

        if (! $barcode) {
            return response()->json([
                'message' => 'Parameter barcode wajib diisi.',
            ], 422);
        }

        $product = Product::where('is_active', true)
            ->where(function ($query) use ($barcode) {
                $query->where('barcode', $barcode)
                    ->orWhere('sku', $barcode);
            })
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return new ProductResource($product);
    }
}
