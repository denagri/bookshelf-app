<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = Auth::user()->favoriteBooks()->with('genres')->paginate(10);
        return view('books.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book->id);
        return back()->with('success', 'お気に入りの状態を更新しました。');
    }
}
