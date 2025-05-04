<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\Authenticate;

Route::apiResource('books', BookController::class);

Route::middleware('auth:sanctum')->get('/profile', function(Request $request){
    return new UserResource($request->user());
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/profile', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
