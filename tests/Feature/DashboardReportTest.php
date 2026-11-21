<?php

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Support\Carbon;

it('shows dashboard totals for the signed-in shop', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 1500]);
    $product = Product::factory()->for($user)->create([
        'stock_qty' => 10,
        'sale_price' => 200,
        'low_stock_threshold' => 2,
    ]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 200]],
        'payment_method' => 'cash',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('1,500')
        ->assertSee('400');
});

it('computes the receivable total from positive balances only', function () {
    $user = User::factory()->create();
    Customer::factory()->for($user)->create(['opening_balance' => 1000]);
    Customer::factory()->for($user)->create(['opening_balance' => 500]);
    $advance = Customer::factory()->for($user)->create(['opening_balance' => 0]);
    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $advance->id,
        'type' => LedgerEntry::TYPE_PAYMENT,
        'amount' => 300,
    ]);

    expect(app(ReportService::class)->totalReceivable($user))->toBe(1500.0);
});

it('buckets receivables using FIFO payments', function () {
    $user = User::factory()->create();

    // Old debt fully paid, new debt today: oldest unpaid is today.
    $recent = Customer::factory()->for($user)->create(['opening_balance' => 0]);
    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $recent->id,
        'type' => LedgerEntry::TYPE_UDHAAR,
        'amount' => 1000,
        'entry_date' => now()->subDays(90),
    ]);
    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $recent->id,
        'type' => LedgerEntry::TYPE_PAYMENT,
        'amount' => 1000,
        'entry_date' => now()->subDays(10),
    ]);
    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $recent->id,
        'type' => LedgerEntry::TYPE_UDHAAR,
        'amount' => 500,
        'entry_date' => now(),
    ]);

    // Old debt never paid: 60+ bucket.
    $old = Customer::factory()->for($user)->create(['opening_balance' => 0]);
    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $old->id,
        'type' => LedgerEntry::TYPE_UDHAAR,
        'amount' => 700,
        'entry_date' => now()->subDays(75),
    ]);

    $rows = app(ReportService::class)->receivables($user)->keyBy(fn ($row) => $row['customer']->id);

    expect($rows[$recent->id]['balance'])->toBe(500.0)
        ->and($rows[$recent->id]['bucket'])->toBe('0-30')
        ->and($rows[$old->id]['bucket'])->toBe('60+')
        ->and($rows[$old->id]['age_days'])->toBeGreaterThanOrEqual(74);
});

it('builds a sales report for a date range', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 50, 'sale_price' => 100]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 100]],
        'payment_method' => 'cash',
    ]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100]],
        'payment_method' => 'udhaar',
        'customer_id' => Customer::factory()->for($user)->create()->id,
    ]);

    $report = app(ReportService::class)->salesReport($user, now()->toDateString(), now()->toDateString());

    expect($report['summary']['count'])->toBe(2)
        ->and($report['summary']['revenue'])->toBe(500.0)
        ->and($report['summary']['cash'])->toBe(300.0)
        ->and($report['summary']['udhaar'])->toBe(200.0)
        ->and($report['topProducts']->first()->product_name)->toBe($product->name)
        ->and((float) $report['topProducts']->first()->quantity)->toBe(5.0);
});

it('renders the report pages', function () {
    $user = User::factory()->create();
    Customer::factory()->for($user)->create(['opening_balance' => 900]);
    Product::factory()->for($user)->create(['stock_qty' => 4, 'low_stock_threshold' => 5]);

    $this->actingAs($user)->get(route('reports.receivables'))->assertOk()->assertSee('Receivables');
    $this->actingAs($user)->get(route('reports.sales'))->assertOk()->assertSee('Sales report');
    $this->actingAs($user)->get(route('reports.stock'))->assertOk()->assertSee('Stock report');
});

it('exports receivables as csv', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create([
        'name' => 'Ali Raza',
        'opening_balance' => 450,
    ]);

    $response = $this->actingAs($user)->get(route('reports.receivables.csv'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');

    $csv = $response->streamedContent();
    expect($csv)->toContain('Customer')
        ->and($csv)->toContain('Ali Raza')
        ->and($csv)->toContain('450.00');
});

it('exports sales as csv for the chosen range', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['stock_qty' => 10, 'sale_price' => 100]);

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100]],
        'payment_method' => 'cash',
    ]);

    $response = $this->actingAs($user)->get(route('reports.sales.csv', [
        'from' => now()->toDateString(),
        'to' => now()->toDateString(),
    ]));

    $response->assertOk();
    $csv = $response->streamedContent();

    expect($csv)->toContain('Invoice')
        ->and($csv)->toContain('INV-')
        ->and($csv)->toContain('100.00');
});

it('exports stock as csv', function () {
    $user = User::factory()->create();
    Product::factory()->for($user)->create([
        'name' => 'Sugar 1kg',
        'stock_qty' => 4,
        'purchase_price' => 145,
        'low_stock_threshold' => 5,
    ]);

    $response = $this->actingAs($user)->get(route('reports.stock.csv'));

    $response->assertOk();
    $csv = $response->streamedContent();

    expect($csv)->toContain('Sugar 1kg')
        ->and($csv)->toContain('580.00')
        ->and($csv)->toContain('yes');
});

it('only counts this shop in reports', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Customer::factory()->for($user)->create(['opening_balance' => 100]);
    Customer::factory()->for($other)->create(['opening_balance' => 99999]);
    Product::factory()->for($other)->create(['stock_qty' => 1000]);

    $report = app(ReportService::class)->dashboard($user);

    expect($report['receivable'])->toBe(100.0)
        ->and($report['lowStockCount'])->toBe(0);
});
