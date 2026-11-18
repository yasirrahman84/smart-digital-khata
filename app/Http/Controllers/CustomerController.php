<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $customers = Customer::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        Customer::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'opening_balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer added successfully!');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $this->authorizeCustomer($customer);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        $this->authorizeCustomer($customer);

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'opening_balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $this->authorizeCustomer($customer);

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    /**
     * Make sure the customer belongs to the logged-in user.
     */
    private function authorizeCustomer(Customer $customer)
    {
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }
    }
}