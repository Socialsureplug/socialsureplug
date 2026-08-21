<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function transactionLog(Request $request)
    {
        $title = 'Transaction Log';

        $query = Transaction::with('user')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('amount', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('email', 'like', "%{$term}%")
                            ->orWhere('fname', 'like', "%{$term}%")
                            ->orWhere('lname', 'like', "%{$term}%");
                    });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();
        $search = $request->search ?? '';

        return view('backend.logs.transactions', compact('title', 'transactions', 'search'));
    }
}
