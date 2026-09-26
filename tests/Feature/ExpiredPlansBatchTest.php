<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class ExpiredPlansBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_automatically_removes_past_reading_plans(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 12));
        $expiredPlan = ReadingPlan::factory()->create([
            'target_date' => '2026-09-11',
            'status'      => 'planned',
        ]);
        $completedPlan = ReadingPlan::factory()->create([
            'target_date' => '2026-09-11',
            'status'      => 'completed',
        ]);
        $futurePlan = ReadingPlan::factory()->create([
            'target_date' => '2026-09-13',
            'status'      => 'planned',
        ]);
        $this->artisan('app:expire-plans')->assertSuccessful();
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $expiredPlan->id,
        ]);
        $this->assertDatabaseHas('reading_plans', [
            'id'     => $completedPlan->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('reading_plans', [
            'id'     => $futurePlan->id,
            'status' => 'planned',
        ]);
    }
}
