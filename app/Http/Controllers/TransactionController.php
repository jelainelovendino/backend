<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function borrow(Request $request)
    {
        try {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);
            $user = auth()->user();

            // Check borrow limit
        $activeBorrows = Transaction::where('user_id', $user->id)
           ->where('status', 'borrowed')
           ->count();
                
           if ($activeBorrows >= 3) {
            return response()->json([
                    'message' => 'You have reached your borrowing limit (3 books)',
                    'error' => 'BORROW_LIMIT_REACHED'
            ], 403);
        } 

            // Check book availability
        if ($book->available_copies < 1) {
                return response()->json([
                    'message' => 'This book is currently unavailable',
                    'error' => 'NO_COPIES_AVAILABLE'
                ], 400);
            }

            // Check if user already borrowed this book
            $existingBorrow = Transaction::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('status', 'borrowed')
                ->first();

            if ($existingBorrow) {
                return response()->json([
                    'message' => 'You have already borrowed this book',
                    'error' => 'ALREADY_BORROWED'
                ], 400);
        }
    
            // Create transaction
        $transaction = Transaction::create([
                'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7),
            'status' => 'borrowed',
        ]);
    
            // Update book availability
        $book->decrement('available_copies');
    
            return response()->json([
                'message' => 'Book borrowed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'due_date' => $transaction->due_date->format('Y-m-d'),
                    'book' => [
                        'id' => $book->id,
                        'title' => $book->title,
                        'available_copies' => $book->available_copies
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Invalid book ID provided',
                'error' => 'VALIDATION_ERROR',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your request',
                'error' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function returnBook(Transaction $transaction)
    {
        if ($transaction->status !== 'borrowed') {
            return response()->json(['message' => 'Book already returned or invalid status'], 400);
        }
    
        $transaction->update([
            'returned_at' => Carbon::now(),
            'status' => 'returned',
        ]);
    
        $transaction->book->increment('available_copies');
    
        return response()->json(['message' => 'Book returned successfully']);
    }

    public function getUserBorrowings()
    {
        try {
            $user = auth()->user();
            $borrowings = Transaction::with(['book'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'book' => [
                            'id' => $transaction->book->id,
                            'title' => $transaction->book->title,
                            'author' => $transaction->book->author
                        ],
                        'borrowed_at' => $transaction->borrowed_at,
                        'due_date' => $transaction->due_date,
                        'returned_at' => $transaction->returned_at,
                        'status' => $transaction->status,
                        'is_overdue' => $transaction->status === 'borrowed' && 
                            Carbon::parse($transaction->due_date)->isPast()
                    ];
                });

            return response()->json([
                'message' => 'Borrowings retrieved successfully',
                'data' => $borrowings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve borrowings',
                'error' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
