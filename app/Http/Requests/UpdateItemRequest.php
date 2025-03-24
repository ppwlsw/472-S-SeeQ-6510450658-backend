<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'api_url' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
       return [
           'api_url.string' => 'API URL must be a string.',
       ];
    }
}
