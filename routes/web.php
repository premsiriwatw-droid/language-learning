<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.authenticate');
});


/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])
        ->name('profile');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/languages', [LearningController::class, 'indexLanguages'])
        ->name('languages.index');

    Route::get('/courses/{course}/units', [LearningController::class, 'showUnits'])
        ->name('courses.units');

    Route::get('/units/{unit}/lessons', [LearningController::class, 'showLessons'])
        ->name('units.lessons');

    Route::get('/lessons/{lesson}', [LearningController::class, 'showLessonContent'])
        ->name('lessons.show');
});


/*
|--------------------------------------------------------------------------
| Quiz Routes
|--------------------------------------------------------------------------
|
| คงพฤติกรรมเดิมของ Quiz System ไว้
| Quiz routes ไม่อยู่ใน auth middleware
|
*/

Route::get('/quiz/{exercise}', [QuizController::class, 'show'])
    ->name('quiz.show');

Route::post('/quiz/{exercise}', [QuizController::class, 'submit'])
    ->name('quiz.submit');


/*
|--------------------------------------------------------------------------
| Lesson Learning Flow
|--------------------------------------------------------------------------
|
| Learning flow เดิมสามารถเข้าถึงได้โดยไม่ผ่าน auth middleware
|
*/

Route::get('/lessons/{lesson}/learn', [LearningController::class, 'learn'])
    ->name('lessons.learn');

Route::get('/lessons/{lesson}/learn/{step}', [LearningController::class, 'learnStep'])
    ->name('lessons.learn.step');

Route::post('/lessons/{lesson}/learn/{step}', [LearningController::class, 'submitLearnStep'])
    ->name('lessons.learn.submit');