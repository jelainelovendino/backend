<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Book;
use App\Models\Transaction;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            })
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());
        return $user;
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }

    public function popularBooks()
    {
        $popularBooks = Book::withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'borrowCount' => $book->transactions_count
                ];
            });

        return response()->json(['data' => $popularBooks]);
    }

    public function recentTransactions()
    {
        $recentTransactions = Transaction::with(['book', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'bookTitle' => $transaction->book->title,
                    'userName' => $transaction->user->name,
                    'type' => $transaction->returned_at ? 'return' : 'borrow',
                    'date' => $transaction->created_at->toDateTimeString()
                ];
            });

        return response()->json(['data' => $recentTransactions]);
    }

    public function overdueTransactions()
    {
        $overdue = Transaction::with(['book', 'user'])
            ->whereNull('returned_at')
            ->where('due_date', '<', Carbon::now())
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user' => [
                        'id' => $transaction->user->id,
                        'name' => $transaction->user->name,
                        'email' => $transaction->user->email,
                    ],
                    'book' => [
                        'id' => $transaction->book->id,
                        'title' => $transaction->book->title,
                        'author' => $transaction->book->author,
                    ],
                    'borrow_date' => $transaction->borrowed_at,
                    'due_date' => $transaction->due_date,
                    'return_date' => $transaction->returned_at,
                    'status' => 'overdue'
                ];
            });

        return response()->json(['data' => $overdue]);
    }

    public function activeTransactions()
    {
        $active = Transaction::with(['book', 'user'])
            ->whereNull('returned_at')
            ->where('due_date', '>=', Carbon::now())
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user' => [
                        'id' => $transaction->user->id,
                        'name' => $transaction->user->name,
                        'email' => $transaction->user->email,
                    ],
                    'book' => [
                        'id' => $transaction->book->id,
                        'title' => $transaction->book->title,
                        'author' => $transaction->book->author,
                    ],
                    'borrow_date' => $transaction->borrowed_at,
                    'due_date' => $transaction->due_date,
                    'return_date' => $transaction->returned_at,
                    'status' => 'active'
                ];
            });

        return response()->json(['data' => $active]);
    }

    public function getStats()
    {
        $stats = [
            'totalBooks' => Book::count(),
            'totalUsers' => User::count(),
            'activeLoans' => Transaction::whereNull('returned_at')->count(),
            'overdueBorrows' => Transaction::whereNull('returned_at')
                ->where('due_date', '<', Carbon::now())
                ->count()
        ];

        return response()->json($stats);
    }
}
