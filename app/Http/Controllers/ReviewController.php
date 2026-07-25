<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * レビューの投稿処理
     */
    public function store(ReviewRequest $request, Book $book)
    {
        $validated = $request->validated();
        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating'  => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'レビューを投稿しました。');
    }

    /**
     * 自分のレビューの編集画面表示
     */
    public function edit(Review $review)
    {
        if (auth()->id() !== $review->user_id) {
            abort(403);
        }

        return view('reviews.edit', compact('review'));
    }

        /**
     * レビューの編集処理
     */
    public function update(ReviewRequest $request, Review $review)
    {
        if (auth()->id() !== $review->user_id) {
            abort(403);
        }

        $validated = $request->validated();
        $review->update($validated);

        return redirect()
            ->route('books.show', $review->book_id)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューの削除処理
     */
    public function destroy(Review $review)
    {
        if (auth()->id() !== $review->user_id) {
            abort(403);
        }
        $review->likedByUsers()->detach();
        $review->delete();

        return back()->with('success', 'レビューを削除しました。');
    }

    /**
     * レビューのいいね・解除
     */
    public function like(Review $review)
    {
        auth()->user()->likedReviews()->toggle($review->id);

        return back()->with('success', 'いいねの状態を更新しました。');
    }
}
