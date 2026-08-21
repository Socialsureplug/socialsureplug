<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NumberApi;
use App\Models\NumberOrder;
use App\Models\NumberService;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SmmOrder;
use Illuminate\Support\Facades\Auth;

class DashController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $walletBalance     = (float) ($user->balance ?? 0);
        $numbersPurchased  = NumberOrder::where('user_id', $user->id)->count();
        $totalFunded       = Payment::where('user_id', $user->id)->where('status', 'success')->sum('amount');
        $smsServerStatus   = NumberApi::where('status', 1)->exists() ? 'Active' : 'Offline';
        $currency          = optional(Setting::first())->website_currency ?? 'NGN';

        $smmOrdersCount   = SmmOrder::where('user_id', $user->id)->count();
        $smmTotalSpent    = (float) SmmOrder::where('user_id', $user->id)->sum('charge');

        $topServices = NumberService::where('status', 1)
            ->orderBy('name')
            ->take(5)
            ->get()
            ->map(fn ($service) => [
                'name'   => $service->name,
                'detail' => $service->code,
                'icon'   => 'ti ti-device-mobile',
            ]);

        $displayName = trim(($user->fname ?? '').' '.($user->lname ?? ''));
        if ($displayName === '') {
            $displayName = $user->email ?? __('there');
        }

        return view('frontend.user.dashboard', [
            'title'            => 'Dashboard',
            'displayName'      => $displayName,
            'walletBalance'    => $walletBalance,
            'numbersPurchased' => $numbersPurchased,
            'totalFunded'      => $totalFunded,
            'smsServerStatus'  => $smsServerStatus,
            'currency'         => $currency,
            'smmOrdersCount'   => $smmOrdersCount,
            'smmTotalSpent'    => $smmTotalSpent,
            'topServices'      => $topServices,
        ]);
    }
}
