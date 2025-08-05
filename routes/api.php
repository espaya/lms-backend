<?php

use App\Http\Controllers\API\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Example protected route group
Route::middleware(['web'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', fn () => response()->json(['message' => 'Welcome Admin']));
// });

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/user/dashboard', fn () => response()->json(['message' => 'Welcome User']));
});
