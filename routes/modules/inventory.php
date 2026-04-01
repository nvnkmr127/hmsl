<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'inventory', 'as' => 'inventory.', 'middleware' => ['permission:manage inventory']], function () {
        Route::get('/', fn() => view('pages.inventory.index'))->name('index');
        Route::get('/stock', fn() => view('pages.inventory.stock'))->name('stock');
        Route::get('/suppliers', fn() => view('pages.inventory.suppliers'))->name('suppliers');
    });
});
