<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    /**
     * テスト前の初期化（ユーザーとレビュー対象の本を準備）
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    /**
     * @test
     * 観点: レビューの投稿処理（Create）
     */
    public function test_書籍に対してレビューを投稿でき書籍詳細画面へリダイレクトされること(): void
    {
        $reviewData = [
            'comment' => 'とても参考になる本でした！',
            'rating'  => 5,
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('reviews.store', $this->book), $reviewData);

        $this->assertDatabaseHas('reviews', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'comment' => 'とても参考になる本でした！',
            'rating'  => 5,
        ]);

        $response->assertRedirect(route('books.index'));
    }

    /**
     * @test
     * 観点: レビューの削除処理（Delete）
     */
    public function test_自分が投稿したレビューを削除できること(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'comment' => '消去予定のレビュー'
        ]);

        $response = $this->actingAs($this->user)
                         ->delete(route('reviews.destroy', $review));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id
        ]);

        $response->assertRedirect(route('books.index'));
    }
}
