<?php

namespace App\Http\Controllers;

use App\Models\ProductionTracking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ]);

        $transaction = Transaction::where('invoice_number', $request->invoice_number)->firstOrFail();
        $user = Auth::user();

        $items = $transaction->items()->whereIn('id', $request->item_ids)->get();

        foreach ($items as $item) {
            if ($user->hasRole('designer')) {
                $item->status = 'designing';
                $type = ProductionTracking::TYPE_DESIGN_IN;
            } else {
                $item->status = 'production';
                $type = ProductionTracking::TYPE_PRODUCTION_IN;
            }
            $item->save();

            ProductionTracking::create([
                'transaction_id' => $transaction->id,
                'transaction_item_id' => $item->id,
                'user_id' => $user->id,
                'type' => $type,
                'tracked_at' => now(),
                'notes' => 'Track In by ' . $user->name,
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
            'pickup_method' => 'nullable|string|in:customer,kurir,diantar',
        ]);

        $transaction = Transaction::where('invoice_number', $request->invoice_number)->firstOrFail();
        $user = Auth::user();

        $items = $transaction->items()->whereIn('id', $request->item_ids)->get();

        foreach ($items as $item) {
            if ($user->hasRole('designer')) {
                $type = ProductionTracking::TYPE_DESIGN_OUT;
            } elseif ($user->hasRole('operator')) {
                $item->status = 'completed';
                $type = ProductionTracking::TYPE_PRODUCTION_OUT;
            } else {
                // Admin final track out
                $item->status = 'finished';
                $item->pickup_method = $request->pickup_method;
                $item->picked_up_at = now();
                $item->checked_by = $user->id;
                $type = ProductionTracking::TYPE_ADMIN_OUT;
            }
            $item->save();

            ProductionTracking::create([
                'transaction_id' => $transaction->id,
                'transaction_item_id' => $item->id,
                'user_id' => $user->id,
                'type' => $type,
                'tracked_at' => now(),
                'notes' => 'Track Out by ' . $user->name,
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
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $transactions = $query->paginate(20);
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('monitoring.status', compact('transactions', 'customers'));
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
        $isDesigner = $user->hasRole('designer');
        $isOperator = $user->hasRole('operator');
        $isAdmin = $user->can('monitoring_admin_out');

        $itemsData = $transaction->items->map(function($item) use ($isDesigner, $isOperator, $isAdmin, $request) {
            $trackings = collect($item->trackings);
            $hasDesignIn = $trackings->contains('type', ProductionTracking::TYPE_DESIGN_IN);
            $hasDesignOut = $trackings->contains('type', ProductionTracking::TYPE_DESIGN_OUT);
            $hasProdIn = $trackings->contains('type', ProductionTracking::TYPE_PRODUCTION_IN);
            $hasProdOut = $trackings->contains('type', ProductionTracking::TYPE_PRODUCTION_OUT);
            $hasAdminOut = $trackings->contains('type', ProductionTracking::TYPE_ADMIN_OUT);

            $canTrackIn = false;
            $canTrackOut = false;
            $statusLabel = $item->status;

            if ($isDesigner) {
                $canTrackIn = !$hasDesignIn && !$hasProdIn && !$hasAdminOut;
                $canTrackOut = $hasDesignIn && !$hasDesignOut && !$hasProdIn && !$hasAdminOut;
            } elseif ($isOperator) {
                $canTrackIn = !$hasProdIn && !$hasAdminOut;
                $canTrackOut = $hasProdIn && !$hasProdOut && !$hasAdminOut;
            } elseif ($isAdmin) {
                $canTrackOut = !$hasAdminOut; // admin final out
            }

            return [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? 'Produk',
                'custom_name' => $item->custom_name,
                'qty' => $item->quantity,
                'dimensions' => $item->dimensions,
                'status' => $statusLabel,
                'can_track_in' => $canTrackIn,
                'can_track_out' => $canTrackOut,
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
