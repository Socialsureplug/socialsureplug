<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentGatewayController extends Controller
{
    public function paystack()
    {
        $title = 'Paystack';
        $gateway = Gateway::where('gateway_name', 'paystack')->firstOrNew([]);
        return view('backend.gateways.paystack', compact('title', 'gateway'));
    }

    public function paystackUpdate(Request $request)
    {
        $gateway = Gateway::where('gateway_name', 'paystack')->first();

        $request->validate([
            'gateway_currency' => 'required|string|max:10',
            'paystack_public_key' => 'required|string|max:255',
            'paystack_key' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'min_payment' => 'required|numeric|min:0',
            'max_payment' => 'nullable|numeric|gte:min_payment',
            'status' => 'required|in:0,1',
            'paystack_image' => [Rule::requiredIf(!$gateway), 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $params = [
            'gateway_currency' => $request->gateway_currency,
            'paystack_public_key' => $request->paystack_public_key,
            'paystack_key' => $request->paystack_key,
        ];

        $filename = $gateway->gateway_image ?? null;
        if ($request->hasFile('paystack_image')) {
            if ($gateway && $gateway->gateway_image && file_exists(base_path('../'.$gateway->gateway_image))) {
                unlink(base_path('../'.$gateway->gateway_image));
            }
            $file = $request->file('paystack_image');
            $filename = 'assets/backend/images/gateways/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/gateways');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($filename));
        }

        Gateway::updateOrCreate(
            ['gateway_name' => 'paystack'],
            [
                'gateway_image' => $filename ?? $gateway->gateway_image ?? null,
                'gateway_parameters' => $params,
                'gateway_type' => 1,
                'rate' => $request->rate,
                'min_payment' => $request->min_payment,
                'max_payment' => $request->max_payment,
                'status' => (int) $request->status,
            ]
        );

        return redirect()->back()->with('success', 'Paystack settings updated successfully');
    }

    public function flutterwave()
    {
        $title = 'Flutterwave';
        $gateway = Gateway::where('gateway_name', 'flutterwave')->firstOrNew([]);
        return view('backend.gateways.flutterwave', compact('title', 'gateway'));
    }

    public function flutterwaveUpdate(Request $request)
    {
        $gateway = Gateway::where('gateway_name', 'flutterwave')->first();

        $request->validate([
            'gateway_currency' => 'required|string|max:10',
            'public_key' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'min_payment' => 'required|numeric|min:0',
            'max_payment' => 'nullable|numeric|gte:min_payment',
            'status' => 'required|in:0,1',
            'flutterwave_image' => [Rule::requiredIf(!$gateway), 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $params = [
            'gateway_currency' => $request->gateway_currency,
            'public_key' => $request->public_key,
            'secret_key' => $request->secret_key,
        ];

        $filename = $gateway->gateway_image ?? null;
        if ($request->hasFile('flutterwave_image')) {
            if ($gateway && $gateway->gateway_image && file_exists(base_path('../'.$gateway->gateway_image))) {
                unlink(base_path('../'.$gateway->gateway_image));
            }
            $file = $request->file('flutterwave_image');
            $filename = 'assets/backend/images/gateways/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/gateways');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($filename));
        }

        Gateway::updateOrCreate(
            ['gateway_name' => 'flutterwave'],
            [
                'gateway_image' => $filename ?? $gateway->gateway_image ?? null,
                'gateway_parameters' => $params,
                'gateway_type' => 1,
                'rate' => $request->rate,
                'min_payment' => $request->min_payment,
                'max_payment' => $request->max_payment,
                'status' => (int) $request->status,
            ]
        );

        return redirect()->back()->with('success', 'Flutterwave settings updated successfully');
    }

    public function korapay()
    {
        $title = 'KoraPay';
        $gateway = Gateway::where('gateway_name', 'korapay')->firstOrNew([]);

        return view('backend.gateways.korapay', compact('title', 'gateway'));
    }

    public function korapayUpdate(Request $request)
    {
        $gateway = Gateway::where('gateway_name', 'korapay')->first();

        $request->validate([
            'gateway_currency' => 'required|string|max:10',
            'public_key' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
            'webhook_secret' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0',
            'min_payment' => 'required|numeric|min:0',
            'max_payment' => 'nullable|numeric|gte:min_payment',
            'status' => 'required|in:0,1',
            'korapay_image' => [Rule::requiredIf(! $gateway), 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $params = [
            'gateway_currency' => $request->gateway_currency,
            'public_key' => $request->public_key,
            'secret_key' => $request->secret_key,
            'webhook_secret' => $request->webhook_secret,
        ];

        $filename = $gateway->gateway_image ?? null;
        if ($request->hasFile('korapay_image')) {
            if ($gateway && $gateway->gateway_image && file_exists(base_path('../'.$gateway->gateway_image))) {
                unlink(base_path('../'.$gateway->gateway_image));
            }
            $file = $request->file('korapay_image');
            $filename = 'assets/backend/images/gateways/'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/gateways');
            if (! file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($filename));
        }

        Gateway::updateOrCreate(
            ['gateway_name' => 'korapay'],
            [
                'gateway_image' => $filename ?? $gateway->gateway_image ?? null,
                'gateway_parameters' => $params,
                'gateway_type' => 1,
                'rate' => $request->rate,
                'min_payment' => $request->min_payment,
                'max_payment' => $request->max_payment,
                'status' => (int) $request->status,
            ]
        );

        return redirect()->back()->with('success', 'KoraPay settings updated successfully');
    }

    public function bachs()
    {
        $title = 'Bachs';
        $gateway = Gateway::where('gateway_name', 'bachs')->firstOrNew([]);

        return view('backend.gateways.bachs', compact('title', 'gateway'));
    }

    public function bachsUpdate(Request $request)
    {
        $gateway = Gateway::where('gateway_name', 'bachs')->first();

        $request->validate([
            'gateway_currency' => 'required|string|max:10',
            'api_key' => ['required', 'string', 'max:255', 'regex:/^sk_(sandbox|live)_/'],
            'webhook_secret' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'min_payment' => 'required|numeric|min:0',
            'max_payment' => 'nullable|numeric|gte:min_payment',
            'status' => 'required|in:0,1',
            'bachs_image' => [Rule::requiredIf(! $gateway), 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'api_key.regex' => 'The API key must start with sk_sandbox_ or sk_live_.',
        ]);

        $params = [
            'gateway_currency' => $request->gateway_currency,
            'api_key' => $request->api_key,
            'webhook_secret' => $request->webhook_secret,
        ];

        $filename = $gateway->gateway_image ?? null;
        if ($request->hasFile('bachs_image')) {
            if ($gateway && $gateway->gateway_image && file_exists(base_path('../'.$gateway->gateway_image))) {
                unlink(base_path('../'.$gateway->gateway_image));
            }
            $file = $request->file('bachs_image');
            $filename = 'assets/backend/images/gateways/'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/gateways');
            if (! file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($filename));
        }

        Gateway::updateOrCreate(
            ['gateway_name' => 'bachs'],
            [
                'gateway_image' => $filename ?? $gateway->gateway_image ?? null,
                'gateway_parameters' => $params,
                'gateway_type' => 1,
                'rate' => $request->rate,
                'min_payment' => $request->min_payment,
                'max_payment' => $request->max_payment,
                'status' => (int) $request->status,
            ]
        );

        return redirect()->back()->with('success', 'Bachs settings updated successfully');
    }

    public function bank()
    {
        $title = 'Bank Transfer';
        $gateway = Gateway::where('gateway_name', 'bank')->firstOrNew([]);
        return view('backend.gateways.bank', compact('title', 'gateway'));
    }

    public function bankUpdate(Request $request)
    {
        $gateway = Gateway::where('gateway_name', 'bank')->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'routing_number' => 'required|string|max:100',
            'branch_name' => 'required|string|max:255',
            'gateway_currency' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0',
            'charge' => 'required|numeric|min:0',
            'min_payment' => 'required|numeric|min:0',
            'max_payment' => 'nullable|numeric|gte:min_payment',
            'status' => 'required|in:0,1',
            'bank_image' => [Rule::requiredIf(!$gateway), 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $params = [
            'name' => $request->name,
            'account_number' => $request->account_number,
            'routing_number' => $request->routing_number,
            'branch_name' => $request->branch_name,
            'gateway_currency' => $request->gateway_currency,
        ];

        $filename = $gateway->gateway_image ?? null;
        if ($request->hasFile('bank_image')) {
            if ($gateway && $gateway->gateway_image && file_exists(base_path('../'.$gateway->gateway_image))) {
                unlink(base_path('../'.$gateway->gateway_image));
            }
            $file = $request->file('bank_image');
            $filename = 'assets/backend/images/gateways/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/gateways');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($filename));
        }

        Gateway::updateOrCreate(
            ['gateway_name' => 'bank'],
            [
                'gateway_image' => $filename ?? $gateway->gateway_image ?? null,
                'gateway_parameters' => $params,
                'gateway_type' => 0,
                'rate' => $request->rate,
                'charge' => $request->charge,
                'min_payment' => $request->min_payment,
                'max_payment' => $request->max_payment,
                'status' => (int) $request->status,
            ]
        );

        return redirect()->back()->with('success', 'Bank transfer settings updated successfully');
    }
}