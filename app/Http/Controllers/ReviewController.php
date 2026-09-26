<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    public function store(ReviewRequest $request, Book $book)
    {
        if (!auth()->check()) {
            abort(403);
        }

        $validated = $request->validated();
        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating'  => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'レビューを投稿しました。');
    }

    public function edit(Review $review)
    {
        if (!auth()->check() || auth()->id() !== $review->user_id) {
            abort(403);
        }

        return view('reviews.edit', compact('review'));
    }


    public function update(ReviewRequest $request, Review $review)
    {
        if (!auth()->check() || auth()->id() !== $review->user_id) {
            abort(403);
        }

        $validated = $request->validated();
        $review->update($validated);

        return redirect()
            ->route('books.show', $review->book_id)
            ->with('success', 'レビューを更新しました。');
    }

    public function destroy(Review $review)
    {
        if (!auth()->check() || auth()->id() !== $review->user_id) {
            abort(403);
        }
        $review->likedByUsers()->detach();
        $review->delete();

        return back()->with('success', 'レビューを削除しました。');
    }

    public function like(Review $review)
    {
        if (!auth()->check()) {
            abort(403);
        }

        auth()->user()->likedReviews()->toggle($review->id);

        return back()->with('success', 'いいねの状態を更新しました。');
    }
}
