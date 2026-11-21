<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLedgerEntryRequest;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Services\KhataService;

class LedgerEntryController extends Controller
{
    public function __construct(private KhataService $khata) {}

    public function store(StoreLedgerEntryRequest $request, Customer $customer)
    {
        $entry = $this->khata->postEntry($customer, $request->validated());

        $label = $entry->isUdhaar() ? 'Udhaar' : 'Payment';

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', "{$label} of Rs ".number_format((float) $entry->amount, 0).' recorded.');
    }

    public function destroy(Customer $customer, LedgerEntry $entry)
    {
        $this->authorize('update', $customer);

        abort_unless($entry->customer_id === $customer->id, 404);

        if ($entry->sale_id !== null) {
            return redirect()
                ->route('customers.show', $customer)
                ->with('error', 'This entry belongs to a sale. Void the sale instead of deleting the entry.');
        }

        $entry->delete();

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Khata entry deleted.');
    }
}
