<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $title = 'Settings';
        $settings = Setting::first();
        
        return view('backend.settings', compact('title', 'settings'));
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'website_name' => 'nullable|string|max:255',
            'website_currency' => 'nullable|string|max:10',
            'currency_rate' => 'nullable|numeric|min:0',
            'price_increase' => 'nullable|numeric|min:0',
            'website_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string',
            'copyright_text' => 'nullable|string',
            'user_registration' => 'nullable|in:0,1',
            'recaptcha' => 'nullable|in:0,1',
            'user_email_notification' => 'nullable|in:0,1',
            'admin_email_notification' => 'nullable|in:0,1',
            'user_payment' => 'nullable|in:0,1',
            'user_login' => 'nullable|in:0,1',
            'email_verification' => 'nullable|in:0,1',
            'buy_number' => 'nullable|in:0,1',
            'buy_rent' => 'nullable|in:0,1',
            'boost_social' => 'nullable|in:0,1',
            'social_logs' => 'nullable|in:0,1',
            'referral_enabled' => 'nullable|in:0,1',
            'referral_commission_percent' => 'nullable|numeric|min:0|max:100',
            'referral_first_deposit_only' => 'nullable|in:0,1',
            'seo_description' => 'nullable|string',
            'live_chat_code' => 'nullable|string',
            'header_code' => 'nullable|string',
            'logo_white' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo_dark' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'favicon_white' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'favicon_dark' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $setting = Setting::firstOrCreate([], [
            'website_name' => config('app.name'),
            'website_currency' => 'NGN',
            'currency_rate' => 0,
            'price_increase' => 0,
            'website_color' => '#4c45e0',
            'timezone' => 'UTC',
        ]);

        $setting->fill($request->except(['logo_white', 'logo_dark', 'favicon_white', 'favicon_dark']));

        if ($request->hasFile('logo_white')) {
            if ($setting->logo_white && file_exists(base_path('../'.$setting->logo_white))) {
                unlink(base_path('../'.$setting->logo_white));
            }

            $file = $request->file('logo_white');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $destinationPath = base_path('../assets/backend/images/settings');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $setting->logo_white = 'assets/backend/images/settings/'.$filename;
        }

        if ($request->hasFile('logo_dark')) {
            if ($setting->logo_dark && file_exists(base_path('../'.$setting->logo_dark))) {
                unlink(base_path('../'.$setting->logo_dark));
            }

            $file = $request->file('logo_dark');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $destinationPath = base_path('../assets/backend/images/settings');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $setting->logo_dark = 'assets/backend/images/settings/'.$filename;
        }

        if ($request->hasFile('favicon_white')) {
            if ($setting->favicon_white && file_exists(base_path('../'.$setting->favicon_white))) {
                unlink(base_path('../'.$setting->favicon_white));
            }

            $file = $request->file('favicon_white');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $destinationPath = base_path('../assets/backend/images/settings');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $setting->favicon_white = 'assets/backend/images/settings/'.$filename;
        }

        if ($request->hasFile('favicon_dark')) {
            if ($setting->favicon_dark && file_exists(base_path('../'.$setting->favicon_dark))) {
                unlink(base_path('../'.$setting->favicon_dark));
            }

            $file = $request->file('favicon_dark');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $destinationPath = base_path('../assets/backend/images/settings');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $setting->favicon_dark = 'assets/backend/images/settings/'.$filename;
        }

        $setting->save();

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
