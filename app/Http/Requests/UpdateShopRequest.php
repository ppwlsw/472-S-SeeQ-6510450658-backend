<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'address' => 'nullable|string|min:3|max:255',
            'phone' => 'nullable|string|min:3|max:255',
            'description' => 'nullable|string|min:3|max:255',
            'image_url' => 'nullable|url',
            'is_open' => 'required|boolean',
            'approve_status' => 'required|boolean|in:P,A,R',
            'latitude' => 'nullable|double',
            'longitude' => 'nullable|double',
        ];
    }
}
