<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_type' => 'required|in:multiple_choice,single_choice,true_false,fill_blank,code,essay',
            'question_text' => 'required|string',
            'question_html' => 'nullable|string',
            'code_snippet' => 'nullable|string',
            'code_language' => 'nullable|string|max:50',
            'explanation' => 'nullable|string',
            'points' => 'nullable|numeric|min:0',
            'difficulty' => 'nullable|in:easy,medium,hard,expert',
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ];
    }
}
