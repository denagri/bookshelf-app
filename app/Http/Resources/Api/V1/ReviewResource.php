<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'reviewer_name' => $this->reviewer_name,
            'rating'        => $this->rating,
            'comment'       => $this->comment,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
