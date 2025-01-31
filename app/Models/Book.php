<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'category', 'author', 'publisher', 'isbn', 'status'];

    protected $dates = ['deleted_at'];
    public function borrowedBook()
    {
        return $this->hasMany(BorrowedBook::class);
    }
}
