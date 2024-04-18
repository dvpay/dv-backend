<?php

use App\Http\Controllers\Api\External\StoreController;
use App\Http\Controllers\Api\External\ExchangeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v2'], function () {
    Route::group(['prefix' => 'stores', 'middleware' => 'auth:store-api-key'], function () {
        Route::get('/currencies/rate', [StoreController::class, 'rateStore']);
    });

    Route::group(['prefix' => 'exchanges', 'middleware' => 'auth:store-api-key'], function () {
        Route::get('/balance', ExchangeController::class);
    });
});
