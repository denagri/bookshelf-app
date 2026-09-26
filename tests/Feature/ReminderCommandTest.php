<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
        Notification::fake();
    }

    /**
     * @test
     */
    public function test_状況に応じたリマインダー通知が対象ユーザーに送信されること(): void
    {
        $incompleteStatusStr = 'reading'; 

        ReadingPlan::create([
            'user_id'     => $this->user->id,
            'book_id'     => $this->book->id,
            'target_date' => Carbon::today()->addDays(3)->format('Y-m-d'),
            'status'      => $incompleteStatusStr, 
        ]);

        ReadingPlan::create([
            'user_id'     => $this->user->id,
            'book_id'     => $this->book->id,
            'target_date' => Carbon::today()->format('Y-m-d'),
            'status'      => $incompleteStatusStr,
        ]);

        ReadingPlan::create([
            'user_id'     => $this->user->id,
            'book_id'     => $this->book->id,
            'target_date' => Carbon::today()->subDays(3)->format('Y-m-d'),
            'status'      => $incompleteStatusStr,
        ]);
        $this->artisan('app:send-reading-reminder')
             ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function test_通知対象外の条件ではリマインダー通知が送信されないこと(): void
    {
        $incompleteStatusStr = 'reading';
        ReadingPlan::create([
            'user_id'     => $this->user->id,
            'book_id'     => $this->book->id,
            'target_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'status'      => $incompleteStatusStr,
        ]);

        ReadingPlan::create([
            'user_id'     => $this->user->id,
            'book_id'     => $this->book->id,
            'target_date' => Carbon::today()->format('Y-m-d'),
            'status'      => 'completed',
        ]);

        $this->artisan('app:send-reading-reminder')
             ->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
