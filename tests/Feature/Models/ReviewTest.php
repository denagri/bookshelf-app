<?php

namespace Tests\Feature\Models;

use App\Models\Review;
use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_レビューモデルの基本属性が正しく保存できること()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $reviewData = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テスト用のレビューコメントです。最高でした。',
        ];

        $review = Review::create($reviewData);

        $this->assertDatabaseHas('reviews', [
            'comment' => 'テスト用のレビューコメントです。最高でした。',
            'rating' => 5,
        ]);

        $this->assertEquals($user->id, $review->user_id);
        $this->assertEquals($book->id, $review->book_id);
    }

    /**
     * @test
     */
    public function test_user_リレーション経由でレビューを投稿したユーザーを取得できること()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    /**
     * @test
     */
    public function test_book_リレーション経由でレビュー対象の書籍を取得できること()
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertNotNull($review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    /**
     * @test
     */
    public function test_likedByUsers_リレーション経由でレビューにいいねしたユーザーを取得できること()
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();

        $review->likedByUsers()->attach($user->id);

        $this->assertCount(1, $review->likedByUsers);
        $this->assertTrue($review->likedByUsers->contains($user));
        $this->assertNotNull($review->likedByUsers->first()->pivot->created_at);
    }
}
