<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\QuestionManagerController;
use App\Http\Controllers\API\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->name('login');

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/admin/dashboard/get-subjects', [QuestionManagerController::class, 'index']); //
    Route::get('/topics/{topic}/questions', [QuestionManagerController::class, 'getQuestions']);
    Route::get('/answers/summary/{topicId}', [QuestionManagerController::class, 'summary']);
    Route::get('/answers/all/{topic}', [QuestionManagerController::class, 'showByTopic']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', fn() => response()->json(['message' => 'Welcome Admin']));
    // Add other API endpoints here
    Route::post('/admin/dashboard/upload-questions', [QuestionManagerController::class, 'store']);
    Route::get('/subjects', [QuestionManagerController::class, 'getSubject']);
    Route::get('/topics', [QuestionManagerController::class, 'getTopic']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/add', [UserController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::get('/user/dashboard', fn() => response()->json(['message' => 'Welcome User']));
    // Add other API endpoints here;
    Route::post('/answers', [QuestionManagerController::class, 'storeAnswer']);
});
