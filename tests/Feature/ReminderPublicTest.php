<?php

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\SmsMessage;
use App\Models\User;
use App\Services\ReminderService;

beforeEach(function () {
    config(['services.sms.driver' => 'log']);
});

it('lists due customers in a dry run without sending', function () {
    $user = User::factory()->create(['sms_enabled' => true]);
    $customer = Customer::factory()->for($user)->create([
        'name' => 'Ali Raza',
        'phone' => '0300-1234567',
        'opening_balance' => 500,
    ]);

    $this->artisan('khata:send-reminders', ['--dry-run' => true])
        ->expectsOutputToContain('Ali Raza')
        ->assertSuccessful();

    expect(SmsMessage::count())->toBe(0)
        ->and($customer->fresh()->last_reminder_at)->toBeNull();
});

it('sends logged reminders and updates the last reminder time', function () {
    $user = User::factory()->create(['sms_enabled' => true]);
    $customer = Customer::factory()->for($user)->create([
        'name' => 'Sara Bibi',
        'phone' => '0311-2345678',
        'opening_balance' => 1200,
    ]);

    $this->artisan('khata:send-reminders')->assertSuccessful();

    $message = SmsMessage::firstOrFail();

    expect($message->status)->toBe('logged')
        ->and($message->provider)->toBe('log')
        ->and($message->body)->toContain('Sara Bibi')
        ->and($message->body)->toContain('1,200')
        ->and($customer->fresh()->last_reminder_at)->not->toBeNull();

    // A second run inside the reminder window sends nothing.
    $this->artisan('khata:send-reminders')->assertSuccessful();

    expect(SmsMessage::count())->toBe(1);
});

it('skips customers without a phone or balance', function () {
    $user = User::factory()->create(['sms_enabled' => true]);
    Customer::factory()->for($user)->create(['phone' => null, 'opening_balance' => 500]);
    Customer::factory()->for($user)->create(['phone' => '0300-0000000', 'opening_balance' => 0]);

    $this->artisan('khata:send-reminders')->assertSuccessful();

    expect(SmsMessage::count())->toBe(0);
});

it('sends a reminder from the customer page', function () {
    $user = User::factory()->create(['sms_enabled' => true]);
    $customer = Customer::factory()->for($user)->create([
        'phone' => '0300-1234567',
        'opening_balance' => 800,
    ]);

    $this->actingAs($user)
        ->post(route('customers.remind', $customer))
        ->assertRedirect();

    expect(SmsMessage::where('customer_id', $customer->id)->count())->toBe(1)
        ->and($customer->fresh()->last_reminder_at)->not->toBeNull();
});

it('refuses to remind a customer with no phone or no balance', function () {
    $user = User::factory()->create(['sms_enabled' => true]);
    $noPhone = Customer::factory()->for($user)->create(['phone' => null, 'opening_balance' => 500]);
    $settled = Customer::factory()->for($user)->create(['phone' => '0300-0000000', 'opening_balance' => 0]);

    $this->actingAs($user)->post(route('customers.remind', $noPhone))->assertSessionHas('error');
    $this->actingAs($user)->post(route('customers.remind', $settled))->assertSessionHas('error');

    expect(SmsMessage::count())->toBe(0);
});

it('includes the public statement link in the reminder message', function () {
    $user = User::factory()->create(['sms_enabled' => true]);
    $customer = Customer::factory()->for($user)->create([
        'phone' => '0300-1234567',
        'opening_balance' => 100,
    ]);

    $message = app(ReminderService::class)->send($customer);

    expect($message->body)->toContain(route('public.statement', $customer->public_token));
});

it('updates shop and reminder settings', function () {
    $user = User::factory()->create(['sms_enabled' => true, 'reminder_after_days' => 7]);

    $this->actingAs($user)->put(route('settings.update'), [
        'store_name' => 'Rahman General Store',
        'store_phone' => '0345-1234567',
        'store_address' => 'Main Bazaar, Batkhela',
        'reminder_after_days' => 14,
        'sms_enabled' => 0,
    ])->assertRedirect(route('settings.edit'));

    $user->refresh();

    expect($user->store_name)->toBe('Rahman General Store')
        ->and($user->reminder_after_days)->toBe(14)
        ->and($user->sms_enabled)->toBeFalse();
});

it('renders the settings page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.edit'))
        ->assertOk()
        ->assertSee('Shop profile')
        ->assertSee('Payment reminders');
});

it('shows a public statement to a logged-out visitor', function () {
    $user = User::factory()->create([
        'store_name' => 'Rahman General Store',
        'store_phone' => '0345-1234567',
    ]);
    $customer = Customer::factory()->for($user)->create([
        'name' => 'Ali Raza',
        'opening_balance' => 300,
    ]);

    $this->get(route('public.statement', $customer->public_token))
        ->assertOk()
        ->assertSee('noindex')
        ->assertSee('Rahman General Store')
        ->assertSee('Ali Raza')
        ->assertSee('300')
        ->assertSee('0345-1234567');
});

it('returns 404 for an invalid statement token', function () {
    $this->get('/s/this-token-does-not-exist')->assertNotFound();
});

it('shows only the last twenty entries on the public statement', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create(['opening_balance' => 0]);

    LedgerEntry::factory()->create([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'type' => LedgerEntry::TYPE_UDHAAR,
        'amount' => 100,
        'entry_date' => now()->subDays(40),
        'description' => 'OLDEST-MARKER',
    ]);

    for ($i = 0; $i < 20; $i++) {
        LedgerEntry::factory()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'type' => LedgerEntry::TYPE_UDHAAR,
            'amount' => 50,
            'entry_date' => now()->subDays($i),
            'description' => 'RECENT-MARKER-'.$i,
        ]);
    }

    $this->get(route('public.statement', $customer->public_token))
        ->assertOk()
        ->assertDontSee('OLDEST-MARKER')
        ->assertSee('RECENT-MARKER-19');
});
