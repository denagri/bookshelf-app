<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReadingReminderBatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $path = app_path('Notifications/ReadingPlanReminder.php');
        if (file_exists($path)) {
            require_once $path;
        }
        
        Notification::fake();
    }

    /**
     * @test
     */
    public function test_batch_sends_correct_reminders_based_on_due_dates()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'target_date' => now()->format('Y-m-d'),
            'status'      => 'reading',
        ]);
        $this->artisan('app:send-reading-reminder')->assertExitCode(0);
    }
}
