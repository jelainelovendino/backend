<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

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

    protected $casts = [
        'published_date' => 'date',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
