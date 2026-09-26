<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userA = User::firstOrCreate(
            ['email' => 'yamada@example.com'],
            ['name' => '山田太郎', 'password' => bcrypt('password')]
        );

        $userB = User::firstOrCreate(
            ['email' => 'suzuki@example.com'],
            ['name' => '鈴木次郎', 'password' => bcrypt('password')]
        );

        $book1 = Book::create([
            'title' => '入門Laravel', 'author' => 'ララベル太郎', 'isbn' => '9784000000001', 'user_id' => $userA->id
        ]);
        $book2 = Book::create([
            'title' => '達人プログラマー', 'author' => 'プログラミング次郎', 'isbn' => '9784000000002', 'user_id' => $userA->id
        ]);
        $book3 = Book::create([
            'title' => 'クリーンコード', 'author' => 'コード三郎', 'isbn' => '9784000000003', 'user_id' => $userA->id
        ]);
        $bookB = Book::create([
            'title' => '他人の本（検証用）', 'author' => 'テスト著者', 'isbn' => '9784000000004', 'user_id' => $userB->id
        ]);

        $today = Carbon::today();

        ReadingPlan::create([
            'user_id' => $userA->id,
            'book_id' => $book1->id,
            'target_date' => $today->copy()->addDays(3),
            'completed_at' => null,
            'status' => ReadingPlanStatus::READING,
        ]);

        ReadingPlan::create([
            'user_id' => $userA->id,
            'book_id' => $book2->id,
            'target_date' => $today->copy(),
            'completed_at' => null,
            'status' => ReadingPlanStatus::READING,
        ]);

        ReadingPlan::create([
            'user_id' => $userA->id,
            'book_id' => $book3->id,
            'target_date' => $today->copy()->subDays(1),
            'completed_at' => null,
            'status' => ReadingPlanStatus::READING, 
        ]);

        ReadingPlan::create([
            'user_id' => $userA->id,
            'book_id' => $book1->id,
            'target_date' => $today->copy()->subDays(3),
            'completed_at' => null,
            'status' => ReadingPlanStatus::READING,
        ]);

        ReadingPlan::create([
            'user_id' => $userB->id,
            'book_id' => $bookB->id,
            'target_date' => $today->copy()->addDays(3),
            'completed_at' => null,
            'status' => ReadingPlanStatus::READING,
        ]);
    }
}
