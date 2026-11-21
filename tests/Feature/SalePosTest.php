<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

it('renders the point of sale with active products', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 10]);
    $archived = Product::factory()->for($user)->create(['is_active' => false]);

    $this->actingAs($user)
        ->get(route('sales.create'))
        ->assertOk()
        ->assertSee($product->name)
        ->assertDontSee($archived->name);
});

it('records a cash sale and reduces stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create([
        'stock_qty' => 10,
        'sale_price' => 100,
        'low_stock_threshold' => 1,
    ]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100],
        ],
        'discount' => 50,
        'payment_method' => 'cash',
    ])->assertRedirect();

    $sale = Sale::firstOrFail();

    expect((float) $sale->subtotal)->toBe(200.0)
        ->and((float) $sale->discount)->toBe(50.0)
        ->and((float) $sale->total)->toBe(150.0)
        ->and($sale->invoice_no)->toStartWith('INV-')
        ->and($sale->ledgerEntries()->count())->toBe(0)
        ->and((float) $product->fresh()->stock_qty)->toBe(8.0)
        ->and($product->stockMovements()->count())->toBe(1);
});

it('records an udhaar sale on the customer khata', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 100]);
    $product = Product::factory()->for($user)->create(['stock_qty' => 10, 'sale_price' => 250]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 250],
        ],
        'payment_method' => 'udhaar',
        'customer_id' => $customer->id,
    ])->assertRedirect();

    $sale = Sale::firstOrFail();

    expect($customer->calculateBalance())->toBe(600.0)
        ->and($sale->ledgerEntries()->count())->toBe(1)
        ->and((float) $sale->ledgerEntries()->first()->amount)->toBe(500.0);
});

it('requires a customer for an udhaar sale', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 10]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100],
        ],
        'payment_method' => 'udhaar',
    ])->assertSessionHasErrors('customer_id');

    expect(Sale::count())->toBe(0);
});

it('rejects a sale that exceeds available stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 3, 'name' => 'Sugar 1kg']);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100],
        ],
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('items');

    expect(Sale::count())->toBe(0)
        ->and((float) $product->fresh()->stock_qty)->toBe(3.0);
});

it('rejects selling another shop product', function () {
    $user = User::factory()->create();
    $foreign = Product::factory()->create(['stock_qty' => 10]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $foreign->id, 'quantity' => 1, 'unit_price' => 100],
        ],
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('items');

    expect(Sale::count())->toBe(0);
});

it('voids a sale, restores stock and reverses the khata entry', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 0]);
    $product = Product::factory()->for($user)->create(['stock_qty' => 10, 'sale_price' => 100]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 4, 'unit_price' => 100],
        ],
        'payment_method' => 'udhaar',
        'customer_id' => $customer->id,
    ]);

    $sale = Sale::firstOrFail();

    expect((float) $product->fresh()->stock_qty)->toBe(6.0)
        ->and($customer->calculateBalance())->toBe(400.0);

    $this->actingAs($user)
        ->post(route('sales.void', $sale))
        ->assertRedirect(route('sales.show', $sale));

    expect($sale->fresh()->isVoided())->toBeTrue()
        ->and((float) $product->fresh()->stock_qty)->toBe(10.0)
        ->and($customer->calculateBalance())->toBe(0.0)
        ->and($sale->ledgerEntries()->count())->toBe(0);
});

it('excludes voided sales from the revenue summary', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 20, 'sale_price' => 100]);

    foreach ([2, 3] as $quantity) {
        $this->actingAs($user)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => 100],
            ],
            'payment_method' => 'cash',
        ]);
    }

    $first = Sale::oldest('id')->first();
    $this->actingAs($user)->post(route('sales.void', $first));

    $this->actingAs($user)
        ->get(route('sales.index'))
        ->assertOk()
        ->assertSee('300');
});

it('renders a receipt', function () {
    $user = User::factory()->create(['store_name' => 'Rahman General Store']);
    $product = Product::factory()->for($user)->create(['stock_qty' => 10, 'sale_price' => 100]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100],
        ],
        'payment_method' => 'cash',
    ]);

    $sale = Sale::firstOrFail();

    $this->actingAs($user)
        ->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee($sale->invoice_no)
        ->assertSee('Rahman General Store')
        ->assertSee($product->name);
});

it('blocks viewing another shop sale', function () {
    $sale = Sale::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('sales.show', $sale))
        ->assertForbidden();
});

it('blocks voiding another shop sale', function () {
    $sale = Sale::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('sales.void', $sale))
        ->assertForbidden();
});
