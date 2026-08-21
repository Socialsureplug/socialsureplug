<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashController;
use App\Http\Controllers\Backend\EmailController;
use App\Http\Controllers\Backend\LogController as BackendLogController;
use App\Http\Controllers\Backend\ManageUserController;
use App\Http\Controllers\Backend\NumberController;
use App\Http\Controllers\Backend\RentingController;
use App\Http\Controllers\Backend\PaymentGatewayController;
use App\Http\Controllers\Backend\PaymentLogController;
use App\Http\Controllers\Backend\SellingController;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\SmmCategoryController;
use App\Http\Controllers\Backend\SmmOrderController as BackendSmmOrderController;
use App\Http\Controllers\Backend\SmmProviderController;
use App\Http\Controllers\Backend\SmmServiceController;
use App\Http\Controllers\Backend\SmmSubcategoryController;
use App\Http\Controllers\Backend\TutorialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:5,1');

Route::get('/forgot-password', [AuthController::class, 'forgotPasswordPage'])->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');
Route::get('/reset-password', [AuthController::class, 'resetPasswordPage'])->name('reset-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.submit');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [DashController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/filter', [DashController::class, 'filter'])->name('dashboard.filter');
    Route::get('/dashboard/export/csv', [DashController::class, 'exportCsv'])->name('dashboard.export.csv');
    Route::get('/dashboard/export/excel', [DashController::class, 'exportExcel'])->name('dashboard.export.excel');
    Route::get('/dashboard/export/pdf', [DashController::class, 'exportPdf'])->name('dashboard.export.pdf');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AdminController::class, 'profile'])->name('index');
        Route::put('/update', [AdminController::class, 'updateProfile'])->name('update');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/update', [SettingsController::class, 'updateSetting'])->name('update-setting');
    });

    // Email
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('/config', [EmailController::class, 'config'])->name('config');
        Route::put('/config/update', [EmailController::class, 'update'])->name('config.update');
        Route::get('/send', [EmailController::class, 'sendForm'])->name('send');
        Route::post('/send', [EmailController::class, 'sendSubmit'])->name('send.submit');
        Route::get('/admin-template', [EmailController::class, 'adminTemplate'])->name('admin-template');
        Route::get('/admin-template/details/{id}', [EmailController::class, 'adminTemplateDetails'])->name('admin-template.details');
        Route::put('/admin-template/update/{id}', [EmailController::class, 'adminTemplateUpdate'])->name('admin-template.update');
        Route::get('/user-template', [EmailController::class, 'userTemplate'])->name('user-template');
        Route::get('/user-template/details/{id}', [EmailController::class, 'userTemplateDetails'])->name('user-template.details');
        Route::put('/user-template/update/{id}', [EmailController::class, 'userTemplateUpdate'])->name('user-template.update');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [ManageUserController::class, 'index'])->name('index');
        Route::post('/toggle-status', [ManageUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/add-user', [ManageUserController::class, 'addUser'])->name('add-user');
        Route::get('/details/{id}', [ManageUserController::class, 'userDetails'])->name('details');
        Route::put('/update-user/{id}', [ManageUserController::class, 'updateUser'])->name('update-user');
        Route::post('/add-balance/{id}', [ManageUserController::class, 'addBalance'])->name('add-balance');
        Route::post('/subtract-balance/{id}', [ManageUserController::class, 'subtractBalance'])->name('subtract-balance');
        Route::post('/send-email/{id}', [ManageUserController::class, 'sendEmail'])->name('send-email');
        Route::put('/update-image/{id}', [ManageUserController::class, 'updateImage'])->name('update-image');
        Route::put('/update-discounts/{id}', [ManageUserController::class, 'updateDiscounts'])->name('update-discounts');
        Route::get('/status-filter/{status}', [ManageUserController::class, 'userStatusFilter'])->name('status-filter');
        Route::get('/login-as-user/{id}', [ManageUserController::class, 'loginAsUser'])->name('login-as-user');
        Route::delete('/{id}', [ManageUserController::class, 'destroy'])->name('destroy');
    });

    // Tutorials & Guides
    Route::prefix('tutorials')->name('tutorials.')->group(function () {
        Route::get('/', [TutorialController::class, 'index'])->name('index');
        Route::post('/', [TutorialController::class, 'store'])->name('store');
        Route::put('/{id}', [TutorialController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [TutorialController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [TutorialController::class, 'destroy'])->name('destroy');
    });

    // Payment gateways
    Route::prefix('payment-gateway')->name('payment-gateway.')->group(function () {
        Route::get('/paystack', [PaymentGatewayController::class, 'paystack'])->name('paystack');
        Route::put('/paystack', [PaymentGatewayController::class, 'paystackUpdate'])->name('paystack.update');
        Route::get('/flutterwave', [PaymentGatewayController::class, 'flutterwave'])->name('flutterwave');
        Route::put('/flutterwave', [PaymentGatewayController::class, 'flutterwaveUpdate'])->name('flutterwave.update');
        Route::get('/korapay', [PaymentGatewayController::class, 'korapay'])->name('korapay');
        Route::put('/korapay', [PaymentGatewayController::class, 'korapayUpdate'])->name('korapay.update');
        Route::get('/bachs', [PaymentGatewayController::class, 'bachs'])->name('bachs');
        Route::put('/bachs', [PaymentGatewayController::class, 'bachsUpdate'])->name('bachs.update');
        Route::get('/bank', [PaymentGatewayController::class, 'bank'])->name('bank');
        Route::put('/bank', [PaymentGatewayController::class, 'bankUpdate'])->name('bank.update');
    });

    Route::prefix('numbers')->name('numbers.')->group(function () {
        Route::get('/api', [NumberController::class, 'api'])->name('api');
        Route::post('/api', [NumberController::class, 'storeApi'])->name('api.store');
        Route::put('/api/{id}', [NumberController::class, 'updateApi'])->name('api.update');
        Route::delete('/api/{id}', [NumberController::class, 'destroyApi'])->name('api.destroy');

        Route::get('/api/{apiId}/countries', [NumberController::class, 'countries'])->name('api.countries');
        Route::post('/api/{apiId}/countries', [NumberController::class, 'storeCountry'])->name('api.countries.store');
        Route::put('/countries/{id}', [NumberController::class, 'updateCountry'])->name('countries.update');
        Route::delete('/countries/{id}', [NumberController::class, 'destroyCountry'])->name('countries.destroy');
        Route::post('/api/{apiId}/countries/import', [NumberController::class, 'importCountries'])->name('api.countries.import');
        Route::post('/api/{apiId}/services/import', [NumberController::class, 'importServices'])->name('api.services.import');

        Route::get('/api/{apiId}/services', [NumberController::class, 'services'])->name('api.services');
        Route::post('/api/{apiId}/services', [NumberController::class, 'storeService'])->name('api.services.store');
        Route::put('/services/{id}', [NumberController::class, 'updateService'])->name('services.update');
        Route::delete('/services/{id}', [NumberController::class, 'destroyService'])->name('services.destroy');

        Route::get('/settings', [NumberController::class, 'settings'])->name('settings');
        Route::put('/settings', [NumberController::class, 'updateSettings'])->name('settings.update');
        Route::get('/orders', [NumberController::class, 'orders'])->name('orders')->middleware('ensure.buy_number');

        Route::get('/sync-runs/{id}', [NumberController::class, 'syncRunStatus'])->name('sync-runs.show');
        Route::post('/api/balances', [NumberController::class, 'fetchBalances'])->name('api.balances');
        Route::post('/api/toggle-status', [NumberController::class, 'toggleApiStatus'])->name('api.toggle-status');
        Route::post('/countries/toggle-status', [NumberController::class, 'toggleCountryStatus'])->name('countries.toggle-status');
        Route::post('/services/toggle-status', [NumberController::class, 'toggleServiceStatus'])->name('services.toggle-status');
    });

    Route::prefix('rentings')->name('rentings.')->group(function () {
        Route::get('/api', [RentingController::class, 'api'])->name('api');
        Route::post('/api', [RentingController::class, 'storeApi'])->name('api.store');
        Route::put('/api/{id}', [RentingController::class, 'updateApi'])->name('api.update');
        Route::delete('/api/{id}', [RentingController::class, 'destroyApi'])->name('api.destroy');

        Route::get('/api/{apiId}/countries', [RentingController::class, 'countries'])->name('api.countries');
        Route::post('/api/{apiId}/countries', [RentingController::class, 'storeCountry'])->name('api.countries.store');
        Route::put('/countries/{id}', [RentingController::class, 'updateCountry'])->name('countries.update');
        Route::delete('/countries/{id}', [RentingController::class, 'destroyCountry'])->name('countries.destroy');
        Route::get('/api/{apiId}/countries/{countryId}/operators', [RentingController::class, 'operators'])->name('api.countries.operators');
        Route::put('/operators/{id}', [RentingController::class, 'updateOperator'])->name('operators.update');
        Route::delete('/operators/{id}', [RentingController::class, 'destroyOperator'])->name('operators.destroy');
        Route::post('/api/{apiId}/countries/import', [RentingController::class, 'importCountries'])->name('api.countries.import');
        Route::post('/api/{apiId}/services/import', [RentingController::class, 'importServices'])->name('api.services.import');

        Route::get('/api/{apiId}/services', [RentingController::class, 'services'])->name('api.services');
        Route::post('/api/{apiId}/services', [RentingController::class, 'storeService'])->name('api.services.store');
        Route::put('/services/{id}', [RentingController::class, 'updateService'])->name('services.update');
        Route::delete('/services/{id}', [RentingController::class, 'destroyService'])->name('services.destroy');
        Route::get('/services/{serviceId}/service-operators', [RentingController::class, 'serviceOperators'])->name('services.service-operators');
        Route::put('/service-operators/{id}', [RentingController::class, 'updateServiceOperator'])->name('service-operators.update');
        Route::delete('/service-operators/{id}', [RentingController::class, 'destroyServiceOperator'])->name('service-operators.destroy');

        Route::get('/settings', [RentingController::class, 'settings'])->name('settings');
        Route::put('/settings', [RentingController::class, 'updateSettings'])->name('settings.update');
        Route::get('/orders', [RentingController::class, 'orders'])->name('orders');

        Route::get('/sync-runs/{id}', [RentingController::class, 'syncRunStatus'])->name('sync-runs.show');
        Route::post('/api/balances', [RentingController::class, 'fetchBalances'])->name('api.balances');
        Route::post('/api/toggle-status', [RentingController::class, 'toggleApiStatus'])->name('api.toggle-status');
        Route::post('/countries/toggle-status', [RentingController::class, 'toggleCountryStatus'])->name('countries.toggle-status');
        Route::post('/operators/toggle-status', [RentingController::class, 'toggleOperatorStatus'])->name('operators.toggle-status');
        Route::post('/services/toggle-status', [RentingController::class, 'toggleServiceStatus'])->name('services.toggle-status');
        Route::post('/service-operators/toggle-status', [RentingController::class, 'toggleServiceOperatorStatus'])->name('service-operators.toggle-status');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentLogController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentLogController::class, 'show'])->name('show');

        Route::post('/{payment}/approve-bank', [PaymentLogController::class, 'approveBank'])
            ->name('approve-bank');
        Route::post('/{payment}/reject-bank', [PaymentLogController::class, 'rejectBank'])
            ->name('reject-bank');
    });

    // Logs (transactions, numbers)
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/transactions', [BackendLogController::class, 'transactionLog'])->name('transactions');
    });

    // SMM
    Route::prefix('smm-providers')->name('smm-providers.')->middleware('ensure.boost_social')->group(function () {
        Route::get('/', [SmmProviderController::class, 'index'])->name('index');
        Route::post('/store', [SmmProviderController::class, 'store'])->name('store');
        Route::post('/balance/{id}', [SmmProviderController::class, 'balance'])->name('balance');
        Route::get('/{id}/services', [SmmProviderController::class, 'services'])->name('services');
        Route::post('/sync-services/{id}', [SmmProviderController::class, 'syncServices'])->name('sync-services');
        Route::post('/import-services', [SmmProviderController::class, 'importServices'])->name('import-services');
        Route::delete('/{id}', [SmmProviderController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('smm-categories')->name('smm-categories.')->middleware('ensure.boost_social')->group(function () {
        Route::get('/', [SmmCategoryController::class, 'index'])->name('index');
        Route::post('/', [SmmCategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [SmmCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [SmmCategoryController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('smm-subcategories')->name('smm-subcategories.')->middleware('ensure.boost_social')->group(function () {
        Route::get('/', [SmmSubcategoryController::class, 'index'])->name('index');
        Route::post('/', [SmmSubcategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [SmmSubcategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [SmmSubcategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('smm-services')->name('smm-services.')->middleware('ensure.boost_social')->group(function () {
        Route::get('/', [SmmServiceController::class, 'index'])->name('index');
        Route::post('/', [SmmServiceController::class, 'store'])->name('store');
        Route::put('/{id}', [SmmServiceController::class, 'update'])->name('update');
        Route::delete('/{id}', [SmmServiceController::class, 'destroy'])->name('destroy');
        Route::post('/sync-provider-prices', [SmmServiceController::class, 'syncProviderPrices'])->name('sync-provider-prices');
        Route::post('/sync-total-prices', [SmmServiceController::class, 'syncTotalPrices'])->name('sync-total-prices');
    });
    Route::prefix('smm-orders')->name('smm-orders.')->middleware('ensure.boost_social')->group(function () {
        Route::get('/', [BackendSmmOrderController::class, 'index'])->name('index');
        Route::post('/store', [BackendSmmOrderController::class, 'store'])->name('store');
        Route::post('/resend/{id}', [BackendSmmOrderController::class, 'resend'])->name('resend');
    });

    Route::prefix('selling')->name('selling.')->group(function () {
        Route::get('/categories', [SellingController::class, 'categories'])->name('categories');
        Route::post('/categories/import', [SellingController::class, 'importCategories'])->name('categories.import');
        Route::post('/categories', [SellingController::class, 'categoriesStore'])->name('categories.store');
        Route::put('/categories/{id}', [SellingController::class, 'categoriesUpdate'])->name('categories.update');
        Route::delete('/categories/{id}', [SellingController::class, 'categoriesDestroy'])->name('categories.destroy');

        Route::get('/subcategories', [SellingController::class, 'subcategories'])->name('subcategories');
        Route::post('/subcategories/import', [SellingController::class, 'importSubcategories'])->name('subcategories.import');
        Route::post('/subcategories', [SellingController::class, 'subcategoriesStore'])->name('subcategories.store');
        Route::put('/subcategories/{id}', [SellingController::class, 'subcategoriesUpdate'])->name('subcategories.update');
        Route::delete('/subcategories/{id}', [SellingController::class, 'subcategoriesDestroy'])->name('subcategories.destroy');

        Route::get('/api', [SellingController::class, 'api'])->name('api');
        Route::post('/api/toggle-status', [SellingController::class, 'toggleStatus'])->name('api.toggle-status');
        Route::post('/api', [SellingController::class, 'store'])->name('api.store');
        Route::put('/api/{id}', [SellingController::class, 'update'])->name('api.update');

        Route::get('/orders', [SellingController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}/accounts', [SellingController::class, 'orderAccounts'])->name('orders.accounts');

        Route::get('/products', [SellingController::class, 'products'])->name('products');
        Route::get('/products/create', [SellingController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [SellingController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/import', [SellingController::class, 'importProducts'])->name('products.import');
        Route::post('/products/sync-descriptions', [SellingController::class, 'syncDescriptions'])->name('products.sync-descriptions');
        Route::get('/products/sync-descriptions/{syncId}/status', [SellingController::class, 'syncDescriptionsStatus'])->name('products.sync-descriptions.status');
        Route::get('/products/{id}/edit', [SellingController::class, 'editProduct'])->name('products.edit');
        Route::get('/products/{productId}/accounts', [SellingController::class, 'productAccounts'])->name('products.accounts');
        Route::post('/products/{productId}/accounts', [SellingController::class, 'storeSellingAccount'])->name('products.accounts.store');
        Route::put('/products/{id}', [SellingController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [SellingController::class, 'destroyProduct'])->name('products.destroy');

        // accounts
        Route::post('/products/{productId}/accounts/import', [SellingController::class, 'importSellingAccounts'])
            ->name('products.accounts.import');
        Route::put('/products/{productId}/accounts/{accountId}', [SellingController::class, 'updateSellingAccount'])
            ->name('products.accounts.update');
        Route::delete('/products/{productId}/accounts/{accountId}', [SellingController::class, 'destroySellingAccount'])
            ->name('products.accounts.destroy');
    });
});
