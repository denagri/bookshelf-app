<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Book $book;
    private ReadingPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->book = Book::factory()->create();
        $this->plan = ReadingPlan::create([
            'user_id'     => $this->user->id,
            'book_id'     => $this->book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
            'status'      => ReadingPlanStatus::READING->value,
        ]);
    }

    /**
     * @test
     */
    public function test_user_can_create_reading_plan_with_valid_data(): void
    {
        $newBook = Book::factory()->create();
        $response = $this->actingAs($this->user)
                         ->post(route('reading-plans.store'), [
                             'book_id'     => $newBook->id,
                             'target_date' => now()->addDays(10)->format('Y-m-d'),
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reading_plans', ['book_id' => $newBook->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     */
    public function test_cannot_create_duplicate_active_reading_plan(): void
    {
        $response = $this->actingAs($this->user)
                         ->post(route('reading-plans.store'), [
                             'book_id'     => $this->book->id,
                             'target_date' => now()->addDays(10)->format('Y-m-d'),
                         ]);

        $response->assertSessionHasErrors(['book_id']);
    }

    /**
     * @test
     */
    public function test_cannot_update_other_users_reading_plan(): void
    {
        $response = $this->actingAs($this->otherUser)
                         ->put(route('reading-plans.update', $this->plan), [
                             'target_date' => now()->addDays(20)->format('Y-m-d'),
                             'status'      => ReadingPlanStatus::COMPLETED->value,
                         ]);

        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    /**
     * @test
     */
    public function test_読書計画の編集画面表示と更新と削除が正常に処理できること(): void
    {
        $responseEdit = $this->actingAs($this->user)->get(route('reading-plans.edit', $this->plan));
        $responseEdit->assertStatus(200);
        $responseUpdate = $this->actingAs($this->user)
                               ->put(route('reading-plans.update', $this->plan), [
                                   'target_date' => now()->addDays(15)->format('Y-m-d'),
                                   'status'      => ReadingPlanStatus::READING->value,
                               ]);
        $responseUpdate->assertRedirect();
        $responseDelete = $this->actingAs($this->user)->delete(route('reading-plans.destroy', $this->plan));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('reading_plans', ['id' => $this->plan->id]);
    }

    /**
     * @test
     */
    public function test_読書計画を完了状態に移行できること(): void
    {
        $response = $this->actingAs($this->user)
                         ->post(route('reading-plans.complete', $this->plan));

        $response->assertRedirect();
        $this->assertDatabaseHas('reading_plans', [
            'id'     => $this->plan->id,
            'status' => ReadingPlanStatus::COMPLETED->value,
        ]);
    }
}
