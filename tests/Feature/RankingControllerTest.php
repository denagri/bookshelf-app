<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
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
    public function test_ランキング画面にアクセスできること(): void
    {
        $response = $this->actingAs($this->user)
                         ->get(route('ranking.index'));

        $response->assertStatus(200);
    }
}

