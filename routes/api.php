<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\QuestionManagerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;


Route::post('/login', [AuthController::class, 'login'])->name('login');

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', fn() => response()->json(['message' => 'Welcome Admin']));
    // Add other API endpoints here
    Route::post('/admin/dashboard/upload-questions', [QuestionManagerController::class, 'store']);
    Route::get('/admin/dashboard/get-subjects', [QuestionManagerController::class, 'index']); //
    Route::get('/subjects', [QuestionManagerController::class, 'getSubject']);
    Route::get('/topics', [QuestionManagerController::class, 'getTopic']);
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::get('/user/dashboard', fn() => response()->json(['message' => 'Welcome User']));
    // Add other API endpoints here;
});
