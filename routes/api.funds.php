<?php

use App\Http\Controllers\FundController;
use Illuminate\Support\Facades\Route;

# api/funds/

Route::get('/{id}/history', [FundController::class, 'getHistory'])->where('id', '[0-9]+')->name('funds.history');
Route::get('/', [FundController::class, 'index'])->name('funds.list');
Route::get('/{id}', [FundController::class, 'show'])->name('funds.detail');
