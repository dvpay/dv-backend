<?php

use App\Http\Controllers\Setup\EnvironmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('setup')->group(function () {
    Route::get('/', [EnvironmentController::class, 'index']);
    Route::post('/save', [EnvironmentController::class, 'saveEnvironment']);
    Route::get('/admin', [EnvironmentController::class, 'admin']);
    Route::post('/register', [EnvironmentController::class, 'registerUser']);
});
