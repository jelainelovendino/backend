<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();
        return response()->json([
            'message' => 'Books retrieved successfully',
            'data' => BookResource::collection($books)
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages(),
            ], 422);
        }

        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'total_copies' => $request->quantity,
            'available_copies' => $request->quantity,
            'genre' => 'Uncategorized',
            'published_date' => now(),
            'publisher' => 'Unknown',
            'description' => '',
        ]);

        return response()->json([
            'message' => 'Book created successfully',
            'data' => new BookResource($book)
        ], 201);
    }

    public function show($id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Book retrieved successfully',
            'data' => new BookResource($book)
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages(),
            ], 422);
        }

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'total_copies' => $request->quantity,
            'available_copies' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Book updated successfully',
            'data' => new BookResource($book)
        ], 200);
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
            'data' => new BookResource($book)
        ], 200);
    }
}
