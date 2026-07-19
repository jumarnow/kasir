<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringStatusExport;
use App\Models\ProductionTracking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    public function trackIn()
    {
        return view('monitoring.track-in');
    }

    public function storeTrackIn(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|exists:transactions,invoice_number',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:transaction_items,id',
            'track_type' => 'required|in:design,production',
        ]);

        $transaction = Transaction::where('invoice_number', $request->invoice_number)->firstOrFail();
        $user = Auth::user();

        $items = $transaction->items()->whereIn('id', $request->item_ids)->get();

        foreach ($items as $item) {
            if ($request->track_type === 'design') {
                $item->status = 'designing';
                $type = ProductionTracking::TYPE_DESIGN_IN;
                $notes = 'Track In Design by ' . $user->name;
            } else {
                $item->status = 'production';
                $type = ProductionTracking::TYPE_PRODUCTION_IN;
                $notes = 'Track In Produksi by ' . $user->name;
            }
            $item->save();

            ProductionTracking::create([
                'transaction_id' => $transaction->id,
                'transaction_item_id' => $item->id,
                'user_id' => $user->id,
                'type' => $type,
                'tracked_at' => now(),
                'notes' => $notes,
            ]);
        }

        $transaction->updateStatusFromItems();

        return back()->with('success', count($items) . ' item dari Order ' . $transaction->invoice_number . ' berhasil di Track In.');
    }

    public function trackOut()
    {
        return view('monitoring.track-out');
    }

    public function storeTrackOut(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|exists:transactions,invoice_number',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:transaction_items,id',
            'track_type' => 'required|in:design,production,admin',
            'pickup_method' => 'required_if:track_type,admin|nullable|string|in:customer,kurir,diantar',
        ]);

        $transaction = Transaction::where('invoice_number', $request->invoice_number)->firstOrFail();
        $user = Auth::user();

        $items = $transaction->items()->whereIn('id', $request->item_ids)->get();

        foreach ($items as $item) {
            if ($request->track_type === 'design') {
                $type = ProductionTracking::TYPE_DESIGN_OUT;
                $notes = 'Track Out Design by ' . $user->name;
            } elseif ($request->track_type === 'production') {
                $item->status = 'completed';
                $type = ProductionTracking::TYPE_PRODUCTION_OUT;
                $notes = 'Track Out Produksi by ' . $user->name;
            } else {
                // Admin final track out
                $item->status = 'finished';
                $item->pickup_method = $request->pickup_method;
                $item->picked_up_at = now();
                $item->checked_by = $user->id;
                $type = ProductionTracking::TYPE_ADMIN_OUT;
                $notes = 'Track Out Admin by ' . $user->name;
            }
            $item->save();

            ProductionTracking::create([
                'transaction_id' => $transaction->id,
                'transaction_item_id' => $item->id,
                'user_id' => $user->id,
                'type' => $type,
                'tracked_at' => now(),
                'notes' => $notes,
            ]);
        }

        $transaction->updateStatusFromItems();

        return back()->with('success', count($items) . ' item dari Order ' . $transaction->invoice_number . ' berhasil di Track Out.');
    }

    public function statusOrder(Request $request)
    {
        $query = Transaction::with([
            'customer', 
            'items', 
            'trackings.user',
            'checkedBy'
        ])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('monitoring.status', compact('transactions', 'customers'));
    }

    public function exportStatusOrder(Request $request)
    {
        $query = Transaction::with([
            'customer', 
            'items', 
            'trackings.user',
            'checkedBy'
        ])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->get();

        return Excel::download(new MonitoringStatusExport($transactions), 'monitoring_status_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function lookupTransaction(Request $request)
    {
        $request->validate(['barcode' => 'required|string', 'mode' => 'nullable|string|in:in,out']);
        
        $transaction = Transaction::with(['customer', 'items.product', 'items.trackings'])
            ->where('invoice_number', $request->barcode)
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $user = Auth::user();
        $isAdmin = $user->can('monitoring_admin_out');

        $itemsData = $transaction->items->map(function($item) use ($isAdmin, $request) {
            $trackings = collect($item->trackings);
            $hasDesignIn = $trackings->contains('type', ProductionTracking::TYPE_DESIGN_IN);
            $hasDesignOut = $trackings->contains('type', ProductionTracking::TYPE_DESIGN_OUT);
            $hasProdIn = $trackings->contains('type', ProductionTracking::TYPE_PRODUCTION_IN);
            $hasProdOut = $trackings->contains('type', ProductionTracking::TYPE_PRODUCTION_OUT);
            $hasAdminOut = $trackings->contains('type', ProductionTracking::TYPE_ADMIN_OUT);

            $statusLabel = $item->status;

            $canDesignIn = !$hasDesignIn && !$hasProdIn && !$hasAdminOut;
            $canDesignOut = $hasDesignIn && !$hasDesignOut && !$hasProdIn && !$hasAdminOut;
            $canProductionIn = !$hasProdIn && !$hasAdminOut;
            $canProductionOut = $hasProdIn && !$hasProdOut && !$hasAdminOut;
            $canAdminOut = $isAdmin && !$hasAdminOut;

            return [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? 'Produk',
                'custom_name' => $item->custom_name,
                'qty' => $item->quantity,
                'dimensions' => $item->dimensions,
                'status' => $statusLabel,
                'can_design_in' => $canDesignIn,
                'can_design_out' => $canDesignOut,
                'can_production_in' => $canProductionIn,
                'can_production_out' => $canProductionOut,
                'can_admin_out' => $canAdminOut,
            ];
        });

        return response()->json([
            'id' => $transaction->id,
            'invoice_number' => $transaction->invoice_number,
            'customer_name' => $transaction->customer ? $transaction->customer->name : 'Walk-in Customer',
            'status' => $transaction->order_status_label,
            'items' => $itemsData,
            'created_at' => $transaction->created_at->format('d M Y, H:i'),
        ]);
    }
}
