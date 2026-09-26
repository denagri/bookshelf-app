<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
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
    public function test_ジャンル一覧画面に作成済みのジャンルが表示されること(): void
    {
        $genre = Genre::factory()->create(['name' => 'テスト用ジャンル']);

        $response = $this->actingAs($this->user)
                         ->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト用ジャンル');
    }

    /**
     * @test
     */
    public function test_新しいジャンルを登録でき一覧画面へリダイレクトされること(): void
    {
        $formData = [
            'name' => '新しいSF小説',
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('genres.store'), $formData);

        $this->assertDatabaseHas('genres', $formData);

        $response->assertRedirect(route('genres.index'));
    }

    /**
     * @test
     */
    public function test_ジャンル名を更新でき一覧画面へリダイレクトされること(): void
    {
        $genre = Genre::factory()->create(['name' => '古いジャンル名']);

        $updateData = [
            'name' => '更新されたジャンル名',
        ];

        $response = $this->actingAs($this->user)
                         ->put(route('genres.update', $genre), $updateData);

        $this->assertDatabaseHas('genres', $updateData);

        $response->assertRedirect(route('genres.index'));
    }

    /**
     * @test
     */
    public function test_ジャンルを削除でき一覧画面へリダイレクトされること(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)
                         ->delete(route('genres.destroy', $genre));

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id
        ]);

        $response->assertRedirect(route('genres.index'));
    }
}
