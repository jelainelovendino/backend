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
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);

        $user = auth()->user();
        $activeBorrows = Transaction::where('user_id', $user->id)
           ->where('status', 'borrowed')
           ->count();
           if ($activeBorrows >= 3) {
            return response()->json([
                'message' => 'You already reached the borrow limit.'
            ], 403);
        } 

        if ($book->available_copies < 1) {
            return response()->json(['message' => 'No copies available'], 400);
        }
    
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'borrowed_at' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7),
            'status' => 'borrowed',
        ]);
    
        $book->decrement('available_copies');
    
        return response()->json($transaction, 201);
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
}
