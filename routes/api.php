<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DictionaryController;
use App\Http\Controllers\Api\ExchangeController;
use App\Http\Controllers\Api\HeartbeatController;
use App\Http\Controllers\Api\HotWalletController;
use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProcessingController;
use App\Http\Controllers\Api\Root\SettingController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WithdrawalRuleController;
use App\Http\Controllers\Api\WithdrawalWalletController;
use App\Http\Controllers\PayerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('users')->name('users.')->group(function () {
    Route::post('register', [UserController::class, 'register'])->middleware('guest')->name('register');

    Route::prefix('password')->name('password.')->middleware('guest')->group(function () {
        Route::post('reset', [UserController::class, 'resetPassword'])->name('reset');
        Route::put('set', [UserController::class, 'setPassword'])->name('set');
    });

    Route::get('user', [UserController::class, 'detail'])->middleware(['auth:sanctum']);
    Route::post('user/update', [UserController::class, 'update'])->middleware('auth:sanctum');

    Route::get('user/rate-source', [UserController::class, 'getRateSource'])->middleware('auth:sanctum');
    Route::post('user/rate-source', [UserController::class, 'updateRateSource'])->middleware('auth:sanctum');
    Route::put('user/change-password', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('user/verify-email', [UserController::class, 'activate'])->name('verify-email');
    Route::post('user/resend-email-confirmation',
        [UserController::class, 'resendEmail'])->middleware('auth:sanctum')->name('resend-email-email');

    Route::post('user/2fa', [UserController::class, 'toggle2fa'])->middleware('auth:sanctum');
    Route::post('user/2fa/validate', [UserController::class, 'verify2fa'])->middleware('auth:sanctum');
});

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [UserController::class, 'login'])->middleware('guest')->name('login');
    Route::post('logout', [UserController::class, 'logout'])->middleware(['auth:sanctum'])->name('logout');
});

Route::prefix('stores')->name('stores.')->middleware(['auth:sanctum', 'role:root|admin|support'])->group(function () {
    Route::post('/', [StoreController::class, 'create']);
    Route::get('/', [StoreController::class, 'list']);

    Route::put('/status', [StoreController::class, 'changeStatus']);

    Route::post('{store}/static-addresses', [StoreController::class, 'changeStaticAddress']);

    Route::get('{store}/urls', [StoreController::class, 'getUrls']);
    Route::post('{store}/urls', [StoreController::class, 'setUrls']);
    Route::put('{store}/general', [StoreController::class, 'update']);
    Route::put('{store}/rate-sources', [StoreController::class, 'rateSource']);
    Route::get('{store}', [StoreController::class, 'getStore']);


    Route::post('{store}/webhooks', [WebhookController::class, 'create']);
    Route::get('{store}/webhooks', [WebhookController::class, 'list']);
    Route::put('{store}/webhooks/{webhook}', [WebhookController::class, 'update']);
    Route::delete('{store}/webhooks/{webhook}', [WebhookController::class, 'delete']);
    Route::post('{store}/webhooks/{webhook}/test', [WebhookController::class, 'test']);

    Route::post('{store}/api-keys', [ApiKeyController::class, 'create']);
    Route::get('{store}/api-keys', [ApiKeyController::class, 'list']);
    Route::put('{store}/api-keys/{apiKey}', [ApiKeyController::class, 'update']);
    Route::delete('{store}/api-keys/{apiKey}', [ApiKeyController::class, 'delete']);

    Route::prefix('wallets')->group(function () {
        Route::get('list', [WalletController::class, 'list']);
        Route::post('withdrawals/transfer', [WalletController::class, 'transfer']);
        Route::get('withdrawals/list', [WalletController::class, 'withdrawalList']);
        Route::get('withdrawals/stats', [WalletController::class, 'withdrawalStats']);
        Route::get('withdrawals/dates', [WalletController::class, 'withdrawalDates']);
    });

    Route::post('invoices/create', [InvoiceController::class, 'createWithAuthKey']);
    Route::post('invoices/addresses', [InvoiceController::class, 'invoiceAddressList']);
    Route::get('invoices/{invoice}', [InvoiceController::class, 'detailWithAuthKey']);
    Route::post('invoices', [InvoiceController::class, 'list']);

    Route::post('addresses/invoices', [InvoiceController::class, 'invoiceAddressDetail']);

    Route::post('dashboard/deposit/summary', [DashboardController::class, 'getDepositSummary']);
    Route::post('dashboard/deposit/transactions', [DashboardController::class, 'getDepositTransactions']);

    Route::get('processing/wallets', [ProcessingController::class, 'getProcessingWallets']);
    Route::post('processing/switch-type', [ProcessingController::class, 'updateProcessingTransferType']);
    Route::get('processing/wallets/{blockchain}', [ProcessingController::class, 'getProcessingWalletInfo']);
    Route::get('processing/wallets/{blockchain}/stats', [ProcessingController::class, 'getProcessingWalletStats']);
    Route::get('processing/wallets/{blockchain}/transfers', [ProcessingController::class, 'getProcessingWalletTransfers']);

    Route::get('currencies/rates', [CurrencyController::class, 'getAllRates']);

    Route::get('heartbeat/status', [HeartbeatController::class, 'getStatusForDashboard']);
    Route::get('heartbeat/status/all', [HeartbeatController::class, 'getAllService']);
    Route::get('heartbeat/status/history', [HeartbeatController::class, 'getServiceLaunch']);
    Route::get('heartbeat/status/financial-stats', [HeartbeatController::class, 'getFinancialStatsForDashboard']);
    Route::get('heartbeat/monitor', [HeartbeatController::class, 'getResources']);

    Route::get('heartbeat/service', [HeartbeatController::class, 'getAllService']);
    Route::get('heartbeat/{service}/launch', [HeartbeatController::class, 'getServiceLaunch']);

    Route::post('balances', [BalanceController::class, 'getAllBalances']);
});


Route::prefix('/withdrawal')->name('withdrawal.')->middleware(['auth:sanctum', 'role:root|admin'])->group(function () {
    Route::get('rules', [WithdrawalRuleController::class, 'index']);
    Route::post('rules', [WithdrawalRuleController::class, 'store']);
});

Route::prefix('stores')->name('stores.')->group(function () {
    Route::post('/{store}/currencies/rate', [StoreController::class, 'rate']);
});

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::post('/', [InvoiceController::class, 'createWithApiKey'])->middleware('auth:store-api-key');
    Route::get('{invoice}', [InvoiceController::class, 'detail']);
    Route::get('{invoice}/confirm', [InvoiceController::class, 'invoiceConfirm']);
    Route::put('{invoice}', [InvoiceController::class, 'saveEmail']);
});
Route::prefix('/payer')->name('.payer')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [PayerController::class, 'index'])->name('.index');
    Route::post('/', [PayerController::class, 'store'])->name('.store');
    Route::get('/{payer}/invoices', [PayerController::class, 'invoices']);
});

Route::prefix('/payer')->name('.payer')->group(function () {
    Route::get('/{payer}/', [PayerController::class, 'show'])->name('.show');
    Route::get('/{payer}/addresses/{currency}', [PayerController::class, 'payerAddress'])->name('.payerAddress');
});

Route::middleware('auth:store-api-key')->group(function () {
    Route::get('/balances', [StoreController::class, 'balancesProcessingWallets']);
    Route::get('/withdrawals/unconfirmed', [StoreController::class, 'unconfirmedWithdrawals']);
    Route::post('/withdrawals/withdrawal-from-processing-wallet', [StoreController::class, 'withdrawalFromProcessingWallet']);
    Route::post('/payer/create', [PayerController::class, 'createWithApikey']);
    Route::get('/address/{payer}/{currency}', [AddressController::class, 'getAddress']);
    Route::post('/payer/addresses', [AddressController::class, 'getAddresses']);
});

Route::prefix('telegram')->group(function () {
    Route::post('start', [TelegramController::class, 'start'])->middleware(['auth:sanctum', 'role:admin|user']);
    Route::post('notification', [TelegramController::class, 'notification'])->middleware([
        'auth:sanctum', 'role:admin|user'
    ]);
    Route::post('command', [TelegramController::class, 'command']);
    Route::get('processing', [TelegramController::class, 'processing'])->middleware(['auth:sanctum', 'role:admin|user']);;
});

Route::get('dictionaries', [DictionaryController::class, 'dictionaries'])->middleware(['auth:sanctum']);

Route::post('/processing/callback', [ProcessingController::class, 'callback'])
    ->name('processing.callback')
    ->middleware('check.sign');

Route::post('/processing/callback-transfer', [ProcessingController::class, 'transferCallback'])->middleware('check.sign');

Route::prefix('support')->name('support.')->middleware(['auth:sanctum', 'role:admin|support'])->group(function () {
    Route::get('transactions/{txId}', [SupportController::class, 'getTransactionInfo']);
    Route::post('transactions/{txId}/invoices/{invoice}/force', [SupportController::class, 'forceAttachTransactionToInvoice']);
    Route::post('transactions/{txId}/payer/', [SupportController::class, 'attachTransactionToPayer']);
    Route::post('invoices/{invoice}/webhook', [WebhookController::class, 'sendWebhook']);
});

Route::prefix('exchanges')->middleware(['auth:sanctum'])->group(function () {
    Route::post('keys', [ExchangeController::class, 'addKeys']);
    Route::get('keys', [ExchangeController::class, 'getKeys']);
    Route::post('test', [ExchangeController::class, 'testConnection']);
    Route::get('deposit/address', [ExchangeController::class, 'depositAddresses']);
    Route::get('withdrawal/address', [ExchangeController::class, 'withdrawalAddresses']);
    Route::get('symbols', [ExchangeController::class, 'symbols']);
    Route::get('symbols-exchange', [ExchangeController::class, 'symbolFromExchange']);
    Route::post('user-pairs', [ExchangeController::class, 'saveExchangeUserPairs']);
    Route::put('user-pairs', [ExchangeController::class, 'saveUserPairs']);
    Route::get('user-pairs', [ExchangeController::class, 'getExchangeUserPairs']);
    Route::put('{wallet}/settings', [ExchangeController::class, 'updateExchangeWalletSetting']);
    Route::get('withdrawal/cold-wallets', [ExchangeController::class, 'getColdWallets']);
    Route::post('withdrawal/cold-wallets', [ExchangeController::class, 'storeColdWallets']);
    Route::put('withdrawal/status', [ExchangeController::class, 'updateStatus']);
    Route::post('withdrawal/exchange-status', [ExchangeController::class, 'updateExchangeStatus']);
    Route::get('withdrawal/history', [ExchangeController::class, 'withdrawalHistory']);
    Route::delete('withdrawal/{coldWallet}/cold-wallets', [ExchangeController::class, 'deleteColdWallets']);
});


Route::prefix('invite')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [InviteController::class, 'index']);
    Route::post('send', [InviteController::class, 'invite']);
    Route::post('accept', [InviteController::class, 'accept'])->withoutMiddleware(['auth:sanctum']);
    Route::post('/{invite}', [InviteController::class, 'update']);
    Route::get('/{invite}', [InviteController::class, 'show']);
});

Route::prefix('notifications')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/', NotificationController::class);
    Route::get('/list', [NotificationController::class, 'list']);
    Route::get('/targets', [NotificationController::class, 'targets']);
    Route::get('/targets/list', [NotificationController::class, 'targetsList']);
    Route::post('/targets', [NotificationController::class, 'storeTargets']);
});

Route::prefix('withdrawal-wallet')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [WithdrawalWalletController::class, 'index']);
    Route::post('/withdrawal', [WithdrawalWalletController::class, 'withdrawal']);
    Route::post('/withdrawal-from-address', [WithdrawalWalletController::class, 'withdrawalFromAddress']);
    Route::post('/withdrawal-from-processing-wallet', [WithdrawalWalletController::class, 'withdrawalFromProcessingWallet']);
    Route::get('/{withdrawalWallet}', [WithdrawalWalletController::class, 'show']);
    Route::put('/{withdrawalWallet}/withdrawal-rules', [WithdrawalWalletController::class, 'updateWithdrawalRules']);
    Route::put('/{withdrawalWallet}', [WithdrawalWalletController::class, 'update']);
});

Route::prefix('/settings')->middleware(['auth:sanctum', 'role:root'])->group(function () {
    Route::get('/', [SettingController::class, 'index']);
    Route::put('/update', [SettingController::class, 'update']);
    Route::get('/registration', [SettingController::class, 'registration'])->withoutMiddleware(['auth:sanctum', 'role:root']);
});

Route::prefix('/system')->group(function () {
    Route::get('/ping', SystemController::class);
});

Route::prefix('/hot-wallets')->middleware(['auth:sanctum', 'role:root|admin'])->group(function () {
    Route::get('/', [HotWalletController::class, 'index']);
    Route::get('/summary', [HotWalletController::class, 'summary']);
    Route::get('/{hotWallet}', [HotWalletController::class, 'show']);
    Route::get('/{blockchain}/stats', [HotWalletController::class, 'stats']);
});

Route::prefix('/transfers')->middleware(['auth:sanctum', 'role:root|admin'])->group(function () {
    Route::get('/', TransferController::class);
});
