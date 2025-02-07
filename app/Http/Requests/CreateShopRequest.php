<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShopRequest extends FormRequest
{
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
            'latitude' => 'nullable|double',
            'longitude' => 'nullable|double',
            'image_url' => 'nullable|url',
        ];
    }
}
