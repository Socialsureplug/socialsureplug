<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $apiKey = $user->ensureApiKey();

        $data['title'] = 'API Tool';
        $data['apiKey'] = $apiKey;
        $data['baseUrl'] = config('app.url');

        return view('frontend.user.api.index', $data);
    }

    public function regenerateKey(Request $request)
    {
        $user = $request->user();

        $user->api_key = bin2hex(random_bytes(20));
        $user->save();

        return response()->json([
            'status' => '200',
            'api_key' => $user->api_key,
            'message' => 'API key regenerated successfully.',
        ]);
    }

    public function smmDocs()
    {
        $user = auth()->user();
        $apiKey = $user->ensureApiKey();
        $data['title'] = 'SMM API Documentation';
        $data['apiKey'] = $apiKey;
        $data['baseUrl'] = rtrim(config('app.url'), '/');
        $data['smmApiUrl'] = $data['baseUrl'] . '/api/smm/v1';
        return view('frontend.user.api.smm-docs', $data);
    }
}