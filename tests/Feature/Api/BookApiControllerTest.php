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
}
