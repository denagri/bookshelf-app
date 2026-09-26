<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
        /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $today = \Carbon\Carbon::today();

            \App\Models\ReadingPlan::where('status', '!=', 'completed')
                ->where('target_date', '<', $today)
                ->update(['status' => 'expired']);

            $dates = [
                'three_days_before' => \Carbon\Carbon::today()->addDays(3)->toDateString(),
                'on_due_date'       => \Carbon\Carbon::today()->toDateString(),
                'day_after'         => \Carbon\Carbon::today()->subDay()->toDateString(),
                'three_days_after'  => \Carbon\Carbon::today()->subDays(3)->toDateString(),
            ];

            foreach ($dates as $timingKey => $dateValue) {
                $plans = \App\Models\ReadingPlan::where('status', '!=', 'completed')
                    ->whereDate('target_date', $dateValue)
                    ->with(['user', 'book'])
                    ->get();

                foreach ($plans as $plan) {
                    if ($plan->user) {
                        $plan->user->notify(new \App\Notifications\ReadingPlanReminder($plan, $timingKey));
                    }
                }
            }
         })->dailyAt('20:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
