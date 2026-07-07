<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/create', [PostController::class, 'create']);
Route::post('/posts/create', [PostController::class, 'store']);
Route::get('/posts/{id}/edit', [PostController::class, 'edit']);
Route::post('/posts/{id}/edit', [PostController::class, 'update']);
Route::get('/posts/{id}/delete', [PostController::class, 'delete']);
Route::post('/posts/{id}/delete', [PostController::class, 'destroy']);