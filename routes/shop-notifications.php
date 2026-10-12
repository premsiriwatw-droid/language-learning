<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/shop/{item}/purchase', [ShopController::class, 'purchase'])->whereNumber('item')->name('shop.purchase');
    Route::get('/inventory', [ShopController::class, 'inventory'])->name('inventory.index');
    Route::put('/inventory/{inventory}/equip', [ShopController::class, 'equip'])->whereNumber('inventory')->name('inventory.equip');
    Route::delete('/inventory/equipped/{type}', [ShopController::class, 'unequip'])->name('inventory.unequip');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->whereUuid('notification')->name('notifications.read');
    Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'unread'])->whereUuid('notification')->name('notifications.unread');
});
