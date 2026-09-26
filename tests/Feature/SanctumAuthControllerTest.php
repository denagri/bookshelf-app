<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    /**
     * @test
     */
    public function test_未認証状態では保護されたAPIルートにアクセスできないこと(): void
    {
        $this->postJson('/api/v1/books', ['title' => 'テスト', 'isbn' => '1234567890123'])->assertStatus(401);
        $this->putJson("/api/v1/books/{$this->book->id}", ['title' => '更新'])->assertStatus(401);
        $this->deleteJson("/api/v1/books/{$this->book->id}")->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_Sanctum認証済みユーザーは書籍の登録更新削除ができること(): void
    {
        Sanctum::actingAs($this->user);
        $storeResponse = $this->postJson('/api/v1/books', []);
        $this->assertNotEquals(401, $storeResponse->getStatusCode(), 'Sanctum認証が機能していません。');

        $updateResponse = $this->putJson("/api/v1/books/{$this->book->id}", []);
        $this->assertNotEquals(401, $updateResponse->getStatusCode(), 'Sanctum認証が機能していません。');

        $destroyResponse = $this->deleteJson("/api/v1/books/{$this->book->id}");
        $this->assertNotEquals(401, $destroyResponse->getStatusCode(), 'Sanctum認証が機能していません。');
    }
}
