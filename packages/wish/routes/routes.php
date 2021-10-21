<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Wish\Controllers\WishController;
use Wish\Models\Wish;

Route::model('group', Group::class);
Route::model('wish', Wish::class);

Route::middleware('auth.basic')->prefix('groups/{group}/wishes')->name('group.wishes.')->group(function () {
    $controller = WishController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::get('{wish}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{wish}', $controller . '@update')->name('update');
    Route::delete('{wish}', $controller . '@destroy')->name('destroy');
});

Route::middleware('auth.basic')->prefix('groups/{group}')->name('group.')->group(function () {
    $controller = WishController::class;

    Route::get('partner/wishes', $controller . '@getPartnerWishes')->name('partner.wishes');
});
