<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
    /**
     * このジャンルに属する書籍一覧（多対多）
     */
    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_genre')->withTimestamps();
    }

}
