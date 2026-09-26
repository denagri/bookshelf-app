<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReadingPlanRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\ReadingPlan::where('user_id', auth()->id())
                        ->where('book_id', $value)
                        ->whereIn('status', [ReadingPlanStatus::PLANNED, ReadingPlanStatus::READING])
                        ->exists();

                    if ($exists) {
                        $fail('this_book_already_has_active_plan');
                    }
                },
            ],
            'target_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.integer' => '書籍IDは整数で入力してください。',
            'book_id.exists' => '選択された書籍は存在しません。',
            'book_id.*' => 'この書籍は既に進行中の読書計画が存在します。',
            
            'target_date.required' => '期日は必須です。',
            'target_date.date' => '期日は有効な日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }
}
