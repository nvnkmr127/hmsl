<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'laboratory', 'as' => 'laboratory.', 'middleware' => ['permission:view laboratory']], function () {
        Route::get('/', fn() => view('pages.laboratory.index'))->name('index');
        Route::get('/tests', fn() => view('pages.laboratory.tests'))->name('tests');
        Route::get('/results', fn() => view('pages.laboratory.results'))->name('results');
    });
});
