<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->count() < 5 || $books->count() < 11) return;

        $reviewDistribution = [
            1 => 3, 2 => 4, 3 => 3, 4 => 3, 5 => 2,
            6 => 3, 7 => 3, 8 => 4, 9 => 2, 10 => 3, 11 => 2
        ];

        $comments = [
            5 => ['大変勉強になりました。何度も読み返したい名著です！', '素晴らしい内容で、知人に勧めたい一冊です。', '人生のバイブルになりました。感動です。'],
            4 => ['非常に分かりやすく、実務や私生活に生かせそうです。', '内容が整理されていて、スラスラ読めました。おすすめ。', '一読の価値あり。とても実用的な視点が得られます。'],
            3 => ['内容は良いですが、少しボリュームが多く読むのに体力が要ります。', '普通に楽しめました。基礎的なおさらいには最適。', '一部賛同できない部分もありましたが、全体的には良書。']
        ];

        foreach ($books as $index => $book) {
            $bookNum = $index + 1;
            $count = $reviewDistribution[$bookNum] ?? 2;

            $reviewerUsers = $users->shuffle()->take($count);

            foreach ($reviewerUsers as $user) {
                $rating = rand(3, 5);
                $commentList = $comments[$rating];
                $comment = $commentList[array_rand($commentList)];

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => "【{$user->name}のレビュー】{$comment}",
                ]);
            }
        }
    }
}
