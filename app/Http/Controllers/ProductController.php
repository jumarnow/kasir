<?php

namespace App\Http\Controllers;

use App\Exports\ProductTemplateExport;
use App\Http\Requests\ProductImportRequest;
use App\Http\Requests\ProductRequest;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

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

        return view('products.form', compact('categories'));
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

        return view('products.form', compact('product', 'categories'));
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

    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport(), 'template-import-produk.xlsx');
    }

    public function import(ProductImportRequest $request)
    {
        $import = new ProductsImport();

        try {
            $import->import($request->file('import_file'));
        } catch (ValidationException $exception) {
            $messages = collect($exception->errors())->flatten()->all();

            return back()->withErrors($messages);
        } catch (\Throwable $exception) {
            return back()->withErrors(['import_file' => $exception->getMessage()]);
        }

        $summary = $import->summary();

        return redirect()
            ->route('products.index')
            ->with('success', "Import produk berhasil. {$summary['created']} data baru, {$summary['updated']} diperbarui.");
    }
}
