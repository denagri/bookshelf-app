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

        $comments = [
            5 => ['大変勉強になりました。何度も読み返したい名著です！', '素晴らしい内容で、知人に勧めたい一冊です。', '人生のバイブルになりました。感動です。'],
            4 => ['非常に分かりやすく、実務や私生活に生かせそうです。', '内容が整理されていて、スラスラ読めました。おすすめ。', '一読の価値あり。とても実用的な視点が得られます。'],
            3 => ['内容は良いですが、少しボリュームが多く読むのに体力が要ります。', '普通に楽しめました。基礎的なおさらいには最適。', '一部賛同できない部分もありましたが、全体的には良書。'],
            2 => ['内容は悪くないですが、少し物足りなさを感じました。', '参考になる部分もありましたが、全体的に惜しい仕上がりです。', '少しテーマや主張が偏っている印象を受けました。'],
            1 => ['期待していた内容とはかなり違っていました。', '自分には合わなかったようで、途中で退屈してしまいました。', '文章の表現が少し読みづらく、内容が頭に入りにくかったです。']
        ];

        foreach ($books as $index => $book) {
            $count = rand(2, 4);

            $reviewerUsers = $users->shuffle()->take($count);

            foreach ($reviewerUsers as $user) {
                $rating = rand(1, 5);
                
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
