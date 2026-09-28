<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($books->isEmpty()) {
            return;
        }
        foreach ($users as $user) {
            $takeCount = min($books->count(), rand(3, 5));
            
            $favoriteBooks = $books->shuffle()->take($takeCount);
            $bookIds = $favoriteBooks->pluck('id')->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
