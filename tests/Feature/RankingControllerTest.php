<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /**
     * テスト前の初期化（ユーザーを準備）
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * @test
     * 観点: ランキング画面の表示
     */
    public function test_ランキング画面にアクセスできること(): void
    {
        $response = $this->actingAs($this->user)
                         ->get(route('ranking.index'));

        $response->assertStatus(200);
    }
}

