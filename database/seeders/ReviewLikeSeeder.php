<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = Review::all();
        $users = User::all();

        foreach ($reviews as $review) {
            $otherUsers = $users->where('id', '!=', $review->user_id);

            $likeUsers = $otherUsers->shuffle()->take(rand(0, 3));
            $userIds = $likeUsers->pluck('id')->toArray();

            $review->likedByUsers()->syncWithoutDetaching($userIds);
        }
    }
}
