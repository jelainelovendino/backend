<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'books';

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'publisher',
        'total_copies',
        'available_copies',
        'genre',
        'cover_image',
        'description'
    ];
}
