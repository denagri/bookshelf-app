<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
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
    public function test_お気に入り一覧画面にアクセスできること(): void
    {
        $response = $this->actingAs($this->user)
                         ->get(route('favorites.index'));

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_書籍をお気に入り登録および解除できること(): void
    {
        $response = $this->actingAs($this->user)
                         ->post(route('favorites.toggle', $this->book));

        $this->assertDatabaseHas('book_user', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response->assertRedirect(route('books.index'));

        $response = $this->actingAs($this->user)
                         ->post(route('favorites.toggle', $this->book));

        $this->assertDatabaseMissing('book_user', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response->assertRedirect(route('books.index'));
    }
}
