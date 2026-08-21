<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Review $review;

    /**
     * テスト前の初期化（ユーザーといいね対象のレビューを準備）
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $book = Book::factory()->create();
        $this->review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * 観点: レビューに対するいいねの登録と解除（トグル処理）
     */
    public function test_レビューに対していいねの登録および解除ができること(): void
    {
        $response = $this->actingAs($this->user)
                         ->post(route('reviews.like', $this->review));

        $this->assertDatabaseHas('review_likes', [
            'user_id'   => $this->user->id,
            'review_id' => $this->review->id,
        ]);

        $response->assertRedirect(route('books.index'));

        $response = $this->actingAs($this->user)
                         ->post(route('reviews.like', $this->review));

        $this->assertDatabaseMissing('review_likes', [
            'user_id'   => $this->user->id,
            'review_id' => $this->review->id,
        ]);

        $response->assertRedirect(route('books.index'));
    }
}
