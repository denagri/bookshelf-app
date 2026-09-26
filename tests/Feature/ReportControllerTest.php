<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 12));
    }

    public function test_reading_report_correctly_aggregates_reading_plans_stats(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'status'      => ReadingPlanStatus::PLANNED->value ?? 'planned',
            'target_date' => '2026-09-20',
        ]);
        ReadingPlan::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'status'      => ReadingPlanStatus::READING->value ?? 'reading',
            'target_date' => '2026-09-14',
        ]);
        $otherUser = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id'     => $otherUser->id,
            'book_id'     => $book->id,
            'status'      => ReadingPlanStatus::READING->value ?? 'reading',
            'target_date' => '2026-09-13',
        ]);
        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['plans']['planned'] === 1
                && $stats['plans']['reading'] === 1
                && $stats['plans']['urgent'] === 1;
        });
    }
}
