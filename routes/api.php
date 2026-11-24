<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;

// AUTHENTICATION (PUBLIC)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// AUTHENTICATED (USER & ADMIN)
Route::middleware('auth:api')->group(function () {
    // Auth Profile & Logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::patch('/projects/{id}', [ProjectController::class, 'update']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);

    // Comments
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/comments/{id}', [CommentController::class, 'show']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::patch('/comments/{id}', [CommentController::class, 'update']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->whereNumber('id');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->whereNumber('id');
    Route::patch('/categories/{id}', [CategoryController::class, 'update'])->whereNumber('id');
});

// KHUSUS ADMIN (DELETE)
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->whereNumber('id');
});
