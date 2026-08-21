<?php

namespace Database\Seeders;

use App\Models\UserEmailTemplate;
use Illuminate\Database\Seeder;

class UserEmailTemplateSeeder extends Seeder
{
    public function run()
    {
        $meaning = [
            'username' => 'Username',
            'email' => 'Email',
            'fname' => 'First name',
            'lname' => 'Last name',
        ];
        UserEmailTemplate::updateOrCreate(
            ['name' => 'Welcome Email'],
            [
                'subject' => 'Welcome to our platform',
                'template' => '<p><b>Hi {fname} {lname},</b></p><p>Welcome! Your account has been created successfully. You can now log in with your email and password.</p><p>Thanks,</p><p>Team</p>',
                'meaning' => $meaning,
                'status' => 1,
            ]
        );

        $paymentMeaning = [
            'username' => 'Username',
            'email' => 'Email',
            'amount' => 'Payment amount',
            'currency' => 'Currency code',
            'reference' => 'Payment reference',
            'method' => 'Payment method',
        ];
        UserEmailTemplate::updateOrCreate(
            ['name' => 'Payment Success'],
            [
                'subject' => 'Payment received',
                'template' => '<p><b>Hi {username},</b></p><p>Your payment of {currency} {amount} has been received successfully.</p><p>Reference: {reference}<br>Method: {method}</p><p>Thanks,</p><p>Team</p>',
                'meaning' => $paymentMeaning,
                'status' => 1,
            ]
        );

        $numberMeaning = [
            'username' => 'Username',
            'email' => 'Email',
            'number' => 'Purchased number',
            'service_name' => 'Service name',
            'amount' => 'Amount paid',
        ];
        UserEmailTemplate::updateOrCreate(
            ['name' => 'Number Purchased'],
            [
                'subject' => 'Number purchased successfully',
                'template' => '<p><b>Hi {username},</b></p><p>You have successfully purchased a number.</p><p>Number: {number}<br>Service: {service_name}<br>Amount: {amount}</p><p>Thanks,</p><p>Team</p>',
                'meaning' => $numberMeaning,
                'status' => 1,
            ]
        );

        $paymentSubmittedMeaning = [
            'username' => 'Username',
            'email' => 'Email',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'reference' => 'Reference',
        ];
        UserEmailTemplate::updateOrCreate(
            ['name' => 'Payment Submitted'],
            [
                'subject' => 'Payment proof submitted',
                'template' => '<p><b>Hi {username},</b></p><p>Your payment proof for {currency} {amount} (Reference: {reference}) has been submitted. We will notify you once it is approved.</p><p>Thanks,</p><p>Team</p>',
                'meaning' => $paymentSubmittedMeaning,
                'status' => 1,
            ]
        );

        $paymentApprovedMeaning = [
            'username' => 'Username',
            'email' => 'Email',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'reference' => 'Reference',
        ];
        UserEmailTemplate::updateOrCreate(
            ['name' => 'Payment Approved'],
            [
                'subject' => 'Payment approved',
                'template' => '<p><b>Hi {username},</b></p><p>Your bank payment of {currency} {amount} (Reference: {reference}) has been approved. The amount has been added to your wallet.</p><p>Thanks,</p><p>Team</p>',
                'meaning' => $paymentApprovedMeaning,
                'status' => 1,
            ]
        );

        $passwordResetMeaning = [
            'fname' => 'First name',
            'reset_code' => '6-digit reset code',
            'expires_in' => 'Code validity (e.g. 1 hour)',
        ];
        UserEmailTemplate::updateOrCreate(
            ['name' => 'Password Reset'],
            [
                'subject' => 'Password reset code',
                'template' => '<p><b>Hi {fname},</b></p><p>You requested a password reset. Use the code below to set a new password. This code expires in {expires_in}.</p><p><strong>Reset code: {reset_code}</strong></p><p>If you did not request this, please ignore this email.</p><p>Thanks,</p><p>Team</p>',
                'meaning' => $passwordResetMeaning,
                'status' => 1,
            ]
        );
    }
}