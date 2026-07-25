<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];
    /**
     * レビューを投稿したユーザー（多対1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * レビュー対象の書籍（多対1）
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * このレビューにいいねしたユーザー一覧（多対多）
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'review_likes')->withTimestamps();
    }

}
