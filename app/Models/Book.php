<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
    ];
    /**
     * 本を登録したユーザー（多対1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 書籍に紐付くジャンル一覧（多対多）
     */
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre')->withTimestamps();
    }

    /**
     * 書籍に投稿されたレビュー一覧（1対多）
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    /**
     * この書籍をお気に入り登録しているユーザー一覧（多対多）
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'book_user')->withTimestamps();
    }
}
