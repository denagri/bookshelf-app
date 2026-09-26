<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_未認証のユーザーでも書籍一覧APIにはアクセスできること(): void
    {
        Book::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_未認証で書籍登録APIにアクセスした場合は401エラーになること(): void
    {
        $formData = [
            'title'  => 'APIテスト本',
            'author' => 'テスト著者',
        ];

        $response = $this->postJson('/api/v1/books', $formData);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_認証済みのユーザーは書籍登録APIから新しい書籍を登録できること(): void
    {

        $genre = \App\Models\Genre::factory()->create();

        $uniqueIsbn = (string)rand(1000000000000, 9999999999999);

        Sanctum::actingAs($this->user);

        $formData = [
            'title'          => 'APIテスト本',
            'author'         => 'テスト著者',
            'isbn'           => $uniqueIsbn,
            'published_date' => '2026-08-19',
            'description'    => 'API経由での登録テストです。',
            'image_url'      => 'https://example.com',
            'genre_id'       => $genre->id,
        ];

        $response = $this->postJson('/api/v1/books', $formData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title'   => 'APIテスト本',
            'isbn'    => $uniqueIsbn,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_クエリパラメータqによるタイトルと著者名のあいまい検索ができること(): void
    {
        Book::factory()->create(['user_id' => $this->user->id, 'title' => 'Laravel実践開発入門', 'author' => '山田太郎']);
        Book::factory()->create(['user_id' => $this->user->id, 'title' => 'Ruby on Rails基礎', 'author' => '佐藤次郎']);

        $response = $this->getJson('/api/v1/books?q=Laravel');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Laravel実践開発入門');

        $response = $this->getJson('/api/v1/books?q=佐藤');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Ruby on Rails基礎');
    }

    /** @test */
    public function test_クエリパラメータgenre_idによるジャンル絞り込みができること(): void
    {
        $genreSf = \App\Models\Genre::factory()->create();
        $genreTech = \App\Models\Genre::factory()->create();

        $sfBook = Book::factory()->create(['user_id' => $this->user->id]);
        $sfBook->genres()->attach($genreSf->id);

        $techBook = Book::factory()->create(['user_id' => $this->user->id]);
        $techBook->genres()->attach($genreTech->id);

        $response = $this->getJson("/api/v1/books?genre_id={$genreSf->id}");
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.id', $sfBook->id);
    }

    /** @test */
    public function test_他人が登録した書籍は更新できずポリシーにより403エラーになること(): void
    {
        $otherUser = User::factory()->create();
        $otherBook = Book::factory()->create(['user_id' => $otherUser->id, 'title' => '他人の本']);

        \Laravel\Sanctum\Sanctum::actingAs($this->user);

        $updateData = [
            'title'          => '勝手にタイトル変更',
            'author'         => 'ハッカー',
            'isbn'           => (string)rand(1000000000000, 9999999999999),
            'published_date' => '2026-08-19',
        ];

        $response = $this->putJson("/api/v1/books/{$otherBook->id}", $updateData);
        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $otherBook->id, 'title' => '他人の本']);
    }

    /** @test */
    public function test_他人が登録した書籍は削除できずポリシーにより403エラーになること(): void
    {
        $otherUser = User::factory()->create();
        $otherBook = Book::factory()->create(['user_id' => $otherUser->id]);

        \Laravel\Sanctum\Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/v1/books/{$otherBook->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $otherBook->id]);
    }

    /** @test */
    public function test_13桁のISBNを送信するとGoogleBooksAPIをモックして書籍データを返すこと(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'APIモックタイトル',
                            'authors' => ['モック著者1', 'モック著者2'],
                            'description' => 'モックされた説明文。',
                        ]
                    ]
                ]
            ], 200)
        ]);

        $isbn = '9784873115658';
        \Laravel\Sanctum\Sanctum::actingAs($this->user);
        
        $response = $this->getJson("/api/v1/books/isbn/{$isbn}");

        $response->assertStatus(200)
                 ->assertJson([
                     'title'  => 'APIモックタイトル',
                     'author' => 'モック著者1, モック著者2', 
                     'description' => 'モックされた説明文。',
                 ]);
    }

    /** @test */
    public function test_ISBNの桁数が13桁でない場合はバリデーションにより400エラーを返すこと(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->user);
        $invalidIsbn = '12345';

        $response = $this->getJson("/api/v1/books/isbn/{$invalidIsbn}");

        $response->assertStatus(400)
                 ->assertJson([
                     'message' => 'ISBNは13桁で入力してください。'
                 ]);
    }

    /** @test */
    public function test_クエリパラメータsortによる出版日やレビュー平均点数でのソートが正しく機能すること(): void
    {
        $oldBook = Book::factory()->create(['user_id' => $this->user->id, 'published_date' => '2020-01-01', 'title' => '古い本']);
        $newBook = Book::factory()->create(['user_id' => $this->user->id, 'published_date' => '2026-01-01', 'title' => '新しい本']);
        $newBook->reviews()->create(['user_id' => $this->user->id, 'rating' => 5, 'comment' => '最高']);
        $oldBook->reviews()->create(['user_id' => $this->user->id, 'rating' => 2, 'comment' => '微妙']);
        $response = $this->getJson('/api/v1/books?sort=published_date_desc');
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $newBook->id);
        $response->assertJsonPath('data.1.id', $oldBook->id);
        $response = $this->getJson('/api/v1/books?sort=rating_desc');
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $newBook->id);
        $response->assertJsonPath('data.1.id', $oldBook->id);
    }
    /** @test */
    public function test_レビューを含む書籍詳細APIが適切なリソース形式で取得できること(): void
    {
        $user = \App\Models\User::factory()->create();
        $book = \App\Models\Book::factory()->create();
        
        \App\Models\Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 5,
            'comment' => 'リソース検証用のコメント'
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");
        $response->assertStatus(200);
    }

}
