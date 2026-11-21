<?php

namespace App\Http\Requests;

use App\Models\LedgerEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLedgerEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('customer'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([LedgerEntry::TYPE_UDHAAR, LedgerEntry::TYPE_PAYMENT])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'entry_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Enter an amount greater than zero.',
            'entry_date.before_or_equal' => 'The entry date cannot be in the future.',
        ];
    }
}
