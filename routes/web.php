<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\UnitController as AdminUnitController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ContentManagementController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\LearningHistoryController;
use App\Http\Controllers\ProgressProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Middleware\RememberLearningVisit;
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
    Route::get('/learning-history', [LearningHistoryController::class, 'index'])->name('learning-history.index');
    Route::get('/learning-history/{attempt}', [LearningHistoryController::class, 'show'])->name('learning-history.show');
    Route::get('/learning-history/{attempt}/review', [LearningHistoryController::class, 'review'])->name('learning-history.review');
    Route::post('/learning-history/{attempt}/review', [LearningHistoryController::class, 'submitReview'])->name('learning-history.review.submit');
    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProgressProfileController::class, 'show'])
        ->name('profile');

    Route::post('/profile/upload', [ProgressProfileController::class, 'upload'])
        ->name('profile.photo.upload');

    Route::get('/profile/photo', [ProgressProfileController::class, 'photo'])
        ->name('profile.photo');

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Learning Structure
    |--------------------------------------------------------------------------
    */

    Route::get('/languages', [LearningController::class, 'indexLanguages'])
        ->name('languages.index');

    Route::get('/courses/{course}/units', [LearningController::class, 'showUnits'])
        ->name('courses.units');

    Route::get('/units/{unit}/lessons', [LearningController::class, 'showLessons'])
        ->name('units.lessons');

    Route::get('/lessons/{lesson}', [LearningController::class, 'showLessonContent'])
        ->name('lessons.show');

    /*
    |--------------------------------------------------------------------------
    | Content Management Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['admin'])
        ->prefix('content')
        ->name('content.')
        ->group(function () {
            Route::get('/lessons/{lesson}', [ContentManagementController::class, 'index'])
                ->name('index');

            Route::post('/lessons/{lesson}/vocabularies', [ContentManagementController::class, 'storeVocabulary'])
                ->name('vocabularies.store');

            Route::put('/vocabularies/{vocabulary}', [ContentManagementController::class, 'updateVocabulary'])
                ->name('vocabularies.update');

            Route::delete('/vocabularies/{vocabulary}', [ContentManagementController::class, 'destroyVocabulary'])
                ->name('vocabularies.destroy');

            Route::post('/lessons/{lesson}/exercises', [ContentManagementController::class, 'storeExercise'])
                ->name('exercises.store');

            Route::put('/exercises/{exercise}', [ContentManagementController::class, 'updateExercise'])
                ->name('exercises.update');

            Route::delete('/exercises/{exercise}', [ContentManagementController::class, 'destroyExercise'])
                ->name('exercises.destroy');

            Route::post('/exercises/{exercise}/questions', [ContentManagementController::class, 'storeQuestion'])
                ->name('questions.store');

            Route::put('/questions/{question}', [ContentManagementController::class, 'updateQuestion'])
                ->name('questions.update');

            Route::delete('/questions/{question}', [ContentManagementController::class, 'destroyQuestion'])
                ->name('questions.destroy');

            Route::post('/questions/{question}/answers', [ContentManagementController::class, 'storeAnswer'])
                ->name('answers.store');

            Route::put('/answers/{answer}', [ContentManagementController::class, 'updateAnswer'])
                ->name('answers.update');

            Route::delete('/answers/{answer}', [ContentManagementController::class, 'destroyAnswer'])
                ->name('answers.destroy');
        });
});

/*
|--------------------------------------------------------------------------
| Admin: Learning Structure
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/languages', [AdminLanguageController::class, 'index'])
            ->name('languages.index');

        Route::post('/languages', [AdminLanguageController::class, 'store'])
            ->name('languages.store');

        Route::put('/languages/{language}', [AdminLanguageController::class, 'update'])
            ->name('languages.update');

        Route::get('/languages/{language}/delete', [AdminLanguageController::class, 'confirmDestroy'])
            ->name('languages.delete');

        Route::delete('/languages/{language}', [AdminLanguageController::class, 'destroy'])
            ->name('languages.destroy');

        Route::get('/languages/{language}/courses', [AdminCourseController::class, 'index'])
            ->name('languages.courses.index');

        Route::post('/languages/{language}/courses', [AdminCourseController::class, 'store'])
            ->name('languages.courses.store');

        Route::put('/courses/{course}', [AdminCourseController::class, 'update'])
            ->name('courses.update');

        Route::get('/courses/{course}/delete', [AdminCourseController::class, 'confirmDestroy'])
            ->name('courses.delete');

        Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])
            ->name('courses.destroy');

        Route::get('/courses/{course}/units', [AdminUnitController::class, 'index'])
            ->name('courses.units.index');

        Route::post('/courses/{course}/units', [AdminUnitController::class, 'store'])
            ->name('courses.units.store');

        Route::patch('/courses/{course}/units/reorder', [AdminUnitController::class, 'reorder'])
            ->name('courses.units.reorder');

        Route::put('/units/{unit}', [AdminUnitController::class, 'update'])
            ->name('units.update');

        Route::get('/units/{unit}/delete', [AdminUnitController::class, 'confirmDestroy'])
            ->name('units.delete');

        Route::delete('/units/{unit}', [AdminUnitController::class, 'destroy'])
            ->name('units.destroy');

        Route::get('/units/{unit}/lessons', [AdminLessonController::class, 'index'])
            ->name('units.lessons.index');

        Route::post('/units/{unit}/lessons', [AdminLessonController::class, 'store'])
            ->name('units.lessons.store');

        Route::patch('/units/{unit}/lessons/reorder', [AdminLessonController::class, 'reorder'])
            ->name('units.lessons.reorder');

        Route::put('/lessons/{lesson}', [AdminLessonController::class, 'update'])
            ->name('lessons.update');

        Route::get('/lessons/{lesson}/delete', [AdminLessonController::class, 'confirmDestroy'])
            ->name('lessons.delete');

        Route::delete('/lessons/{lesson}', [AdminLessonController::class, 'destroy'])
            ->name('lessons.destroy');
    });

/*
|--------------------------------------------------------------------------
| Quiz Routes
|--------------------------------------------------------------------------
*/

Route::get('/quiz/{exercise}', [QuizController::class, 'show'])
    ->name('quiz.show');

Route::post('/quiz/{exercise}', [QuizController::class, 'submit'])
    ->name('quiz.submit');

/*
|--------------------------------------------------------------------------
| Lesson Learning Flow
|--------------------------------------------------------------------------
*/

Route::get('/lessons/{lesson}/learn', [LearningController::class, 'learn'])
    ->name('lessons.learn');

Route::get('/lessons/{lesson}/learn/{step}', [LearningController::class, 'learnStep'])
    ->middleware(RememberLearningVisit::class)
    ->name('lessons.learn.step');

Route::post('/lessons/{lesson}/learn/{step}', [LearningController::class, 'submitLearnStep'])
    ->name('lessons.learn.submit');

// Summary ตรวจว่าเรียนครบจริงภายใน Controller
Route::get('/lessons/{lesson}/summary', [LearningController::class, 'lessonSummary'])
    ->name('lessons.learn.summary');

/*
|--------------------------------------------------------------------------
| Admin Dashboard / User Management
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index',
        ])->name('dashboard');

        Route::get('/users', [
            UserManagementController::class,
            'index',
        ])->name('users.index');

        Route::get('/users/{user}/edit', [
            UserManagementController::class,
            'edit',
        ])->name('users.edit');

        Route::put('/users/{user}', [
            UserManagementController::class,
            'update',
        ])->name('users.update');
    });
