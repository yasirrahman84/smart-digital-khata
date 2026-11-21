<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable', 'string', 'max:64',
                Rule::unique('products', 'sku')->where('user_id', $this->user()->id),
            ],
            'unit' => ['required', 'string', 'max:20'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'stock_qty' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter the product name.',
            'sku.unique' => 'You already have a product with this SKU.',
        ];
    }
}
