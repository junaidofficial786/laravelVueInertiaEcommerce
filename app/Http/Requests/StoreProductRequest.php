<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'images' => ['array'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}


