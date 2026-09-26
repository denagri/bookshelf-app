<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function test_書籍登録画面にアクセスできること(): void
    {
        $response = $this->actingAs($this->user)
                         ->get(route('books.create'));

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_書籍を新規登録でき一覧画面へリダイレクトされること(): void
    {
        $genre1 = Genre::factory()->create(['name' => '技術書']);
        $genre2 = Genre::factory()->create(['name' => 'プログラミング']);

        $formData = [
            'title'          => 'リーダブルコード',
            'author'         => 'Dustin Boswell',
            'isbn'           => '9784873115658',
            'published_date' => '2012-06-01',
            'description'    => '読みやすいコードの書き方。',
            'image_url'      => 'https://example.com',
            'genres'      => [$genre1->id, $genre2->id],
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('books.store'), $formData);
        $response->dumpSession();

        unset($formData['genres']);
        $this->assertDatabaseHas('books', array_merge($formData, [
            'user_id' => $this->user->id,
        ]));

        $createdBook = Book::latest('id')->first();
        $this->assertCount(2, $createdBook->genres);
        $this->assertTrue($createdBook->genres->contains($genre1));

        $response->assertRedirect(route('books.show', $createdBook));
    }

    /**
     * @test
     */
    public function test_書籍一覧画面に登録済みの書籍が表示されること(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'title'   => '表示テスト用タイトル',
            'author'  => '表示テスト用著者'
        ]);

        $response = $this->actingAs($this->user)
                         ->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('表示テスト用タイトル');
        $response->assertSee('表示テスト用著者');
    }

    /**
     * @test
     */
    public function test_書籍詳細画面で書籍情報と紐付くジャンルやレビューが表示されること(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);
        $genre = Genre::factory()->create(['name' => 'SF小説']);
        $book->genres()->attach($genre->id);

        $review = $book->reviews()->create([
            'user_id' => $this->user->id,
            'comment' => '最高に面白い一冊でした！',
            'rating'  => 5,
        ]);

        $response = $this->actingAs($this->user)
                         ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertSee('SF小説');
        $response->assertSee('最高に面白い一冊でした！');
    }

    /**
     * @test
     */
    public function test_書籍編集画面にアクセスできること(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->get(route('books.edit', $book));

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_書籍データを更新でき詳細画面へリダイレクトされること(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'title'   => '古いタイトル'
        ]);

        $genre = Genre::factory()->create(['name' => '更新後のジャンル']);

        $updateData = [
            'title'          => '新しいタイトル',
            'author'         => '新しい著者名',
            'isbn'           => '9784000000000',
            'published_date' => '2026-08-15',
            'description'    => '更新された説明文。',
            'image_url'      => 'https://example.com',
            'genres'         => [$genre->id],
        ];

        $response = $this->actingAs($this->user)
                         ->put(route('books.update', $book), $updateData);
        $response->dumpSession();

        unset($updateData['genres']);
        $this->assertDatabaseHas('books', array_merge($updateData, [
            'id' => $book->id,
        ]));

        $response->assertRedirect(route('books.show', $book));
    }


    /**
     * @test
     */
    public function test_書籍データを削除でき一覧画面へリダイレクトされること(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->delete(route('books.destroy', $book));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);

        $response->assertRedirect(route('books.index'));
    }
}
