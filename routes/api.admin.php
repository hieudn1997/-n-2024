<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

# api/admin/

Route::post('/verify_credentials/{credential}/{otp}', [AdminController::class, 'verifyCredential'])->name('admin.verify-credentials');

Route::get('/users/transactions', [TransactionController::class, 'get'])->name('admin.user.transactions');
Route::resource('/users', UserController::class)->only(['index', 'show', 'store', 'destroy']);
Route::put('/users/{userId}', [UserController::class, 'update'])->name('admin.update-user');
Route::delete('/users', [UserController::class, 'remove'])->name('admin.remove');
Route::post('/users/{userId}/send-verify-email', [UserController::class, 'sendVerifyEmail'])->name('admin.send-verify-mail');
Route::post('/users/{userId}/send-notification', [NotificationController::class, 'create'])->name('admin.get-notifications');
Route::get('/users/{userId}/notifications', [NotificationController::class, 'get'])->name('admin.get-notifications');

Route::get('/funds', [FundController::class, 'get'])->name('admin.get-list-fund');
Route::get('/funds/transactions', [TransactionController::class, 'getFundTransactions'])->name('admin.fund.transactions');
Route::resource('/funds', FundController::class)->only(['show', 'store', 'destroy']);
Route::put('/funds/{fundId}', [FundController::class, 'update'])->name('admin.update-fund');

Route::get('/packages', [PackageController::class, 'getAllPackages'])->name('admin.packages');
