<?php

namespace Database\Seeders;

use App\Models\AdminEmailTemplate;
use Illuminate\Database\Seeder;

class AdminEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meaningReset = [
            'username' => 'Admin Username',
            'email' => 'Admin Email',
            'reset_code' => '6-digit reset code',
            'expires_in' => 'Code validity (e.g. 1 hour)',
        ];
        AdminEmailTemplate::updateOrCreate(
            ['key' => 'admin_password_reset'],
            [
                'name' => 'Admin Password Reset',
                'subject' => 'Password reset code',
                'template' => '<p><b>Hi {username},</b></p>
                                <p>Your password reset code is: <strong>{reset_code}</strong></p>
                                <p>This code expires in {expires_in}. If you did not request this, please ignore this email.</p>
                                <p>Thanks,</p>
                                <p>Admin</p>',
                'meaning' => $meaningReset,
                'status' => 1,
            ]
        );

        $adminLoginMeaning = [
            'username' => 'Admin Username',
            'email' => 'Admin Email',
            'login_time' => 'Login date/time',
            'ip_address' => 'IP address',
        ];
        AdminEmailTemplate::updateOrCreate(
            ['key' => 'admin_login'],
            [
                'name' => 'Admin Login',
                'subject' => 'Admin login notification',
                'template' => '<p><b>Hi {username},</b></p><p>Your admin account was used to log in.</p><p>Login time: {login_time}<br>IP: {ip_address}</p><p>If this was not you, please change your password.</p><p>Thanks,</p><p>System</p>',
                'meaning' => $adminLoginMeaning,
                'status' => 1,
            ]
        );

        $userPaymentMeaning = [
            'username' => 'Admin Username',
            'email' => 'Admin Email',
            'user_email' => 'User email',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'reference' => 'Reference',
            'method' => 'Payment method',
        ];
        AdminEmailTemplate::updateOrCreate(
            ['key' => 'user_payment'],
            [
                'name' => 'User Payment',
                'subject' => 'New user payment',
                'template' => '<p><b>Hi {username},</b></p><p>A user has made a payment.</p><p>User: {user_email}<br>Amount: {currency} {amount}<br>Method: {method}<br>Reference: {reference}</p><p>Thanks,</p><p>System</p>',
                'meaning' => $userPaymentMeaning,
                'status' => 1,
            ]
        );

        $userNumberMeaning = [
            'username' => 'Admin Username',
            'email' => 'Admin Email',
            'user_email' => 'User email',
            'number' => 'Purchased number',
            'service_name' => 'Service name',
            'amount' => 'Amount',
        ];
        AdminEmailTemplate::updateOrCreate(
            ['key' => 'user_number_purchase'],
            [
                'name' => 'User Number Purchase',
                'subject' => 'User purchased a number',
                'template' => '<p><b>Hi {username},</b></p><p>A user has purchased a number.</p><p>User: {user_email}<br>Number: {number}<br>Service: {service_name}<br>Amount: {amount}</p><p>Thanks,</p><p>System</p>',
                'meaning' => $userNumberMeaning,
                'status' => 1,
            ]
        );
    }
}