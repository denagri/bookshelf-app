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
    
    public function index(Request $request)
    {
        $genres = Genre::all();

        $query = Book::with('genres')->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('author', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre);
            });
        }
            switch ($request->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating':
                $query->orderByRaw('reviews_avg_rating IS NULL ASC')
                      ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $books = $query->paginate(10)->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);
        return view('books.show', compact('book'));
    }

    public function create()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

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

    public function edit(Book $book)
    {
        if (auth()->id() !== $book->user_id) {
            abort(403, 'この書籍の編集権限がありません。');
        }

        $genres = Genre::all();
        return view('books.edit', compact('book', 'genres'));
    }

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
        $rankedBooks = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->has('reviews')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }

}
