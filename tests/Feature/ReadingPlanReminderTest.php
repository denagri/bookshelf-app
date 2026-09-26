<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ReadingPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'テスト対象書籍']);
        
        $this->plan = ReadingPlan::create([
            'user_id'      => $this->user->id,
            'book_id'      => $book->id,
            'target_date'  => Carbon::create(2026, 8, 14),
            'status'       => 'reading',
            'completed_at' => null,
        ]);
    }

    /**
     * @test
     */
    public function test_全ての通知タイミングでメッセージと配列が仕様通り生成されること(): void
    {
        $timings = ['three_days_before', 'on_due_date', 'day_after', 'three_days_after'];

        foreach ($timings as $timing) {
            $notification = new ReadingPlanReminder($this->plan, $timing);
            $this->assertEquals(['mail', 'database'], $notification->via($this->user));
            $mailMessage = $notification->toMail($this->user);
            $this->assertNotNull($mailMessage->subject);
            $this->assertStringContainsString('テスト対象書籍', $mailMessage->introLines[0]);
            $arrayData = $notification->toArray($this->user);
            $this->assertEquals($this->plan->id, $arrayData['reading_plan_id']);
            $this->assertEquals($timing, $arrayData['timing']);
            $this->assertArrayHasKey('title', $arrayData);
            $this->assertArrayHasKey('body', $arrayData);
            $this->assertStringContainsString('テスト対象書籍', $arrayData['body']);
        }
    }

    /**
     * @test
     */
    public function test_未定義のタイミングではデフォルトの文言が適用されること(): void
    {
        $notification = new ReadingPlanReminder($this->plan, 'unknown_timing');

        $mailMessage = $notification->toMail($this->user);
        $this->assertEquals('【リマインダー】読書計画について', $mailMessage->subject);

        $arrayData = $notification->toArray($this->user);
        $this->assertEquals('読書計画のお知らせ', $arrayData['title']);
    }
}
