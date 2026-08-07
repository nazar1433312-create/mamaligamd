<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TelegramWebhookController;
use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Orders\Create as OrdersCreate;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\MyOrders;
use App\Livewire\Orders\Show as OrdersShow;
use App\Livewire\Settings\PayoutSettings;
use App\Livewire\Users\PublicProfile;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/orders');

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
Route::get('orders/{order}', OrdersShow::class)->name('orders.show');

Route::get('users/{user}', PublicProfile::class)->name('users.show');

Route::get('settings/payout', PayoutSettings::class)->middleware('auth')->name('settings.payout');

Route::middleware('auth')->group(function () {
    Route::get('payments/liqpay/checkout/{order}', [PaymentController::class, 'checkout'])
        ->name('payments.liqpay.checkout');
});

Route::post('payments/liqpay/callback', [PaymentController::class, 'callback'])
    ->name('payments.liqpay.callback');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('users', AdminUsers::class)->name('users');
    Route::get('categories', AdminCategories::class)->name('categories');
    Route::get('orders', AdminOrders::class)->name('orders');
});

require __DIR__.'/auth.php';
