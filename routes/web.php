<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('/index');
});

// Route::middleware(['auth'])->group(function () {
//     Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
//     Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
// });

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// 新規登録のルートは /orders/{orderId} より先に定義する
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{orderId}/receipt', [OrderController::class, 'showReceipt'])->name('orders.receipt');
Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::get('/orders/{orderId}', [OrderController::class, 'showDetail'])->name('orders.detail');

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, config('app.available_locales', ['en', 'ja'])), 404);
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');
