<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 観点: 基本属性（fillable）が正しく保存できるか
     */
    public function test_ジャンルモデルの基本属性が正しく保存できること()
    {
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $this->assertDatabaseHas('genres', [
            'name' => 'プログラミング',
        ]);
        $this->assertEquals('プログラミング', $genre->name);
    }

    /**
     * @test
     * 観点: booksリレーション（多対多）およびタイムスタンプの保持
     */
    public function test_books_リレーション経由で属する書籍を取得できること()
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $this->assertCount(1, $genre->books);
        $this->assertTrue($genre->books->contains($book));
        $this->assertNotNull($genre->books->first()->pivot->created_at);
    }
}
