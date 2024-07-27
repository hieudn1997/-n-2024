<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Broadcast;
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

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/assets/{uid}/{name}',  [ImageController::class, 'getImage'])->name('packages.asset');

Route::get('/banks/{bank_id}/{account_id}', [BankController::class, 'getBankAccountInfo'])->name('packages.get-bank-account-info');

Route::get('/banks/used', [BankController::class, 'getBankAccountUsed'])->name('banks.used');
