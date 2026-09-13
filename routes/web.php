<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

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

// ================================
// Learning Routes
// ================================

// หน้าเลือกภาษา / คอร์ส
Route::get('/languages', [LearningController::class, 'indexLanguages']);

// หน้าเลือก Unit ของแต่ละ Course
Route::get('/courses/{course}/units', [LearningController::class, 'showUnits']);

// หน้าเลือก Lesson และเนื้อหาการเรียน
Route::get('/units/{unit}/lessons', [LearningController::class, 'showLessons']);

Route::get('/lessons/{lesson}', [LearningController::class, 'showLessonContent']);        });

Route::get('/quiz/{exercise}', [QuizController::class, 'show'])
    ->name('quiz.show');

Route::post('/quiz/{exercise}', [QuizController::class, 'submit'])
    ->name('quiz.submit');

Route::get('/lessons/{lesson}/learn', [LearningController::class, 'learn'])
    ->name('lessons.learn');

Route::get('/lessons/{lesson}/learn/{step}', [LearningController::class, 'learnStep'])
    ->name('lessons.learn.step');

Route::post('/lessons/{lesson}/learn/{step}', [LearningController::class, 'submitLearnStep'])
    ->name('lessons.learn.submit');