<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

# api/transactions/

Route::get('/{id}/check-payment', [TransactionController::class, 'checkPayment'])->name('transaction.check-payment');
Route::get('/{id}', [TransactionController::class, 'show'])->name('transaction.detail');
Route::apiResource('/', TransactionController::class)->only(['index']);
