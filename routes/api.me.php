<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

# api/me/

Route::get('/', [UserController::class, 'getUserInfo'])->name('user.info');
Route::post('/change-avatar', [UserController::class, 'changeAvatar'])->name('user.change-avatar');
Route::get('/settings', [UserController::class, 'getUserStatus'])->name('user.status');
Route::get('/assets', [UserController::class, 'getAssetInfo'])->name('user.asset');
Route::prefix('/kyc')->group(function () {
    Route::post('/identity_image_front', [UserController::class, 'uploadFrontIdentityImage'])->name('user.identity_image_front');
    Route::post('/identity_image_back', [UserController::class, 'uploadBackIdentityImage'])->name('user.identity_image_back');
    Route::post('/verify', [UserController::class, 'checkIdentityCardImage'])->name('user.check-identity-card-image');
});
