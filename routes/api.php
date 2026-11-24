<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// AUTHENTICATION
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// PROJECTS
Route::middleware('auth:api')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});

// TASKS
Route::middleware('auth:api')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});

// COMMENTS
Route::middleware('auth:api')->group(function () {
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/comments/{id}', [CommentController::class, 'show']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::patch('/comments/{id}', [CommentController::class, 'update']);

    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])
        ->middleware('role:admin');
});

// CATEGORIES - MEMBER DAN ADMIN
Route::middleware('auth:api')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/categories/{id}', [CategoryController::class, 'show'])
        ->whereNumber('id');

    Route::post('/categories', [CategoryController::class, 'store']);

    Route::put('/categories/{id}', [CategoryController::class, 'update'])
        ->whereNumber('id');

    Route::patch('/categories/{id}', [CategoryController::class, 'update'])
        ->whereNumber('id');
});

// CATEGORIES - KHUSUS ADMIN
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])
        ->whereNumber('id');
});
