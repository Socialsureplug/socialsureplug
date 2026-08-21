<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminEmailTemplate;
use App\Models\EmailConfig;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserEmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function config()
    {
        $data['title'] = 'Email Config';
        $data['emailConfig'] = EmailConfig::firstOrNew([]);

        return view('backend.email.config', $data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|string|max:255',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'required|string|max:255',
            'sent_from' => 'required|string|max:255',
            'smtp_encryption' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $emailConfig = EmailConfig::first();
        if ($emailConfig) {
            $emailConfig->update($request->all());
        } else {
            EmailConfig::create($request->all());
        }

        return redirect()->route('backend.email.config')->with('success', 'Email config updated successfully');
    }

    public function adminTemplate()
    {
        $data['pageTitle'] = 'Admin Email Template';
        $data['adminTemplates'] = AdminEmailTemplate::all();

        return view('backend.email.admin-template', $data);
    }

    public function adminTemplateDetails($id)
    {
        $data['pageTitle'] = 'Admin Email Template Details';
        $data['adminTemplate'] = AdminEmailTemplate::find($id);

        return view('backend.email.details-admin', $data);
    }

    public function adminTemplateUpdate(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'template' => 'required|string',
        ]);

        $adminTemplate = AdminEmailTemplate::find($id);
        $adminTemplate->update($request->all());

        return redirect()->route('backend.email.admin-template')->with('success', 'Admin email template updated successfully');
    }

    public function userTemplate()
    {
        $data['pageTitle'] = 'User Email Template';
        $data['userTemplates'] = UserEmailTemplate::all();

        return view('backend.email.user-template', $data);
    }

    public function userTemplateDetails($id)
    {
        $data['pageTitle'] = 'User Email Template Details';
        $data['userTemplate'] = UserEmailTemplate::find($id);

        return view('backend.email.details-user', $data);
    }

    public function userTemplateUpdate(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'template' => 'required|string',
        ]);

        $userTemplate = UserEmailTemplate::find($id);
        $userTemplate->update($request->all());

        return redirect()->route('backend.email.user-template')->with('success', 'User email template updated successfully');
    }

    public function sendForm()
    {
        $data['pageTitle'] = 'Send Email to Users';
        $data['users'] = User::whereNotNull('email')->orderBy('fname')->get();

        return view('backend.email.send', $data);
    }

    public function sendSubmit(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipients' => 'required|in:all,selected',
        ]);

        if ($request->recipients === 'selected') {
            $request->validate(['user_ids' => 'required|array', 'user_ids.*' => 'integer|exists:users,id']);
            $users = User::whereIn('id', $request->user_ids)->whereNotNull('email')->get();
        } else {
            $users = User::whereNotNull('email')->get();
        }

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'No users to send email to.');
        }

        $config = EmailConfig::first();
        if (! $config) {
            return redirect()->back()->with('error', 'Email configuration not found. Please configure email settings first.');
        }

        $fromName = optional(Setting::first())->website_name ?? 'Admin';

        try {
            $encryption = $config->smtp_encryption;
            if ($encryption === 0 || $encryption === '0') {
                $encryption = 'tls';
            } elseif ($encryption === 1 || $encryption === '1') {
                $encryption = 'ssl';
            } elseif ($encryption === 2 || $encryption === '2') {
                $encryption = null;
            }

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $config->smtp_host);
            Config::set('mail.mailers.smtp.port', $config->smtp_port);
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Config::set('mail.mailers.smtp.username', $config->smtp_username);
            Config::set('mail.mailers.smtp.password', $config->smtp_password);
            Config::set('mail.from.address', $config->sent_from);
            Config::set('mail.from.name', $fromName);

            $sent = 0;
            foreach ($users as $user) {
                $username = trim($user->fname . ' ' . $user->lname) ?: $user->email;
                $message = str_replace('{username}', $username, $request->message);
                $message = str_replace('{email}', $user->email, $message);
                $message = str_replace('{fname}', $user->fname ?? '', $message);
                $message = str_replace('{lname}', $user->lname ?? '', $message);

                Mail::html($message, function ($mail) use ($user, $request, $config, $fromName) {
                    $mail->to($user->email)
                        ->subject($request->subject)
                        ->from($config->sent_from, $fromName)
                        ->replyTo($config->sent_from);
                });
                $sent++;
            }

            return redirect()->route('backend.email.send')->with('success', "Email sent successfully to {$sent} user(s).");
        } catch (\Exception $e) {
            Log::error('Bulk email sending failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
