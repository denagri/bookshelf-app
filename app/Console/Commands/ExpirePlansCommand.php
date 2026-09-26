<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class ExpirePlansCommand extends Command
{
    protected $signature = 'app:expire-plans';
    protected $description = '期限切れの未完了読書計画を自動的にクリーンアップ（削除）します';

    public function handle()
    {
        // 期日（target_date）が今日より前（過去）で、まだ「completed（読了）」になっていない計画を抽出して一括削除
        ReadingPlan::where('target_date', '<', Carbon::today())
            ->where('status', '!=', 'completed')
            ->lazy()
            ->each(function ($plan) {
                $plan->delete(); // データベースから削除（または論理削除）
            });

        $this->info('期限切れの未完了読書計画を削除しました。');
        return Command::SUCCESS;
    }
}
