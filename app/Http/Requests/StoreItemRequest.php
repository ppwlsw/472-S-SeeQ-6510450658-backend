<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'api_url' => 'required|string',
            'api_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'api_url.required' => 'API URL is required.',
            'api_url.string' => 'API URL must be a string.',
            'api_token.required' => 'API token is required.',
            'api_token.string' => 'API token must be a string.',
        ];
    }
}
