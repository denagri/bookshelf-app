<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Http\Requests\BookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class BooksController extends Controller
{
    /**
     * 書籍一覧画面の表示
     */
    public function index()
    {
        $books = Book::with('genres')->latest()->paginate(10);
        return view('books.index', compact('books'));
    }

    /**
     * 書籍詳細画面の表示
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);
        return view('books.show', compact('book'));
    }

    /**
     * 書籍登録画面の表示
     */
    public function create()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    /**
     * 書籍の登録処理
     */
    public function store(BookRequest $request)
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {
            $bookData = Arr::except($validated, ['genres']);
            $book = auth()->user()->books()->create($bookData);
            $book->genres()->sync($validated['genres']);

            return $book;
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }
        /**
     * 書籍編集画面の表示
     */
    public function edit(Book $book)
    {
        if (auth()->id() !== $book->user_id) {
            abort(403, 'この書籍の編集権限がありません。');
        }

        $genres = Genre::all();
        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍の更新処理
     */
    public function update(BookRequest $request, Book $book)
    {
        if (auth()->id() !== $book->user_id) {
            abort(403, 'この書籍の更新権限がありません。');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $book) {
            $bookData = Arr::except($validated, ['genres']);
            $book->update($bookData);
            $book->genres()->sync($validated['genres']);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍の削除処理
     */
    public function destroy(Book $book)
    {
        if (auth()->id() !== $book->user_id) {
            abort(403, 'この書籍の削除権限がありません。');
        }

        DB::transaction(function () use ($book) {
            $book->genres()->detach();
            if (method_exists($book, 'favoritedByUsers')) {
                $book->favoritedByUsers()->detach();
            }
            foreach ($book->reviews as $review) {
                $review->likedByUsers()->detach();
                $review->delete();
            }
            $book->delete();
        });

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    public function ranking()
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->has('reviews')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(10)
            ->get();

        return view('books.index', compact('books'));
    }
}
