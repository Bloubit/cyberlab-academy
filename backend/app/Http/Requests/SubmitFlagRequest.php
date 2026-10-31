<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flag' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'flag.required' => 'Flag is required',
            'flag.string' => 'Flag must be a string',
        ];
    }
}
