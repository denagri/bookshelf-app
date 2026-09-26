<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;

class SendReadingReminderCommand extends Command
{
    protected $signature = 'app:send-reading-reminder';
    protected $description = '読書計画の状況に応じたリマインダー通知を送信します';

    public function handle()
    {
        // 💡 修正ポイント：基準となる今日を文字列(Y-m-d)にして、時間の概念を完全に消去します
        $todayStr = Carbon::today()->format('Y-m-d');
        $today = Carbon::parse($todayStr);

        // 判定に使用する「3日前の日付文字列」「当日の文字列」「3日後（超過）の文字列」を事前に確定させます
        $threeDaysBeforeStr = Carbon::parse($todayStr)->addDays(3)->format('Y-m-d'); // 3日前判定用(期日が3日後)
        $onDueDateStr       = $todayStr;                                              // 当日判定用
        $threeDaysAfterStr  = Carbon::parse($todayStr)->subDays(3)->format('Y-m-d'); // 3日超過判定用(期日が3日前)

        // 物理ファイルを安全にロード
        $filePath = app_path('Notifications/ReadingPlanReminder.php');
        if (file_exists($filePath)) {
            require_once $filePath;
        }

        $className = '\\App\\Notifications\\ReadingPlanReminder';

        if (class_exists($className)) {
            ReadingPlan::where('status', '!=', ReadingPlanStatus::COMPLETED->value)
                ->with(['user', 'book'])
                ->lazy()
                ->each(function ($plan) use ($threeDaysBeforeStr, $onDueDateStr, $threeDaysAfterStr, $className) {
                    if (!$plan->user) return;

                    // データベースから取得した期日も Y-m-d の文字列に固定して比較します
                    $targetDateStr = Carbon::parse($plan->target_date)->format('Y-m-d');

                    $timing = match ($targetDateStr) {
                        $threeDaysBeforeStr => 'three_days_before',
                        $onDueDateStr       => 'on_due_date',
                        $threeDaysAfterStr  => 'three_days_after',
                        default             => null,
                    };

                    if ($timing) {
                        $plan->user->notify(new $className($plan, $timing));
                    }
                });
        }

        $this->info('状況に応じたリマインダー送信が完了しました。');
        return 0; 
    }
}
