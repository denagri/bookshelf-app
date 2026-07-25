<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $booksData = [
            [
                'title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014',
                'published_date' => '1905-01-01', 'genres' => ['小説'], 'desc' => '夏目漱石のデビュー作にして不朽の名作。'
            ],
            [
                'title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524',
                'published_date' => '1936-10-01', 'genres' => ['ビジネス', '自己啓発'], 'desc' => '人間関係の原則を説いた歴史的ベストセラー。'
            ],
            [
                'title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658',
                'published_date' => '2012-06-23', 'genres' => ['技術書'], 'desc' => '美しく読みやすいコードを書くための実践的ガイド。'
            ],
            [
                'title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246',
                'published_date' => '2013-08-30', 'genres' => ['ビジネス', '自己啓発'], 'desc' => '人生を成功に導くためのタイムレスな教訓。'
            ],
            [
                'title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021',
                'published_date' => '1906-04-01', 'genres' => ['小説'], 'desc' => 'ユーモアと社会風刺に満ちた国民的傑作。'
            ],
            [
                'title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712',
                'published_date' => '2016-09-08', 'genres' => ['歴史', '科学'], 'desc' => '人類の歴史を全く新しい視点から紐解く一冊。'
            ],
            [
                'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598',
                'published_date' => '2017-12-18', 'genres' => ['技術書'], 'desc' => 'プロフェッショナルとして質の高いコードを書く技術。'
            ],
            [
                'title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819',
                'published_date' => '2013-12-13', 'genres' => ['自己啓発'], 'desc' => 'アドラー心理学を対話形式で分かりやすく解説。'
            ],
            [
                'title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302',
                'published_date' => '2015-03-11', 'genres' => ['小説'], 'desc' => '売れない芸人たちの葛藤と純粋な熱量を描く芥川賞受賞作。'
            ],
            [
                'title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607',
                'published_date' => '2019-01-11', 'genres' => ['ビジネス', '科学'], 'desc' => 'データに基づき、世界の正しい姿を捉えるための思考法。'
            ],
            [
                'title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468',
                'published_date' => '2007-01-18', 'genres' => ['ビジネス', '歴史'], 'desc' => '世界経済を劇的に変えた「箱」の壮大なイノベーション史。'
            ],
        ];

        foreach ($booksData as $index => $data) {
            $num = $index + 1;

            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['published_date'],
                    'description' => $data['desc'],
                    'image_url' => "https://placehold.co/200x300/e2e8f0/475569?text={$num}",
                ]
            );

            $genreIds = Genre::whereIn('name', $data['genres'])->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
