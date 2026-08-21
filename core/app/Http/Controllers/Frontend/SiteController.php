<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function homePage()
    {
        $siteName = optional(Setting::first())->website_name ?? config('app.name');
        $data['title'] = 'Home';
        $data['description'] = 'Virtual numbers and OTP verification — receive SMS and verification codes without using your personal phone number. ' . $siteName . ' offers numbers in 50+ countries.';

        return view('frontend.pages.home', $data);
    }

    public function terms()
    {
        $siteName = optional(Setting::first())->website_name ?? config('app.name');
        $data['title'] = 'Terms of Service';
        $data['description'] = 'Read our Terms of Service for virtual numbers and OTP verification. Rules and guidelines for using ' . $siteName . '.';
        return view('frontend.pages.terms', $data);
    }

    public function privacy()
    {
        $siteName = optional(Setting::first())->website_name ?? config('app.name');
        $data['title'] = 'Privacy Policy';
        $data['description'] = 'Privacy Policy for ' . $siteName . ' — how we collect, use, and protect your information when you use our virtual number and OTP verification service.';
        return view('frontend.pages.privacy', $data);
    }

    public function support()
    {
        $siteName = optional(Setting::first())->website_name ?? config('app.name');
        $data['title'] = 'Support & Help';
        $data['description'] = 'Get help with ' . $siteName . ' virtual numbers and OTP verification. FAQ, contact support, and how to get started.';
        return view('frontend.pages.support', $data);
    }
}
