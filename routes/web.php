<?php

use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/quiz/{exercise}', [QuizController::class, 'show'])
    ->name('quiz.show');

Route::post('/quiz/{exercise}', [QuizController::class, 'submit'])
    ->name('quiz.submit');