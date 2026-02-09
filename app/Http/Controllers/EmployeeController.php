<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_employees')->only('index');
        $this->middleware('permission:create_employees')->only(['create', 'store']);
        $this->middleware('permission:edit_employees')->only(['edit', 'update']);
        $this->middleware('permission:delete_employees')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        // Filter by employee type
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        $employees = $query->latest()->paginate(10);

        return view('payroll.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('payroll.employees.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:employees,employee_id',
            'name' => 'required|string|max:100',
            'position' => 'required|string|max:50',
            'employee_type' => 'required|in:permanent,intern,internship',
            'join_date' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'daily_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);

        // Set default values based on employee type
        if (in_array($validated['employee_type'], ['intern', 'internship'])) {
            $validated['basic_salary'] = $validated['basic_salary'] ?? 0;
        } else {
            $validated['daily_salary'] = $validated['daily_salary'] ?? 0;
        }

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Data pegawai berhasil ditambahkan');
    }

    public function edit(Employee $employee)
    {
        return view('payroll.employees.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:employees,employee_id,' . $employee->id,
            'name' => 'required|string|max:100',
            'position' => 'required|string|max:50',
            'employee_type' => 'required|in:permanent,intern,internship',
            'join_date' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'daily_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);

        // Set default values based on employee type
        if (in_array($validated['employee_type'], ['intern', 'internship'])) {
            $validated['basic_salary'] = $validated['basic_salary'] ?? 0;
        } else {
            $validated['daily_salary'] = $validated['daily_salary'] ?? 0;
        }

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Data pegawai berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Data pegawai berhasil dihapus');
    }
}
