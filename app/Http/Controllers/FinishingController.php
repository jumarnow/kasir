<?php

namespace App\Http\Controllers;


use App\Models\Finishing;
use Illuminate\Http\Request;

class FinishingController extends Controller
{
    public function index(Request $request)
    {
        $query = Finishing::query();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $finishings = $query->latest()->paginate(10);

        return view('finishings.index', compact('finishings'));
    }

    public function create()
    {
        return view('finishings.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:finishings,code',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:per_meter,per_dimension,per_unit', // Assuming these are the types
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Finishing::create($validated);

        return redirect()->route('finishings.index')
            ->with('success', 'Finishing created successfully.');
    }

    public function edit(Finishing $finishing)
    {
        return view('finishings.form', compact('finishing'));
    }

    public function update(Request $request, Finishing $finishing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:finishings,code,' . $finishing->id,
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:per_meter,per_dimension,per_unit',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $finishing->update($validated);

        return redirect()->route('finishings.index')
            ->with('success', 'Finishing updated successfully.');
    }

    public function destroy(Finishing $finishing)
    {
        $finishing->delete();

        return redirect()->route('finishings.index')
            ->with('success', 'Finishing deleted successfully.');
    }
}
