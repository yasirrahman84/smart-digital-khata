<?php

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Validation\ValidationException;

it('creates a product with opening stock', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('products.store'), [
        'name' => 'Sugar 1kg',
        'sku' => 'SKU-1001',
        'unit' => 'kg',
        'purchase_price' => 145,
        'sale_price' => 160,
        'stock_qty' => 40,
        'low_stock_threshold' => 10,
        'is_active' => 1,
    ])->assertRedirect();

    $product = Product::where('name', 'Sugar 1kg')->firstOrFail();

    expect($product->user_id)->toBe($user->id)
        ->and((float) $product->stock_qty)->toBe(40.0)
        ->and($product->stockValue())->toBe(5800.0);
});

it('validates a unique sku per shop', function () {
    $user = User::factory()->create();
    Product::factory()->for($user)->create(['sku' => 'SKU-1']);

    $this->actingAs($user)
        ->post(route('products.store'), [
            'name' => 'Another item',
            'sku' => 'SKU-1',
            'unit' => 'piece',
            'purchase_price' => 10,
            'sale_price' => 15,
            'stock_qty' => 5,
            'low_stock_threshold' => 1,
        ])
        ->assertSessionHasErrors('sku');
});

it('adds stock and records a movement', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 10]);

    $this->actingAs($user)
        ->post(route('products.adjust', $product), [
            'mode' => 'in',
            'quantity' => 5,
            'note' => 'Supplier delivery',
        ])
        ->assertRedirect(route('products.show', $product));

    expect((float) $product->fresh()->stock_qty)->toBe(15.0)
        ->and($product->stockMovements()->count())->toBe(1)
        ->and($product->stockMovements()->first()->type)->toBe(StockMovement::TYPE_IN);
});

it('removes stock and records a movement', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 10]);

    $this->actingAs($user)->post(route('products.adjust', $product), [
        'mode' => 'out',
        'quantity' => 4,
    ])->assertRedirect();

    expect((float) $product->fresh()->stock_qty)->toBe(6.0)
        ->and($product->stockMovements()->first()->signedQuantity())->toBe(-4.0);
});

it('refuses to remove more stock than available', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 3]);

    $this->actingAs($user)
        ->post(route('products.adjust', $product), [
            'mode' => 'out',
            'quantity' => 10,
        ])
        ->assertSessionHasErrors('quantity');

    expect((float) $product->fresh()->stock_qty)->toBe(3.0)
        ->and($product->stockMovements()->count())->toBe(0);
});

it('sets an exact stock count and stores the delta', function () {
    $product = Product::factory()->create(['stock_qty' => 4]);

    app(StockService::class)->adjust($product, 'set', 20);

    expect((float) $product->fresh()->stock_qty)->toBe(20.0)
        ->and($product->stockMovements()->first()->signedQuantity())->toBe(16.0)
        ->and($product->stockMovements()->first()->type)->toBe(StockMovement::TYPE_ADJUSTMENT);
});

it('notifies once per low-stock crossing and again after restocking', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create([
        'stock_qty' => 10,
        'low_stock_threshold' => 5,
    ]);

    $stock = app(StockService::class);

    // 10 -> 5: crosses the threshold.
    $stock->adjust($product, 'out', 5);
    expect($user->notifications()->count())->toBe(1);

    // 5 -> 4: still low, no second alert.
    $stock->adjust($product, 'out', 1);
    expect($user->notifications()->count())->toBe(1);

    // 4 -> 14: recovered, marker cleared.
    $stock->adjust($product, 'in', 10);
    expect($product->fresh()->low_stock_notified_at)->toBeNull();

    // 14 -> 4: crosses again, new alert.
    $stock->adjust($product, 'out', 10);
    expect($user->notifications()->count())->toBe(2);
});

it('blocks adjusting another user product', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('products.adjust', $product), ['mode' => 'in', 'quantity' => 1])
        ->assertForbidden();
});

it('archives a product instead of deleting it', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['is_active' => true]);

    $this->actingAs($user)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect($product->fresh())->not->toBeNull()
        ->and($product->fresh()->is_active)->toBeFalse();
});

it('lists only the signed-in user products', function () {
    $user = User::factory()->create();
    $mine = Product::factory()->for($user)->create();
    $other = Product::factory()->create();

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee($mine->name)
        ->assertDontSee($other->name);
});

it('shows and clears notifications', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create([
        'stock_qty' => 10,
        'low_stock_threshold' => 5,
    ]);

    app(StockService::class)->adjust($product, 'out', 6);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee($product->name);

    $this->actingAs($user)
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'));

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('validates stock adjustment quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();

    expect(fn () => app(StockService::class)->adjust($product, 'in', 0))
        ->toThrow(ValidationException::class);
});
