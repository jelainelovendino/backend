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
        $books = Book::get();
        if($books->count() > 0)
        {
            return BookResource::collection($books);
        }
        else
        {
            return response()->json(['message' => 'No record available'], 200);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'published_date' => 'required|date',
            'publisher' => 'required|string|max:255',
            'total_copies' => 'required|integer',
            'available_copies' => 'required|integer',
            'genre' => 'required|string|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'message' => 'All fields are required',
                'error' => $validator->messages(),
            ], 422);
        }

        if ($request->hasFile('cover_image')){
            $image = $request->file('cover_image');
            $imageName = time() .'.'. $image->getClientoriginalExtension();
            $image->move(public_path('cover_image'),'$imageName');
        } else {
            $imageName = null;
        }

        $books = Book::create([
            'title' => $request -> title,
            'author' => $request -> author,
            'isbn' => $request -> isbn,
            'published_date' => $request -> published_date,
            'publisher' => $request -> publisher,
            'total_copies' => $request -> total_copies,
            'available_copies' => $request -> available_copies,
            'genre' => $request -> genre,
            'cover_image' => $imageName,
            'description' => $request -> description,
        ]);

        return response()->json([
            'message' => 'Book Created Succesfully',
            'data' => new BookResource($books)
        ],200);
    }

    public function show(Book $book)
    {
        return new BookResource($book);
    }

    public function update(Request $request, Book $book)
    {

        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'published_date' => 'required|date',
            'publisher' => 'required|string|max:255',
            'total_copies' => 'required|integer',
            'available_copies' => 'required|integer',
            'genre' => 'required|string|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'message' => 'All fields are required',
                'error' => $validator->messages(),
            ], 422);
        }
        
        if ($request->hasFile('cover_image')){
            $image = $request->file('cover_image');
            $imageName = time() .'.'. $image->getClientoriginalExtension();
            $image->storeAs('cover_images','$imageName', 'public');
        } else {
            $imageName = $imageName = $book->cover_image;
        }

        $book->update([
            'title' => $request -> title,
            'author' => $request -> author,
            'isbn' => $request -> isbn,
            'published_date' => $request -> published_date,
            'publisher' => $request -> publisher,
            'total_copies' => $request -> total_copies,
            'available_copies' => $request -> available_copies,
            'genre' => $request -> genre,
            'cover_image' => $imageName,
            'description' => $request -> description,
        ]);

        return response()->json([
            'message' => 'Book Updated Succesfully',
            'data' => new BookResource($book)
        ],200);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Book Deleted Succesfully',
            'data' => new BookResource($book)
        ],200);
    }



}
