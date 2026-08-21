<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use Illuminate\Http\Request;

class PayController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Fund Account';

        $paystack    = Gateway::where('gateway_name', 'paystack')->where('status', 1)->first();
        $flutterwave = Gateway::where('gateway_name', 'flutterwave')->where('status', 1)->first();
        $korapay     = Gateway::where('gateway_name', 'korapay')->where('status', 1)->first();
        $bachs       = Gateway::where('gateway_name', 'bachs')->where('status', 1)->first();
        $bank        = Gateway::where('gateway_name', 'bank')->where('status', 1)->first();

        $data['paystackPublicKey']    = $paystack ? ($paystack->gateway_parameters->paystack_public_key ?? null) : null;
        $data['paystackCurrency']     = $paystack ? ($paystack->gateway_parameters->gateway_currency ?? 'NGN') : 'NGN';
        $data['paystackEnabled']      = (bool) $paystack;

        $data['flutterwavePublicKey'] = $flutterwave ? ($flutterwave->gateway_parameters->public_key ?? null) : null;
        $data['flutterwaveCurrency']  = $flutterwave ? ($flutterwave->gateway_parameters->gateway_currency ?? 'NGN') : 'NGN';
        $data['flutterwaveEnabled']   = (bool) $flutterwave;

        $data['korapayCurrency'] = $korapay ? ($korapay->gateway_parameters->gateway_currency ?? 'NGN') : 'NGN';
        $data['korapayEnabled']  = (bool) $korapay;

        $data['bachsCurrency'] = $bachs ? ($bachs->gateway_parameters->gateway_currency ?? 'NGN') : 'NGN';
        $data['bachsEnabled']  = (bool) $bachs;

        $data['bankEnabled']          = (bool) $bank;
        $data['bankCurrency']         = $bank ? ($bank->gateway_parameters->gateway_currency ?? 'NGN') : 'NGN';
        $data['gatewayLimits'] = [
            'paystack' => [
                'min' => $paystack ? (float) $paystack->min_payment : 0.0,
                'max' => ($paystack && $paystack->max_payment !== null) ? (float) $paystack->max_payment : null,
            ],
            'flutterwave' => [
                'min' => $flutterwave ? (float) $flutterwave->min_payment : 0.0,
                'max' => ($flutterwave && $flutterwave->max_payment !== null) ? (float) $flutterwave->max_payment : null,
            ],
            'korapay' => [
                'min' => $korapay ? (float) $korapay->min_payment : 0.0,
                'max' => ($korapay && $korapay->max_payment !== null) ? (float) $korapay->max_payment : null,
            ],
            'bachs' => [
                'min' => $bachs ? (float) $bachs->min_payment : 0.0,
                'max' => ($bachs && $bachs->max_payment !== null) ? (float) $bachs->max_payment : null,
            ],
            'bank' => [
                'min' => $bank ? (float) $bank->min_payment : 0.0,
                'max' => ($bank && $bank->max_payment !== null) ? (float) $bank->max_payment : null,
            ],
        ];

        return view('frontend.user.payment.index', $data);
    }
}
