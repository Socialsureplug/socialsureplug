<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use App\Models\UserServiceDiscount;
use Illuminate\Support\Facades\Hash;
use App\Models\EmailConfig;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ManageUserController extends Controller
{
    public function index(Request $request)
    {
        $data['pageTitle'] = 'All Users';

        $query = User::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('fname', 'like', "%{$searchTerm}%")
                    ->orWhere('lname', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $data['users'] = $query->latest()->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.users.index', $data);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
            'status' => 'required|in:0,1',
        ]);
        $user = User::find($request->id);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }
        $user->status = $request->status;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:50',
            'lname' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);
        try {
            $user = User::create([
                'fname' => $request->fname,
                'lname' => $request->lname,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 1,
            ]);

            return redirect()->route('backend.users.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function userDetails($id)
    {
        $user = User::find($id);
        if (! $user) {
            return redirect()->route('backend.users.index')->with('error', 'User not found');
        }
        $data['pageTitle'] = 'User'.':'.' '.$user->fname.' '.$user->lname.' Details';
        $data['user'] = User::find($id);
        $data['settings'] = Setting::first();
        $data['discountServices'] = UserServiceDiscount::SERVICES;
        $data['discounts'] = UserServiceDiscount::where('user_id', $id)->pluck('discount_percent', 'service');

        return view('backend.users.details', $data);
    }

    public function updateDiscounts(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $request->validate([
            'discounts' => 'array',
            'discounts.*' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach (UserServiceDiscount::SERVICES as $service => $label) {
            $percent = (float) ($request->input("discounts.$service") ?: 0);

            UserServiceDiscount::updateOrCreate(
                ['user_id' => $user->id, 'service' => $service],
                ['discount_percent' => $percent]
            );
        }

        return redirect()->back()->with('success', 'Discounts updated successfully');
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $request->validate([
                'fname' => 'required|string|max:50',
                'lname' => 'required|string|max:50',
                'phone' => 'nullable|string|max:20',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
            ]);
            $user = User::find($id);

            if (! $user) {
                return redirect()->route('backend.users.index')->with('error', 'Error updating user');
            }

            $updateData = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'phone' => $request->phone,
                'email' => $request->email,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = $request->password;
            }

            $user->update($updateData);

            return redirect()->back()->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function addBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);
        $user = User::find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found');
        }
        $user->balance += $request->amount;
        $user->save();
        return redirect()->back()->with('success', 'Balance added successfully');
    }

    public function subtractBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);
        $user = User::find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found');
        }
        if ($user->balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient balance');
        }
        $user->balance -= $request->amount;
        $user->save();
        return redirect()->back()->with('success', 'Balance subtracted successfully');
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (! $user) {
            return redirect()->route('backend.users.index')->with('error', 'User not found');
        }
        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully');
    }

    public function userStatusFilter(Request $request, $status)
    {
        $data['pageTitle'] = ucwords($status) . ' Users';
        $query = User::query();

        if ($status === 'active') {
            $query->where('status', 1);
        } else {
            $query->where('status', 0);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('fname', 'like', "%{$searchTerm}%")
                    ->orWhere('lname', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $data['users'] = $query->latest()->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.users.index', $data);
    }

    public function sendEmail(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = User::find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $config = EmailConfig::first();
        if (! $config) {
            return redirect()->back()->with('error', 'Email configuration not found. Please configure email settings first.');
        }

        try {
            $fromName = optional(Setting::first())->website_name ?? 'Admin';
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $config->smtp_host);
            Config::set('mail.mailers.smtp.port', $config->smtp_port);
            Config::set('mail.mailers.smtp.encryption', $config->smtp_encryption);
            Config::set('mail.mailers.smtp.username', $config->smtp_username);
            Config::set('mail.mailers.smtp.password', $config->smtp_password);
            Config::set('mail.from.address', $config->sent_from);
            Config::set('mail.from.name', $fromName);

            // Replace basic variables in message
            $message = str_replace('{username}', $user->username ?? '', $request->message);
            $message = str_replace('{email}', $user->email, $message);
            $message = str_replace('{fname}', $user->fname ?? '', $message);
            $message = str_replace('{lname}', $user->lname ?? '', $message);

            // Send email
            Mail::html($message, function ($mail) use ($user, $request, $config, $fromName) {
                $mail->to($user->email)
                    ->subject($request->subject)
                    ->from($config->sent_from, $fromName)
                    ->replyTo($config->sent_from);
            });

            return redirect()->back()->with('success', 'Email sent successfully to ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Custom email sending failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function updateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = User::find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found');
        }

        try {
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($user->image && file_exists(base_path('../'.$user->image))) {
                    unlink(base_path('../'.$user->image));
                }

                $file = $request->file('image');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $destinationPath = base_path('../assets/backend/images/users');

                if (! file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $imagePath = 'assets/backend/images/users/'.$filename;

                // Update user image
                $user->image = $imagePath;
                $user->save();
            }

            return redirect()->back()->with('success', 'Profile image updated successfully');
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update image: ' . $e->getMessage());
        }
    }

    public function loginAsUser($id)
    {
        $user = User::findOrFail($id);

        // Check if user account is deactivated
        if ($user->status == 0) {
            return redirect()->back()->with('error', 'Cannot login as deactivated user: ' . $user->email);
        }

        Auth::guard('web')->loginUsingId($user->id);

        return redirect()->route('user.dashboard')->with('success', 'Logged in as ' . $user->fname . ' ' . $user->lname);
    }
}
