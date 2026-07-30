<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $genre = $this->route('genre');
        $genreId = $genre ? ($genre->id ?? $genre) : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $genreId ? "unique:genres,name,{$genreId}" : 'unique:genres,name',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ジャンル名は必須です。',
            'name.max'      => 'ジャンル名は255文字以内で入力してください。',
            'name.string'   => 'ジャンル名は文字列で入力してください。',
            'name.unique'   => 'そのジャンル名は既に使用されています。',
        ];
    }
}
