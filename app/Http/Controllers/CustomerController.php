<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\KhataService;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerController extends Controller
{
    public function __construct(private KhataService $khata) {}

    /**
     * Customer list with search, filter and balance sorting.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $filter = (string) $request->query('filter', 'all');
        $sort = (string) $request->query('sort', 'name');

        $customers = Customer::where('user_id', $request->user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withBalance()
            ->get();

        if ($filter === 'owing') {
            $customers = $customers->filter(fn (Customer $customer) => $customer->balance > 0);
        } elseif ($filter === 'clear') {
            $customers = $customers->filter(fn (Customer $customer) => $customer->balance <= 0);
        } elseif ($filter === 'inactive') {
            $customers = $customers->where('is_active', false);
        }

        $customers = match ($sort) {
            'balance' => $customers->sortByDesc('balance'),
            'balance_asc' => $customers->sortBy('balance'),
            'recent' => $customers->sortByDesc('created_at'),
            default => $customers->sortBy(fn (Customer $customer) => mb_strtolower($customer->name)),
        };

        $customers = $this->paginate($customers->values(), $request, 15)
            ->withQueryString();

        $summary = [
            'count' => $customers->total(),
            'receivable' => round(Customer::where('user_id', $request->user()->id)->withBalance()->get()->sum('balance'), 2),
        ];

        return view('customers.index', compact('customers', 'summary', 'search', 'filter', 'sort'));
    }

    public function create()
    {
        return view('customers.create', [
            'customer' => new Customer(['is_active' => true, 'opening_balance' => 0]),
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create([
            'user_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'notes' => $request->validated('notes'),
            'opening_balance' => $request->validated('opening_balance') ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', "{$customer->name} has been added to your khata.");
    }

    public function show(Request $request, Customer $customer)
    {
        $this->authorize('view', $customer);

        $statement = $this->khata->statement($customer);

        $entries = $this->paginate($statement->reverse()->values(), $request, 20)
            ->withQueryString();

        $totals = [
            'udhaar' => round($customer->ledgerEntries()->where('type', 'udhaar')->sum('amount'), 2),
            'payment' => round($customer->ledgerEntries()->where('type', 'payment')->sum('amount'), 2),
        ];

        $recentSales = $customer->sales()
            ->notVoided()
            ->latest('sale_date')
            ->limit(5)
            ->get();

        $recentMessages = $customer->smsMessages()
            ->latest()
            ->limit(3)
            ->get();

        return view('customers.show', compact(
            'customer',
            'entries',
            'totals',
            'recentSales',
            'recentMessages',
        ) + ['balance' => $customer->calculateBalance()]);
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'notes' => $request->validated('notes'),
            'opening_balance' => $request->validated('opening_balance') ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer details updated.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        if ($customer->calculateBalance() > 0) {
            return redirect()
                ->route('customers.show', $customer)
                ->with('error', 'This customer still owes money. Settle the balance before removing them.');
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', "{$customer->name} was removed from your khata.");
    }

    /**
     * Send an SMS payment reminder to this customer.
     */
    public function remind(Request $request, Customer $customer, ReminderService $reminders)
    {
        $this->authorize('update', $customer);

        if (! $customer->phone) {
            return back()->with('error', 'Add a phone number before sending a reminder.');
        }

        if ($customer->calculateBalance() <= 0) {
            return back()->with('error', 'This customer has no outstanding balance.');
        }

        if (! $request->user()->sms_enabled) {
            return back()->with('error', 'SMS reminders are turned off in settings.');
        }

        $message = $reminders->send($customer);

        if ($message->status === 'failed') {
            return back()->with('error', 'Reminder failed: '.$message->error);
        }

        $verb = $message->status === 'logged' ? 'logged' : 'sent';

        return back()->with('success', "Reminder {$verb} for {$customer->name}.");
    }

    /**
     * Printable statement for a customer.
     */
    public function print(Customer $customer)
    {
        $this->authorize('view', $customer);

        $entries = $this->khata->statement($customer);

        return view('customers.print', [
            'customer' => $customer,
            'entries' => $entries,
            'balance' => $customer->calculateBalance(),
        ]);
    }

    /**
     * Paginate an in-memory collection (balances are computed in PHP).
     *
     * @param  Collection<int, mixed>  $items
     */
    private function paginate(Collection $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }
}
