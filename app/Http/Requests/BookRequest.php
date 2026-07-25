<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $bookId = $this->route('book') ? $this->route('book')->id : null;

        return [
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'isbn'           => [
                'required',
                'string',
                'size:13',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'published_date' => 'required|date',
            'genres'         => 'required|array',
            'genres.*'       => 'exists:genres,id',
            'description'    => 'nullable|string|max:255',
            'image_url'      => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => ':attributeを入力してください。',
            'isbn.size'       => 'ISBNは13桁で入力してください。',
            'max'             => ':attributeは:max文字以内で入力してください。',
            'date'            => '有効な日付形式で入力してください。',
            'url'             => '有効なURL形式で入力してください。',
            'isbn.unique'     => 'ISBNは既に使用されています。',
            'genres.required' => 'ジャンルは一つ以上選択してください。',
            'genres.array'    => 'ジャンルは配列で入力してください。',
            'genres.*.exists' => '選択されたジャンルは存在しません。',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'          => 'タイトル',
            'author'         => '著者名',
            'isbn'           => 'ISBN',
            'published_date' => '出版日',
            'description'    => '説明',
            'image_url'      => '画像URL',
            'genres'         => 'ジャンル',
        ];
    }
}
