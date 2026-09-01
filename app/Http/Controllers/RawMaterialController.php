<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\RawMaterialMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exports\RawMaterialTemplateExport;
use App\Imports\RawMaterialsImport;
use App\Http\Requests\RawMaterialImportRequest;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = RawMaterial::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }

        $rawMaterials = $query->orderBy('name')->paginate(15);
        return view('raw_materials.index', compact('rawMaterials'));
    }

    public function create()
    {
        return view('raw_materials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:raw_materials',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['stock'] = 0; // Initial stock is always 0, must use scan in
        $validated['min_stock'] = $validated['min_stock'] ?? 0;

        RawMaterial::create($validated);

        return redirect()->route('raw-materials.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(RawMaterial $rawMaterial)
    {
        return view('raw_materials.edit', compact('rawMaterial'));
    }

    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:raw_materials,barcode,' . $rawMaterial->id,
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $rawMaterial->update($validated);

        return redirect()->route('raw-materials.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(RawMaterial $rawMaterial)
    {
        $rawMaterial->delete();
        return redirect()->route('raw-materials.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }

    public function generateBarcode(RawMaterial $rawMaterial)
    {
        if (empty($rawMaterial->barcode)) {
            $rawMaterial->barcode = 'RM-' . strtoupper(Str::random(8));
            $rawMaterial->save();
        }
        
        return response()->json([
            'success' => true,
            'barcode' => $rawMaterial->barcode,
            'name' => $rawMaterial->name
        ]);
    }

    public function bulkBarcode(Request $request)
    {
        $materialIds = $request->input('material_ids', []);

        if (empty($materialIds) || $request->input('all') == '1') {
            $rawMaterials = RawMaterial::all();
        } else {
            $rawMaterials = RawMaterial::whereIn('id', $materialIds)->get();
        }

        if ($rawMaterials->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu bahan baku.');
        }

        return view('raw_materials.bulk-barcode', compact('rawMaterials'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
        ]);

        $rawMaterial = RawMaterial::where('barcode', $request->barcode)
            ->orWhere('sku', $request->barcode)
            ->first();

        if (!$rawMaterial) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan dengan barcode/sku tersebut.'
            ], 404);
        }

        if ($request->type === 'out' && $rawMaterial->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Stock tidak mencukupi. Stock saat ini: {$rawMaterial->stock} {$rawMaterial->unit}"
            ], 400);
        }

        try {
            DB::transaction(function () use ($rawMaterial, $request) {
                $previousStock = $rawMaterial->stock;
                
                if ($request->type === 'in') {
                    $rawMaterial->incrementStock($request->quantity);
                } else {
                    $rawMaterial->decrementStock($request->quantity);
                }

                $rawMaterial->refresh(); // Ambil stock terbaru setelah disave

                RawMaterialMutation::create([
                    'raw_material_id' => $rawMaterial->id,
                    'type' => $request->type,
                    'quantity' => $request->quantity,
                    'previous_stock' => $previousStock,
                    'current_stock' => $rawMaterial->stock,
                    'reference' => 'Scan Barcode',
                    'user_id' => auth()->id(),
                ]);
            });

            $typeLabel = $request->type === 'in' ? 'ditambahkan' : 'dikurangi';
            return response()->json([
                'success' => true,
                'message' => "Berhasil! Stock {$rawMaterial->name} {$typeLabel} sebanyak {$request->quantity} {$rawMaterial->unit}.",
                'data' => [
                    'id' => $rawMaterial->id,
                    'name' => $rawMaterial->name,
                    'current_stock' => $rawMaterial->fresh()->stock,
                    'unit' => $rawMaterial->unit
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new RawMaterialTemplateExport(), 'template-import-bahan-baku.xlsx');
    }

    public function import(RawMaterialImportRequest $request)
    {
        $import = new RawMaterialsImport();

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
            ->route('raw-materials.index')
            ->with('success', "Import bahan baku berhasil. {$summary['created']} data baru ditambahkan, {$summary['updated']} data diperbarui.");
    }
}
