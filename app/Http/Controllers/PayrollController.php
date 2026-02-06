<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_payrolls')->only('index');
        $this->middleware('permission:create_payrolls')->only(['create', 'store']);
        $this->middleware('permission:edit_payrolls')->only(['edit', 'update', 'markPaid']);
        $this->middleware('permission:delete_payrolls')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->filled('month')) {
            $query->where('period_month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $payrolls = $query->latest()->paginate(10);
        $employees = Employee::orderBy('name')->get();

        return view('payroll.payrolls.index', compact('payrolls', 'employees'));
    }

    public function create()
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('payroll.payrolls.form', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2000|max:2099',
            'basic_salary' => 'required|numeric|min:0',
            'tunjangan_makan' => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'bonus_kehadiran' => 'nullable|numeric|min:0',
            'bonus_target' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'potongan_notes' => 'nullable|string',
        ]);

        // Cek duplikasi
        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('period_month', $request->period_month)
            ->where('period_year', $request->period_year)
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Slip gaji untuk pegawai ini pada periode tersebut sudah ada.'])->withInput();
        }

        // Hitung net salary
        $validated['net_salary'] = $validated['basic_salary']
            + ($validated['tunjangan_makan'] ?? 0)
            + ($validated['tunjangan_transport'] ?? 0)
            + ($validated['tunjangan_jabatan'] ?? 0)
            + ($validated['bonus_kehadiran'] ?? 0)
            + ($validated['bonus_target'] ?? 0)
            - ($validated['potongan'] ?? 0);

        $validated['status'] = $request->has('save_as_draft') ? 'draft' : 'draft'; // Default draft logic currently used, logic can be updated for immediate pay

        $payroll = Payroll::create($validated);

        if ($request->has('save_and_print')) {
            return redirect()->route('payrolls.show', $payroll);
        }

        return redirect()->route('payrolls.index')
            ->with('success', 'Slip gaji berhasil dibuat');
    }

    public function show(Payroll $payroll)
    {
        return view('payroll.payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Slip gaji yang sudah dibayar tidak dapat diedit.');
        }
        $employees = Employee::active()->orderBy('name')->get();
        return view('payroll.payrolls.form', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Slip gaji yang sudah dibayar tidak dapat diedit.');
        }

        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'tunjangan_makan' => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'bonus_kehadiran' => 'nullable|numeric|min:0',
            'bonus_target' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'potongan_notes' => 'nullable|string',
        ]);

        // Hitung net salary
        $validated['net_salary'] = $validated['basic_salary']
            + ($validated['tunjangan_makan'] ?? 0)
            + ($validated['tunjangan_transport'] ?? 0)
            + ($validated['tunjangan_jabatan'] ?? 0)
            + ($validated['bonus_kehadiran'] ?? 0)
            + ($validated['bonus_target'] ?? 0)
            - ($validated['potongan'] ?? 0);

        $payroll->update($validated);

        if ($request->has('save_and_print')) {
            return redirect()->route('payrolls.show', $payroll);
        }

        return redirect()->route('payrolls.index')
            ->with('success', 'Slip gaji berhasil diperbarui');
    }

    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Slip gaji yang sudah dibayar tidak dapat dihapus.');
        }
        $payroll->delete();
        return redirect()->route('payrolls.index')
            ->with('success', 'Slip gaji berhasil dihapus');
    }

    public function markPaid(Payroll $payroll)
    {
        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Slip gaji ditandai sudah dibayar');
    }

    public function print(Payroll $payroll)
    {
        $pdf = Pdf::loadView('payroll.payrolls.pdf', compact('payroll'));
        return $pdf->stream("SLIP-{$payroll->period_month}-{$payroll->period_year}-{$payroll->employee->name}.pdf");
    }
}
