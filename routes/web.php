<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return view('welcome');
});
// ================================
// Authentication Routes
// ================================

// สำหรับผู้ใช้ที่ยังไม่ได้ Login
Route::middleware('guest')->group(function () {

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');


    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.authenticate');
});


// สำหรับผู้ใช้ที่ Login แล้ว
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])
        ->name('profile');


    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});