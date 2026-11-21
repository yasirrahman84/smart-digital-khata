<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\KhataService;

class PublicStatementController extends Controller
{
    /**
     * A read-only statement the shopkeeper can share with a customer.
     * Access is by a random token, and the page is marked noindex.
     */
    public function show(string $token, KhataService $khata)
    {
        $customer = Customer::where('public_token', $token)->firstOrFail();

        $entries = $khata->statement($customer)
            ->reverse()
            ->take(20)
            ->values();

        return view('public.statement', [
            'customer' => $customer,
            'shop' => $customer->user,
            'entries' => $entries,
            'balance' => $customer->calculateBalance(),
        ]);
    }
}
