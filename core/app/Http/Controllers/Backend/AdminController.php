<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function profile()
    {
        $admin = Auth::guard('admin')->user();

        return view('backend.admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:admins,username,' . $admin->id],
            'email' => ['required', 'email', 'max:100', 'unique:admins,email,' . $admin->id],
            'password' => ['nullable', 'string', 'min:6'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $admin->name = $validated['name'] ?? $admin->name;
        $admin->username = $validated['username'];
        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = $validated['password'];
        }

        if ($request->hasFile('image')) {
            if ($admin->image && file_exists(base_path('../'.$admin->image))) {
                unlink(base_path('../'.$admin->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = base_path('../assets/backend/images/users');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $admin->image = 'assets/backend/images/users/' . $filename;
        }

        $admin->save();

        return back()->with('success', 'Profile updated successfully');
    }
}
