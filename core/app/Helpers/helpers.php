<?php

use App\Models\AdminEmailTemplate;
use App\Models\EmailConfig;
use App\Models\UserEmailTemplate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;

function menuActive($route, $parameter = null)
{
    $class = 'active';

    if (is_array($route)) {
        foreach ($route as $r) {
            if (request()->routeIs($r)) {
                return $class;
            }
        }
    } elseif (request()->routeIs($route)) {
        if ($parameter !== null) {
            $currentParameter = request()->route()->parameter('status');
            if ($currentParameter !== $parameter) {
                return '';
            }
        }

        return $class;
    }

    return '';
}

function menuActivePrefix($prefix)
{
    if (request()->routeIs($prefix.'.*')) {
        return 'active';
    }

    return '';
}

function variableReplacer($search, $replace, $subject)
{
    return str_replace($search, $replace, $subject);
}

function sendAdminEmail($key, array $data, $admin = null)
{
    $settings = Setting::first();
    if (!$settings || $settings->admin_email_notification == 0) {
        Log::info('Admin email notification is disabled in settings');
        return false;
    }
    $template = AdminEmailTemplate::where('key', $key)->first();
    $config = EmailConfig::first();

    if (! $template || ! $config || ! $admin) {
        Log::error('Admin email sending failed: Missing template, config, or admin');

        return false;
    }

    // Replace variables in template
    $message = variableReplacer('{username}', $admin->username, $template->template);
    $message = variableReplacer('{email}', $admin->email, $message);

    foreach ($data as $key => $value) {
        $message = variableReplacer('{'.$key.'}', $value, $message);
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

        // Save processed message to use in closure
        $emailBody = $message;

        // Send email using Laravel's Mail facade
        Mail::html($emailBody, function ($mail) use ($admin, $template, $config, $fromName) {
            $mail->to($admin->email)
                 ->subject($template->subject)
                 ->from($config->sent_from, $fromName)
                 ->replyTo($config->sent_from);
        });

        return true;
    } catch (\Exception $e) {
        Log::error('Admin email sending failed: '.$e->getMessage());

        return false;
    }
}

function sendUserEmail($templateName, array $data, $user = null)
{
    $settings = Setting::first();
    if (!$settings || $settings->user_email_notification == 0) {
        Log::info('User email notification is disabled in settings');
        return false;
    }
    $template = UserEmailTemplate::where('name', $templateName)->first();
    $config = EmailConfig::first();

    if (! $template || ! $config || ! $user) {
        Log::error('User email sending failed: Missing template, config, or user');

        return false;
    }

    $message = variableReplacer('{username}', $user->username, $template->template);
    $message = variableReplacer('{email}', $user->email, $message);

    foreach ($data as $key => $value) {
        $message = variableReplacer('{'.$key.'}', $value, $message);
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

        $emailBody = $message;

        Mail::html($emailBody, function ($mail) use ($user, $template, $config, $fromName) {
            $mail->to($user->email)
                 ->subject($template->subject)
                 ->from($config->sent_from, $fromName)
                 ->replyTo($config->sent_from);
        });

        return true;
    } catch (\Exception $e) {
        Log::error('User email sending failed: '.$e->getMessage());

        return false;
    }
}