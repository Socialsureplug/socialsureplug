<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SmmOrder;
use App\Models\SellingOrder;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Http\Request;
class LogController extends Controller
{
    public function transactionLog(Request $request)
    {
        $data['title'] = 'Transaction Log';
        $query = Transaction::where('user_id', auth()->id())->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('type', 'like', "%{$term}%")
                    ->orWhere('source', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%");
            });
        }

        $data['transactions'] = $query->paginate(15)->withQueryString();
        $data['search'] = $request->get('search', '');

        return view('frontend.user.log.transaction', $data);
    }

    public function paymentLog(Request $request)
    {
        $data['title'] = 'Payment Log';
        $query = Payment::where('user_id', auth()->id())->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('method', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%")
                    ->orWhere('currency', 'like', "%{$term}%");
            });
        }

        $data['payments'] = $query->paginate(15)->withQueryString();
        $data['search'] = $request->get('search', '');

        return view('frontend.user.log.payments', $data);
    }

    public function smmOrderLog(Request $request)
    {
        $data['title'] = 'Order Log';
        $query = SmmOrder::where('user_id', auth()->id())
            ->with(['smmService', 'smmCategory'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('link', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%")
                    ->orWhere('id', 'like', "%{$term}%")
                    ->orWhereHas('smmService', fn ($s) => $s->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('smmCategory', fn ($c) => $c->where('name', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $data['orders'] = $query->paginate(15)->withQueryString();
        $data['search'] = $request->get('search', '');
        $data['statusFilter'] = $request->get('status', 'all');

        return view('frontend.user.log.smm-orders', $data);
    }

    public function sellingOrderLog(Request $request)
    {
        $data['title'] = 'Selling orders';
        $query = SellingOrder::query()
            ->where('user_id', auth()->id())
            ->with(['sellingProduct'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', '%'.$term.'%')
                    ->orWhereHas('sellingProduct', fn ($p) => $p->where('title', 'like', '%'.$term.'%'));
            });
        }

        $data['orders'] = $query->paginate(15)->withQueryString();
        $data['search'] = $request->get('search', '');
        $data['currency'] = optional(Setting::first())->website_currency ?? 'NGN';

        return view('frontend.user.log.selling-order', $data);
    }

    public function sellingOrderAccounts(int $order)
    {
        $orderModel = SellingOrder::query()
            ->where('user_id', auth()->id())
            ->with([
                'sellingProduct',
                'sellingAccounts' => fn ($q) => $q->orderBy('id'),
            ])
            ->findOrFail($order);

        $data['title'] = 'Order #'.$orderModel->id.' — Accounts';
        $data['order'] = $orderModel;
        $data['currency'] = optional(Setting::first())->website_currency ?? 'NGN';

        return view('frontend.user.log.selling-accounts', $data);
    }
}
