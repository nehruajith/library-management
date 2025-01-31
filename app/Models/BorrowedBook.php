<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowedBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'book_id', 'borrowed_at', 'due_date', 'returned_at', 'fine'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
