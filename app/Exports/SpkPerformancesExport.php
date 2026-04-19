<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SpkPerformancesExport implements FromView, ShouldAutoSize
{
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate ?? \Carbon\Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = $endDate ?? \Carbon\Carbon::now()->endOfMonth()->toDateString();
    }

    public function view(): View
    {
        $employees = Employee::orderBy('name')->get();

        $data = collect();

        foreach ($employees as $employee) {
            // Get design transactions
            $designTransactions = Transaction::query()
                ->where('desainer_id', $employee->id)
                ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
                ->with(['customer', 'items.product'])
                ->get();

            foreach ($designTransactions as $tx) {
                $productNames = $tx->items->map(fn($item) => $item->product?->name ?? $item->custom_name ?? 'Item')->implode(', ');
                $data->push([
                    'employee' => $employee->name,
                    'type' => 'Design',
                    'date' => $tx->created_at->format('Y-m-d H:i'),
                    'invoice' => $tx->invoice_number,
                    'customer' => $tx->customer?->name ?? 'Umum',
                    'detail' => $productNames,
                ]);
            }

            // Get produksi transactions
            $produksiTransactions = Transaction::query()
                ->where(function($query) use ($employee) {
                    $query->where('eksekutor_id', $employee->id)
                          ->orWhere('eksekutor_2_id', $employee->id);
                })
                ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
                ->with(['customer', 'items.product'])
                ->get();

            foreach ($produksiTransactions as $tx) {
                $productNames = $tx->items->map(fn($item) => $item->product?->name ?? $item->custom_name ?? 'Item')->implode(', ');
                $data->push([
                    'employee' => $employee->name,
                    'type' => 'Produksi',
                    'date' => $tx->created_at->format('Y-m-d H:i'),
                    'invoice' => $tx->invoice_number,
                    'customer' => $tx->customer?->name ?? 'Umum',
                    'detail' => $productNames,
                ]);
            }
        }

        // Sort data primarily by employee name, then type (Design/Produksi), then date
        $sortedData = $data->sortBy(['employee', 'type', 'date']);

        return view('exports.spk_performances', [
            'data' => $sortedData,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
