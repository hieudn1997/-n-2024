<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NotificationController::class, 'index'])->name('notifications.get-all');
Route::patch('/{id}/mark-as-read', [NotificationController::class, 'maskAsRead'])->name('notifications.mark-as-read');
Route::patch('/mark-all-as-read', [NotificationController::class, 'maskAllAsRead'])->name('notifications.mark-all-as-read');
Route::delete('/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
Route::delete('/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
