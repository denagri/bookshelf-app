<?php

namespace Tests\Feature\Models;

use App\Models\User;
use App\Models\Review;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_ユーザーモデルの基本属性とキャストと隠蔽が正しく機能すること()
    {
        $user = new User([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'secret-password',
        ]);
        $user->email_verified_at = '2026-08-14 00:00:00';
        $user->save();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
        $this->assertTrue(Hash::check('secret-password', $user->password));

        $userArray = $user->toArray();
        $this->assertArrayNotHasKey('password', $userArray);
    }

    /**
     * @test
     */
    public function test_reviews_リレーション経由でユーザーが投稿したレビューを取得できること()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = $user->reviews()->create([
            'book_id' => $book->id,
            'comment' => 'とても勉強になりました。',
            'rating' => 5,
        ]);

        $this->assertCount(1, $user->reviews);
        $this->assertTrue($user->reviews->contains($review));
    }

    /**
     * @test
     */
    public function test_books_リレーション経由でユーザーの書籍を取得できること()
    {
        $user = User::factory()->create();

        $book = $user->books()->create([
            'title' => 'Laravel入門',
            'author' => '開発著者',
            'isbn' => '9784000000000',
            'published_date' => '2026-08-14',
        ]);

        $this->assertCount(1, $user->books);
        $this->assertEquals($book->id, $user->books->first()->id);
    }

    /**
     * @test
     */
    public function test_favoriteBooks_リレーション経由でお気に入り書籍を登録・取得できること()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->assertCount(1, $user->favoriteBooks);
        $this->assertTrue($user->favoriteBooks->contains($book));
        $this->assertNotNull($user->favoriteBooks->first()->pivot->created_at);
    }

    /**
     * @test
     */
    public function test_likedReviews_リレーション経由でいいねしたレビューを取得できること()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $user->likedReviews()->attach($review->id);

        $this->assertCount(1, $user->likedReviews);
        $this->assertTrue($user->likedReviews->contains($review));
        $this->assertNotNull($user->likedReviews->first()->pivot->created_at);
    }
}
