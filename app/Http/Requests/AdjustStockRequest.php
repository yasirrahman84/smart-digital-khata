<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['in', 'out', 'set'])],
            'quantity' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('mode') !== 'set' && (float) $this->input('quantity') <= 0) {
                    $validator->errors()->add('quantity', 'Enter a quantity greater than zero.');
                }
            },
        ];
    }
}
