<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\FocusSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskRecommendationController;
use App\Http\Controllers\SyncController;
use Illuminate\Http\Request;

// ============================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ============================================================
// PROTECTED ROUTES (auth:sanctum required)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth ---
    Route::get('/user',    fn(Request $r) => $r->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Profile ---
    Route::get('/profile',        [ProfileController::class, 'show']);
    Route::put('/profile',       [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

    // --- Categories ---
    Route::apiResource('categories', CategoryController::class);

    // --- Tasks (recommendation must be before apiResource to avoid ID conflict) ---
    Route::get('/tasks/recommendation', [TaskController::class, 'recommendation']);
    Route::apiResource('tasks', TaskController::class);

    // --- Energy ---
    Route::post('/energy', [EnergyController::class, 'store']);

    // --- Focus Sessions ---
    Route::post('/focus/start',      [FocusSessionController::class, 'start']);
    Route::post('/focus/finish/{id}',[FocusSessionController::class, 'finish']);
    Route::post('/focus/fail/{id}',  [FocusSessionController::class, 'fail']);
    Route::post('/focus/sync',       [FocusSessionController::class, 'sync']);

    // --- Task Recommendation (Mindy Bantu Aku) ---
    Route::get('/task/recommend', [TaskRecommendationController::class, 'recommend']);

    // --- Sync Endpoints (Flutter offline-first sync) ---
    // Flutter calls these to pull server state and reconcile with local SQLite
    Route::get('/journey', [SyncController::class, 'journey']);
    Route::get('/streak',  [SyncController::class, 'streak']);
});
