<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->search($request->query('q'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'search' => $request->query('q'),
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');

        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $payload = $request->validated();
        $payload['cost_price'] = $payload['cost_price'] ?? $payload['price'];
        $payload['stock'] = $payload['stock'] ?? 0;
        $payload['stock_alert'] = $payload['stock_alert'] ?? 0;

        Product::create($payload);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $payload = $request->validated();
        $payload['cost_price'] = $payload['cost_price'] ?? $payload['price'];

        $product->update($payload);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->transactionItems()->exists()) {
            return back()->withErrors(['product' => 'Produk tidak dapat dihapus karena sudah digunakan pada transaksi.']);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function barcode(Product $product)
    {
        return view('products.barcode', compact('product'));
    }
}

