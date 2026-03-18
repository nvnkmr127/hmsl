<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'pharmacy', 'as' => 'pharmacy.', 'middleware' => ['permission:view pharmacy']], function () {
        Route::get('/', fn() => view('pages.pharmacy.index'))->name('index');
        Route::get('/orders', fn() => view('pages.pharmacy.orders'))->name('orders');
        Route::get('/stock', fn() => view('pages.pharmacy.stock'))->name('stock');
    });
});
