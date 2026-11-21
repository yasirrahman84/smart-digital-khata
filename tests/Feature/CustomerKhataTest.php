<?php

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Sale;
use App\Models\User;

it('redirects guests to the login page', function () {
    $this->get(route('customers.index'))->assertRedirect(route('login'));
});

it('lists only the signed-in user customers', function () {
    $user = User::factory()->create();
    $mine = Customer::factory()->for($user)->create();
    $other = Customer::factory()->create();

    $this->actingAs($user)
        ->get(route('customers.index'))
        ->assertOk()
        ->assertSee($mine->name)
        ->assertDontSee($other->name);
});

it('creates a customer with an opening balance', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('customers.store'), [
        'name' => 'Ali Raza',
        'phone' => '0300-1234567',
        'opening_balance' => 500,
        'is_active' => 1,
    ])->assertRedirect();

    $customer = Customer::where('name', 'Ali Raza')->firstOrFail();

    expect($customer->user_id)->toBe($user->id)
        ->and($customer->calculateBalance())->toBe(500.0);
});

it('validates the customer name', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('customers.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('records udhaar and payment entries and keeps the balance correct', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 100]);

    $this->actingAs($user)->post(route('customers.entries.store', $customer), [
        'type' => 'udhaar',
        'amount' => 250,
        'entry_date' => now()->toDateString(),
    ])->assertRedirect(route('customers.show', $customer));

    $this->actingAs($user)->post(route('customers.entries.store', $customer), [
        'type' => 'payment',
        'amount' => 150,
        'entry_date' => now()->toDateString(),
    ])->assertRedirect(route('customers.show', $customer));

    expect($customer->calculateBalance())->toBe(200.0)
        ->and($customer->ledgerEntries()->count())->toBe(2);
});

it('rejects a khata entry with a zero amount', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('customers.entries.store', $customer), [
            'type' => 'payment',
            'amount' => 0,
            'entry_date' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('amount');
});

it('blocks access to another user customer', function () {
    $customer = Customer::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('customers.show', $customer))
        ->assertForbidden();
});

it('prevents deleting a customer who still owes money', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 300]);

    $this->actingAs($user)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.show', $customer));

    expect($customer->fresh())->not->toBeNull();
});

it('soft deletes a settled customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 0]);

    $this->actingAs($user)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    expect(Customer::find($customer->id))->toBeNull()
        ->and(Customer::withTrashed()->find($customer->id))->not->toBeNull();
});

it('does not delete khata entries that belong to a sale', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $sale = Sale::factory()->for($user)->create();

    $entry = LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'sale_id' => $sale->id,
        'type' => LedgerEntry::TYPE_UDHAAR,
        'amount' => 400,
    ]);

    $this->actingAs($user)
        ->delete(route('customers.entries.destroy', [$customer, $entry]))
        ->assertRedirect(route('customers.show', $customer));

    expect($entry->fresh())->not->toBeNull();
});

it('deletes a manual khata entry', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $entry = LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'sale_id' => null,
        'type' => LedgerEntry::TYPE_PAYMENT,
        'amount' => 100,
    ]);

    $this->actingAs($user)
        ->delete(route('customers.entries.destroy', [$customer, $entry]))
        ->assertRedirect(route('customers.show', $customer));

    expect($entry->fresh())->toBeNull();
});

it('renders a printable statement', function () {
    $user = User::factory()->create(['store_name' => 'Rahman General Store']);
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 250]);

    $this->actingAs($user)
        ->get(route('customers.print', $customer))
        ->assertOk()
        ->assertSee('Rahman General Store')
        ->assertSee('Khata statement')
        ->assertSee($customer->name);
});

it('shows the shop-wide khata book', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'type' => LedgerEntry::TYPE_UDHAAR,
        'amount' => 750,
    ]);

    $this->actingAs($user)
        ->get(route('khata.index'))
        ->assertOk()
        ->assertSee($customer->name)
        ->assertSee('750');
});
