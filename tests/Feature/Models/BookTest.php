<?php

namespace Tests\Feature\Models;

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 観点: 基本属性（fillable）が仕様通り正しくデータベースに保存できるか
     */
    public function test_書籍モデルの基本属性が正しく保存できること()
    {
        $user = User::factory()->create();

        $bookData = [
            'title' => 'テスト書籍タイトル',
            'author' => 'テスト著者名',
            'isbn' => '9784000000000',
            'published_date' => '2026-08-14',
            'description' => 'これはテスト用の書籍説明文です。',
            'image_url' => 'https://example.com',
            'user_id' => $user->id,
        ];

        $book = Book::create($bookData);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍タイトル',
            'isbn' => '9784000000000',
        ]);

        $this->assertEquals('テスト著者名', $book->author);
        $this->assertEquals('2026-08-14', $book->published_date);
        $this->assertEquals('これはテスト用の書籍説明文です。', $book->description);
        $this->assertEquals('https://example.com', $book->image_url);
    }

    /**
     * @test
     * 観点: userリレーション（多対1）
     */
    public function test_user_リレーション経由で登録したユーザーを取得できること()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    /**
     * @test
     * 観点: genresリレーション（多対多）およびタイムスタンプの保持
     */
    public function test_genres_リレーション経由でジャンルを登録・取得できること()
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        $this->assertCount(1, $book->genres);
        $this->assertTrue($book->genres->contains($genre));
        $this->assertNotNull($book->genres->first()->pivot->created_at);
    }

    /**
     * @test
     * 観点: reviewsリレーション（1対多）
     */
    public function test_reviews_リレーション経由で書籍に投稿されたレビューを取得できること()
    {
        $book = Book::factory()->create();

        $review = $book->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'comment' => '素晴らしい本でした。',
            'rating' => 5,
        ]);

        $this->assertCount(1, $book->reviews);
        $this->assertTrue($book->reviews->contains($review));
    }

    /**
     * @test
     * 観点: favoritedByUsersリレーション（多対多）およびタイムスタンプの保持
     */
    public function test_favoritedByUsers_リレーション経由でお気に入り登録しているユーザーを取得できること()
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $book->favoritedByUsers()->attach($user->id);

        $this->assertCount(1, $book->favoritedByUsers);
        $this->assertTrue($book->favoritedByUsers->contains($user));
        $this->assertNotNull($book->favoritedByUsers->first()->pivot->created_at);
    }
}
