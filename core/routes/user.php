<?php

use App\Http\Controllers\Frontend\ApiController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\DashController;
use App\Http\Controllers\Frontend\LogController;
use App\Http\Controllers\Frontend\NumberController;
use App\Http\Controllers\Frontend\RentingController;
use App\Http\Controllers\Frontend\PayController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\ReferralController;
use App\Http\Controllers\Frontend\SellingController;
use App\Http\Controllers\Frontend\SmmOrderController;
use App\Http\Controllers\Frontend\WalletPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/register/{referrer_id?}', [AuthController::class, 'registerPage'])->name('register')->middleware('user.registration.check');
Route::post('/register/{referrer_id?}', [AuthController::class, 'register'])->name('register.submit')->middleware('user.registration.check');
Route::get('/login', [AuthController::class, 'loginPage'])->name('login.page');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('user.login.check');
Route::get('/refresh-captcha', [AuthController::class, 'refreshCaptcha'])->name('refresh-captcha');

Route::get('/forgot-password', [AuthController::class, 'forgotPasswordPage'])->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');
Route::get('/reset-password', [AuthController::class, 'resetPasswordPage'])->name('reset-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.submit');
Route::get('/verify-email', [AuthController::class, 'emailVerify'])->name('verify-email');
Route::post('/verify-email', [AuthController::class, 'emailVerifyConfirm'])->name('verify-email.confirm');
Route::post('/verify-email/resend', [AuthController::class, 'resendVerificationCode'])->name('verify-email.resend');
Route::get('/two-factor', [AuthController::class, 'twoFactorPage'])->name('two-factor');
Route::post('/two-factor', [AuthController::class, 'twoFactorConfirm'])->name('two-factor.confirm')->middleware('throttle:6,1');
Route::post('/two-factor/resend', [AuthController::class, 'resendTwoFactorCode'])->name('two-factor.resend')->middleware('throttle:3,1');

Route::post('/payment/korapay/webhook', [WalletPaymentController::class, 'korapayWebhook'])->name('payment.korapay.webhook');
Route::post('/payment/bachs/webhook', [WalletPaymentController::class, 'bachsWebhook'])->name('payment.bachs.webhook');

Route::middleware(['auth', 'user.status.check', 'verified.email'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'profilePage'])->name('profile');
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.update-image');

    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/', [PayController::class, 'index'])->name('index')->middleware('user.payment.check');

        // New: Paystack & Flutterwave callbacks
        Route::get('/paystack/callback', [WalletPaymentController::class, 'paystackCallback'])
            ->name('paystack.callback');

        Route::get('/flutterwave/callback', [WalletPaymentController::class, 'flutterwaveCallback'])
            ->name('flutterwave.callback');

        Route::post('/korapay/initiate', [WalletPaymentController::class, 'korapayInitiate'])
            ->name('korapay.initiate')
            ->middleware('user.payment.check');

        Route::get('/korapay/callback', [WalletPaymentController::class, 'korapayCallback'])
            ->name('korapay.callback');

        Route::post('/bachs/initiate', [WalletPaymentController::class, 'bachsInitiate'])
            ->name('bachs.initiate')
            ->middleware('user.payment.check');

        Route::get('/bachs/callback', [WalletPaymentController::class, 'bachsCallback'])
            ->name('bachs.callback');

        // New: Bank transfer
        Route::post('/bank/initiate', [WalletPaymentController::class, 'initiateBankTransfer'])
            ->name('bank.initiate')
            ->middleware('user.payment.check');
        Route::post('/bank/upload-proof', [WalletPaymentController::class, 'uploadBankProof'])
            ->name('bank.upload-proof')
            ->middleware('user.payment.check');
    });

    Route::prefix('referral')->name('referral.')->middleware('ensure.referral')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
    });

    Route::prefix('log')->name('log.')->group(function () {
        Route::get('/transaction', [LogController::class, 'transactionLog'])->name('transaction');
        Route::get('/payments', [LogController::class, 'paymentLog'])->name('payments');
        Route::get('/smm-orders', [LogController::class, 'smmOrderLog'])->name('smm-orders')->middleware('ensure.boost_social');
        Route::get('/selling-orders', [LogController::class, 'sellingOrderLog'])->name('selling-orders')->middleware('ensure.social_logs');
        Route::get('/selling-orders/{order}/accounts', [LogController::class, 'sellingOrderAccounts'])->name('selling-orders.accounts')->middleware('ensure.social_logs');
    });

    Route::prefix('selling')->name('selling.')->middleware('ensure.social_logs')->group(function () {
        Route::get('/', [SellingController::class, 'index'])->name('index');
        Route::get('/product/{id}', [SellingController::class, 'show'])->name('product');
        Route::post('/purchase', [SellingController::class, 'purchase'])->name('purchase');
    });

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/', [ApiController::class, 'index'])->name('index')->middleware('ensure.buy_number');
        Route::get('/smm-docs', [ApiController::class, 'smmDocs'])->name('smm-docs')->middleware('ensure.boost_social');
        Route::post('/regenerate-key', [ApiController::class, 'regenerateKey'])->name('regenerate-key');
    });

    Route::prefix('number')->name('number.')->middleware('ensure.buy_number')->group(function () {
        Route::get('/', [NumberController::class, 'index'])->name('index');
        Route::get('orders', [NumberController::class, 'orders'])->name('orders');
        Route::get('countries', [NumberController::class, 'getCountries'])->name('countries');
        Route::get('services', [NumberController::class, 'getServices'])->name('services');
        Route::post('buy', [NumberController::class, 'buy'])->name('buy');
        Route::get('active-orders', [NumberController::class, 'activeOrders'])->name('active-orders');
        Route::get('message', [NumberController::class, 'pollMessage'])->name('message');
        Route::post('cancel', [NumberController::class, 'cancel'])->name('cancel');
        Route::post('dismiss', [NumberController::class, 'dismiss'])->name('dismiss');
    });

    Route::prefix('renting')->name('renting.')->middleware('ensure.buy_rent')->group(function () {
        Route::get('/', [RentingController::class, 'index'])->name('index');
        Route::get('orders', [RentingController::class, 'orders'])->name('orders');
        Route::get('countries', [RentingController::class, 'getCountries'])->name('countries');
        Route::get('durations', [RentingController::class, 'getDurations'])->name('durations');
        Route::get('services', [RentingController::class, 'getServices'])->name('services');
        Route::get('operators', [RentingController::class, 'getOperators'])->name('operators');
        Route::post('rent', [RentingController::class, 'rent'])->name('rent');
        Route::get('active-orders', [RentingController::class, 'activeOrders'])->name('active-orders');
        Route::get('poll-sms', [RentingController::class, 'pollSms'])->name('poll-sms');
        Route::post('prolong', [RentingController::class, 'prolong'])->name('prolong');
        Route::post('restore-precalc', [RentingController::class, 'restorePrecalc'])->name('restore-precalc');
        Route::post('restore', [RentingController::class, 'restore'])->name('restore');
    });

    // SMM orders (Boost Socials)
    Route::prefix('smm-order')->name('smm-order.')->middleware('ensure.boost_social')->group(function () {
        Route::get('/', [SmmOrderController::class, 'newOrder'])->name('index');
        Route::post('/store', [SmmOrderController::class, 'store'])->name('store');
        Route::post('/mass-order', [SmmOrderController::class, 'massOrder'])->name('mass-order');
    });
});
