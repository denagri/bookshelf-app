<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required'  => '評価を選択してください。',
            'comment.required' => 'コメントは必須です。',
            'comment.max'      => 'コメントは1000文字以内で入力してください。',
        ];
    }
}
