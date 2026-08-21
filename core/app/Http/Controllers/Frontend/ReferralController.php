<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        /** @var User $user */
        $data['title'] = 'Referral';

        $query = ReferralCommission::with('referred:id,fname,lname,email')
            ->where('referrer_user_id', $user->id)
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%")
                    ->orWhereHas('referred', function ($uq) use ($term) {
                        $uq->where('fname', 'like', "%{$term}%")
                            ->orWhere('lname', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        $data['commissions'] = $query->paginate(15)->withQueryString();
        $data['search'] = $request->get('search', '');
        $data['totalCommission'] = ReferralCommission::where('referrer_user_id', $user->id)
            ->where('status', 'paid')
            ->sum('commission_amount');

        $data['referralUrl'] = route('user.register', $user->id);

        return view('frontend.user.referral.index', $data);
    }
}
