<?php

namespace App\Http\Controllers;


use App\Models\Display;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function index(Request $request)
    {
        $query = Display::query();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $displays = $query->latest()->paginate(10);

        return view('displays.index', compact('displays'));
    }

    public function create()
    {
        return view('displays.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:displays,code',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'stock_alert' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Display::create($validated);

        return redirect()->route('displays.index')
            ->with('success', 'Display created successfully.');
    }

    public function edit(Display $display)
    {
        return view('displays.form', compact('display'));
    }

    public function update(Request $request, Display $display)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:displays,code,' . $display->id,
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'stock_alert' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $display->update($validated);

        return redirect()->route('displays.index')
            ->with('success', 'Display updated successfully.');
    }

    public function destroy(Display $display)
    {
        $display->delete();

        return redirect()->route('displays.index')
            ->with('success', 'Display deleted successfully.');
    }
}
