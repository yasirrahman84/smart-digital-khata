<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sale::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'payment_method' => ['required', Rule::in([Sale::PAYMENT_CASH, Sale::PAYMENT_UDHAAR])],
            'customer_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('payment_method') === Sale::PAYMENT_UDHAAR) {
                    $ownsCustomer = Customer::where('user_id', $this->user()->id)
                        ->whereKey($this->input('customer_id'))
                        ->exists();

                    if (! $ownsCustomer) {
                        $validator->errors()->add('customer_id', 'Choose one of your customers for an udhaar sale.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one product to the sale.',
            'items.min' => 'Add at least one product to the sale.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
