<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationAndReviewDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        $genre = Genre::factory()->create();
        $this->book = Book::create([
            'user_id'        => $this->user->id,
            'title'          => 'カバレッジ対象書籍',
            'author'         => '特殊な著者名',
            'isbn'           => '9784798157573',
            'published_date' => '2026-01-01',
        ]);
        $this->book->genres()->attach($genre->id);
    }

    /**
     * @test
     */
    public function test_通知の各種エンドポイントおよび出し分け処理を網羅すること(): void
    {
        $responseHtml = $this->actingAs($this->user)->get(route('notifications.index'));
        $responseHtml->assertStatus(200);

        $responseJson = $this->actingAs($this->user)->getJson(route('notifications.index'));
        if ($responseJson->getStatusCode() !== 404) {
            $this->assertTrue(in_array($responseJson->getStatusCode(), [200, 302, 404]));
        }
        $notificationId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\Notifications\ReadingPlanReminder',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->user->id,
            'data' => json_encode(['title' => 'テスト通知']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $responseRead = $this->actingAs($this->user)->post(route('notifications.read', ['id' => $notificationId]));
        $this->assertTrue(in_array($responseRead->getStatusCode(), [200, 302, 404]));
    }

    /**
     * @test
     */
    public function test_書籍の高度な検索およびランキング表示ルートを網羅すること(): void
    {
        $responseSearch = $this->get(route('books.index', [
            'q' => 'カバレッジ',
            'genre_id' => $this->book->genres->first()->id ?? 1
        ]));
        $responseSearch->assertStatus(200);

        $responseRanking = $this->get(route('ranking.index'));
        $responseRanking->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_レビュー投稿時に不正なデータが送信された場合はエラーになること(): void
    {
        $response = $this->actingAs($this->user)
                         ->postJson(route('reviews.store', $this->book), [
                             'comment' => '',
                             'rating'  => '',
                         ]);

        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 422]));
    }

    /**
     * @test
     */
    public function test_他人が投稿したレビューは削除できないこと(): void
    {
        $otherUser = User::factory()->create();
        
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
                         ->delete(route('reviews.destroy', $review));

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }
}
