<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\FocusSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskRecommendationController;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Jalur untuk Profile (Menyesuaikan dengan ProfileRemoteDataSource di Flutter)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

    // Jalur auth bawaanmu yang sudah ada
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});

Route::middleware('auth:sanctum')->get('/tasks/recommendation', [TaskController::class, 'recommendation']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});

Route::middleware('auth:sanctum')->post('/energy', [EnergyController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/focus/start', [FocusSessionController::class, 'start']);
    Route::post('/focus/finish/{id}', [FocusSessionController::class, 'finish']);
    Route::post('/focus/fail/{id}', [FocusSessionController::class, 'fail']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/task/recommend', [TaskRecommendationController::class, 'recommend']);
});
