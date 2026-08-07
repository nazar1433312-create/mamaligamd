<?php

use App\Http\Controllers\Admin\VerificationDocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TelegramWebhookController;
use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Admin\Verifications as AdminVerifications;
use App\Livewire\Orders\Create as OrdersCreate;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\MyOrders;
use App\Livewire\Orders\Show as OrdersShow;
use App\Livewire\Admin\Support as AdminSupport;
use App\Livewire\Executor\Dashboard as ExecutorDashboard;
use App\Livewire\Messages\Inbox as MessagesInbox;
use App\Livewire\Messages\Show as MessagesShow;
use App\Livewire\Settings\PayoutSettings;
use App\Livewire\Settings\Verification as VerificationSettings;
use App\Livewire\Support\Create as SupportCreate;
use App\Livewire\Support\MyTickets as SupportMyTickets;
use App\Livewire\Support\Show as SupportShow;
use App\Livewire\Users\PublicProfile;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/orders');

Route::get('lang/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED, true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang.switch');

Route::post('telegram/webhook/{secret}', TelegramWebhookController::class)
    ->name('telegram.webhook');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('orders', OrdersIndex::class)->name('orders.index');
Route::get('orders/create', OrdersCreate::class)->middleware('auth')->name('orders.create');
Route::get('orders/mine', MyOrders::class)->middleware('auth')->name('orders.my');
Route::get('executor', ExecutorDashboard::class)->middleware('auth')->name('executor.dashboard');
Route::get('orders/{order}', OrdersShow::class)->name('orders.show');

Route::get('users/{user}', PublicProfile::class)->name('users.show');

Route::middleware('auth')->group(function () {
    Route::get('messages', MessagesInbox::class)->name('messages.inbox');
    Route::get('messages/{user}', MessagesShow::class)->name('messages.show');
});

Route::get('settings/payout', PayoutSettings::class)->middleware('auth')->name('settings.payout');
Route::get('settings/verification', VerificationSettings::class)->middleware('auth')->name('settings.verification');

Route::get('support', SupportCreate::class)->name('support.create');
Route::get('support/mine', SupportMyTickets::class)->middleware('auth')->name('support.my');
Route::get('support/{ticket}', SupportShow::class)->middleware('auth')->name('support.show');

Route::middleware('auth')->group(function () {
    Route::get('payments/liqpay/checkout/{order}', [PaymentController::class, 'checkout'])
        ->name('payments.liqpay.checkout');
});

Route::post('payments/liqpay/callback', [PaymentController::class, 'callback'])
    ->name('payments.liqpay.callback');

Route::middleware(['admin.gate', 'auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('users', AdminUsers::class)->name('users');
    Route::get('categories', AdminCategories::class)->name('categories');
    Route::get('orders', AdminOrders::class)->name('orders');
    Route::get('support', AdminSupport::class)->name('support');
    Route::get('verifications', AdminVerifications::class)->name('verifications');
    Route::get('verifications/{verificationRequest}/document/{index}', VerificationDocumentController::class)
        ->name('verifications.document');
});

require __DIR__.'/auth.php';
