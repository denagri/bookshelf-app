<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPost = $this->isMethod('post');

        return [
            'title'          => [$isPost ? 'required' : 'sometimes', 'string', 'max:255'],
            'author'         => [$isPost ? 'required' : 'sometimes', 'string', 'max:255'],
            'genre_id'       => [$isPost ? 'required' : 'sometimes', 'exists:genres,id'],
            'isbn'           => [$isPost ? 'required' : 'sometimes', 'string', 'max:255'],
            'published_date' => [$isPost ? 'required' : 'sometimes', 'date_format:Y-m-d'],
            'description'    => ['nullable', 'string'],
        ];
    }
}
