<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    protected $plan;
    protected $timing;

    public function __construct(ReadingPlan $plan, string $timing)
    {
        $this->plan = $plan;
        $this->timing = $timing;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subjects = [
            'three_days_before' => '【リマインダー】読書計画の期日3日前です',
            'on_due_date'       => '【本日期日】読書計画の期日当日です',
            'day_after'         => '【期間経過】読書計画の期日から1日経過しました',
            'three_days_after'  => '【未完了警告】読書計画の期日を3日超過しています',
        ];

        $bookTitle = $this->plan->book->title ?? '書籍';
        $targetDate = $this->plan->target_date ? $this->plan->target_date->format('Y-m-d') : '';

        return (new MailMessage)
            ->subject($subjects[$this->timing] ?? '【リマインダー】読書計画について')
            ->line("計画している書籍: 「{$bookTitle}」")
            ->line("目標期日: {$targetDate}")
            ->action('計画を確認する', url('/reading-plans/' . $this->plan->id))
            ->line('進捗に合わせて更新をお願いします。');
    }

    public function toArray(object $notifiable): array
    {
        $titles = [
            'three_days_before' => '読書計画リマインダー（期日3日前）',
            'on_due_date'       => '読書計画リマインダー（本日が期日）',
            'day_after'         => '読書計画リマインダー（期日1日経過）',
            'three_days_after'  => '読書計画リマインダー（期日3日超過）',
        ];

        $formattedDate = $this->plan->target_date ? $this->plan->target_date->format('m/d') : '';
        $bookTitle = $this->plan->book->title ?? '登録書籍';

        $bodies = [
            'three_days_before' => "「{$bookTitle}」の読書期日（{$formattedDate}）が近づいています。",
            'on_due_date'       => "「{$bookTitle}」は本日が読書期日です！",
            'day_after'         => "「{$bookTitle}」の読書期日（{$formattedDate}）を1日過ぎています。",
            'three_days_after'  => "「{$bookTitle}」の読書期日を3日過ぎています。進捗はどうですか？",
        ];

        return [
            'reading_plan_id' => $this->plan->id,
            'timing'          => $this->timing,
            'title'           => $titles[$this->timing] ?? '読書計画のお知らせ',
            'body'            => $bodies[$this->timing] ?? '読書計画の進捗を確認してください。',
        ];
    }
}
