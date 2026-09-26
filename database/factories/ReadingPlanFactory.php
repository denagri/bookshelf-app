<?php

namespace Database\Factories;

use App\Models\ReadingPlan;
use App\Models\Book;
use App\Models\User;
use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    protected $model = ReadingPlan::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'book_id'      => Book::factory(),
            'target_date'  => \Carbon\Carbon::instance($this->faker->dateTimeBetween('now', '+3 months')),
            'completed_at' => null,
            'status'       => ReadingPlanStatus::PLANNED ?? 'planned',
        ];
    }
}
