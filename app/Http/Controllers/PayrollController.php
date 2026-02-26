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

        // Filter by employee type
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
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
            'working_days' => 'nullable|integer|min:0|max:31',
            'basic_salary' => 'required|numeric|min:0',
            'tunjangan_makan' => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'tunjangan_lembur' => 'nullable|numeric|min:0',
            'bonus_kehadiran' => 'nullable|numeric|min:0',
            'bonus_target' => 'nullable|numeric|min:0',
            'potongan_items' => 'nullable|array',
            'potongan_items.*' => 'nullable|numeric|min:0',
            'potongan_notes_items' => 'nullable|array',
            'potongan_notes_items.*' => 'nullable|string|max:255',
        ]);

        // Cek duplikasi
        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('period_month', $request->period_month)
            ->where('period_year', $request->period_year)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['error' => 'Slip gaji untuk pegawai ini pada periode tersebut sudah ada.'])
                ->withInput();
        }

        // Process potongan items
        $potonganItems = $validated['potongan_items'] ?? [];
        $potonganNotes = $validated['potongan_notes_items'] ?? [];
        $totalPotongan = array_sum(array_map('floatval', $potonganItems));
        $notesArr = [];
        foreach ($potonganItems as $i => $amt) {
            $amt = floatval($amt);
            $note = trim($potonganNotes[$i] ?? '');
            if ($amt > 0 || $note) {
                $notesArr[] = $note ? $note . ': Rp ' . number_format($amt, 0, ',', '.') : 'Rp ' . number_format($amt, 0, ',', '.');
            }
        }
        $validated['potongan'] = $totalPotongan;
        $validated['potongan_notes'] = implode(' | ', $notesArr);
        unset($validated['potongan_items'], $validated['potongan_notes_items']);

        // Get employee data for snapshot
        $employee = Employee::findOrFail($validated['employee_id']);
        $validated['employee_type'] = $employee->employee_type;
        $validated['daily_salary'] = $employee->daily_salary;
        $validated['working_days'] = $validated['working_days'] ?? 0;

        // Calculate basic salary based on employee type
        if ($employee->isDailyPaid()) {
            $validated['basic_salary'] = $employee->daily_salary * $validated['working_days'];
        }

        // Hitung net salary
        $validated['net_salary'] = $validated['basic_salary']
            + ($validated['tunjangan_makan'] ?? 0)
            + ($validated['tunjangan_transport'] ?? 0)
            + ($validated['tunjangan_jabatan'] ?? 0)
            + ($validated['tunjangan_lembur'] ?? 0)
            + ($validated['bonus_kehadiran'] ?? 0)
            + ($validated['bonus_target'] ?? 0)
            - ($validated['potongan'] ?? 0);

        $validated['status'] = 'draft';

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
            'working_days' => 'nullable|integer|min:0|max:31',
            'basic_salary' => 'required|numeric|min:0',
            'tunjangan_makan' => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'tunjangan_lembur' => 'nullable|numeric|min:0',
            'bonus_kehadiran' => 'nullable|numeric|min:0',
            'bonus_target' => 'nullable|numeric|min:0',
            'potongan_items' => 'nullable|array',
            'potongan_items.*' => 'nullable|numeric|min:0',
            'potongan_notes_items' => 'nullable|array',
            'potongan_notes_items.*' => 'nullable|string|max:255',
        ]);

        // Process potongan items
        $potonganItems = $validated['potongan_items'] ?? [];
        $potonganNotes = $validated['potongan_notes_items'] ?? [];
        $totalPotongan = array_sum(array_map('floatval', $potonganItems));
        $notesArr = [];
        foreach ($potonganItems as $i => $amt) {
            $amt = floatval($amt);
            $note = trim($potonganNotes[$i] ?? '');
            if ($amt > 0 || $note) {
                $notesArr[] = $note ? $note . ': Rp ' . number_format($amt, 0, ',', '.') : 'Rp ' . number_format($amt, 0, ',', '.');
            }
        }
        $validated['potongan'] = $totalPotongan;
        $validated['potongan_notes'] = implode(' | ', $notesArr);
        unset($validated['potongan_items'], $validated['potongan_notes_items']);

        // Recalculate basic salary for daily-paid employees
        if ($payroll->isDailyPaid()) {
            $workingDays = $validated['working_days'] ?? $payroll->working_days;
            $validated['working_days'] = $workingDays;
            $validated['basic_salary'] = $payroll->daily_salary * $workingDays;
        }

        // Hitung net salary
        $validated['net_salary'] = $validated['basic_salary']
            + ($validated['tunjangan_makan'] ?? 0)
            + ($validated['tunjangan_transport'] ?? 0)
            + ($validated['tunjangan_jabatan'] ?? 0)
            + ($validated['tunjangan_lembur'] ?? 0)
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

    /**
     * Get employee data via AJAX for form
     */
    public function getEmployeeData(Employee $employee)
    {
        return response()->json([
            'id' => $employee->id,
            'name' => $employee->name,
            'employee_type' => $employee->employee_type,
            'employee_type_label' => $employee->employee_type_label,
            'basic_salary' => $employee->basic_salary,
            'daily_salary' => $employee->daily_salary,
            'is_daily_paid' => $employee->isDailyPaid(),
        ]);
    }
}
