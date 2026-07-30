<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'author'       => $this->author,
            'description'  => $this->description,
            'published_date' => $this->published_date,
            'image_url'    => $this->image_url,
            'genres' => $this->genres->map(function ($genre) {
                return [
                    'id'   => $genre->id,
                    'name' => $genre->name,
                ];
            }),
            'metrics' => [
                'average_rating' => round((float) ($this->reviews_avg_rating ?? 0), 1),
                'review_count'   => (int) ($this->reviews_count ?? 0),
            ],
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
