<?php

namespace App\Http\Controllers;


use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $materials = $query->latest()->paginate(10);

        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        return view('materials.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:materials,code',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_alert' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Material::create($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Material created successfully.');
    }

    public function edit(Material $material)
    {
        return view('materials.form', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:materials,code,' . $material->id,
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_alert' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material deleted successfully.');
    }
}
