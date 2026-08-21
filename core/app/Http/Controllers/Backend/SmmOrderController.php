<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmmOrder;
use App\Models\User;
use Illuminate\Http\Request;

class SmmOrderController extends Controller
{
    public function index(Request $request)
    {
        $title = 'SMM Orders';
        $orders = SmmOrder::with(['user', 'smmService', 'smmCategory'])
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->when($request->q, fn($q) => $q->where('id', $request->q)->orWhere('link', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(20)->withQueryString();
        $search = $request->q ?? '';
        $status = $request->status ?? 'all';
        return view('backend.smm.orders.index', compact('title', 'orders', 'search', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:smm_orders,id',
            'link' => 'nullable|string',
            'start_counter' => 'nullable|integer|min:0',
            'remains' => 'nullable|integer|min:0',
            'status' => 'required|in:awaiting,inprogress,completed,partial,canceled,error,fail',
        ]);
        $order = SmmOrder::findOrFail($request->id);
        $newStatus = $request->status;
        $order->update([
            'link' => $request->link ?? $order->link,
            'start_counter' => $request->start_counter,
            'remains' => $request->remains,
            'status' => $newStatus,
        ]);
        $refunded = $order->fresh()->refundIfEligible($newStatus);

        return redirect()->back()->with(
            'success',
            $refunded ? 'Order updated and user refunded.' : 'Order updated.'
        );
    }

    public function resend($id)
    {
        $order = SmmOrder::findOrFail($id);
        if ($order->refunded) {
            return redirect()->back()->with('error', 'Refunded orders cannot be resent.');
        }
        if (!in_array($order->status, ['error', 'fail'])) {
            return redirect()->back()->with('error', 'Only failed/error orders can be resent.');
        }
        $order->update(['status' => 'awaiting', 'api_order_id' => null]);
        // Optionally dispatch job to re-send to provider.
        return redirect()->back()->with('success', 'Order queued for resend.');
    }
}