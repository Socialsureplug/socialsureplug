<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function profilePage()
    {
        $data['title'] = 'Profile';
        $data['user'] = Auth::user()->fresh();

        return view('frontend.user.profile', $data);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'fname' => 'nullable|string|max:255',
            'lname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'two_factor_enabled' => 'nullable|boolean',
            'current_password' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $twoFactorEnabled = (bool) $request->boolean('two_factor_enabled');
            $twoFactorChanged = $twoFactorEnabled !== (bool) $user->two_factor_enabled;

            if ($twoFactorChanged && ! Hash::check((string) $request->current_password, $user->password)) {
                $message = 'Current password is required to change two-factor authentication.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }

                return redirect()->back()->with('error', $message)->withInput();
            }

            $updateData = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'email' => $request->email,
                'two_factor_enabled' => $twoFactorEnabled,
            ];

            if (! $twoFactorEnabled) {
                $updateData['two_factor_code'] = null;
                $updateData['two_factor_expires_at'] = null;
            }

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'user' => $user->fresh()
                ]);
            }

            return redirect()->route('user.profile')
                ->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to update profile');
        }
    }

    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = Auth::user();

        try {
            if ($request->hasFile('image')) {
                $uploadPath = base_path('../assets/frontend/images/users');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                if ($user->image && file_exists(base_path('../'.$user->image))) {
                    @unlink(base_path('../'.$user->image));
                }

                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $imagePath = 'assets/frontend/images/users/' . $filename;

                $user->update(['image' => $imagePath]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Profile image updated successfully',
                        'image_url' => asset($imagePath),
                        'user' => $user->fresh()
                    ]);
                }
                return redirect()->back()->with('success', 'Profile image updated successfully');
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No image file provided'], 400);
            }
            return redirect()->back()->with('error', 'No image file provided');
        } catch (\Exception $e) {
            Log::error('Profile image upload failed: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update image',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to update image.');
        }
    }
}
