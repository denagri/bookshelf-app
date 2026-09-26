<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case PLANNED = 'planned';
    case READING = 'reading';
    case COMPLETED = 'completed';

    public const Planned = self::PLANNED;
    public const Reading = self::READING;
    public const Completed = self::COMPLETED;

    public function label(): string
    {
        return match($this) {
            self::PLANNED => '計画中',
            self::READING => '読書中',
            self::COMPLETED => '読了達成',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PLANNED => 'bg-yellow-100 text-yellow-800',
            self::READING => 'bg-blue-100 text-blue-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
        };
    }
}
