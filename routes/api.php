<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\Authenticate;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Models\Transactions;
use App\Models\Book;
use App\Models\User;

// Health check endpoint
Route::get('/health', function() {
    return response()->json(['status' => 'ok']);
});

Route::apiResource('books', BookController::class);
Route::apiResource('transactions', TransactionController::class);

Route::middleware('auth:sanctum')->get('/profile', function(Request $request){
    return new UserResource($request->user());
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/profile', function (Request $request) {
        return new UserResource($request->user());
    });
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // User management
    Route::get('/users', [AdminController::class, 'index']);
    Route::put('/users/{id}', [AdminController::class, 'update']);
    Route::delete('/users/{id}', [AdminController::class, 'destroy']);

    // Book management
    Route::get('/books/popular', [AdminController::class, 'popularBooks']);
    
    // Transaction management
    Route::get('/transactions/recent', [AdminController::class, 'recentTransactions']);
    Route::get('/transactions/overdue', [AdminController::class, 'overdueTransactions']);
    Route::get('/transactions/active', [AdminController::class, 'activeTransactions']);
    
    // Dashboard statistics
    Route::get('/stats', [AdminController::class, 'getStats']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/borrow', [TransactionController::class, 'borrow']);
    Route::post('/return/{transaction}', [TransactionController::class, 'returnBook']);
    Route::get('/borrowings', [TransactionController::class, 'getUserBorrowings']);
});
