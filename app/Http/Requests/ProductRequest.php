<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'url', 'max:255'],
            'description' => ['required', 'string'],
            'keywords' => ['required', 'string'],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A product name is required.',
            'name.max' => 'The product name may not be greater than 255 characters.',
            'url.required' => 'A product URL is required.',
            'url.url' => 'The product URL must be a valid URL.',
            'url.max' => 'The product URL may not be greater than 255 characters.',
            'description.required' => 'A product description is required.',
            'keywords.required' => 'Product keywords are required.',
        ];
    }
}
