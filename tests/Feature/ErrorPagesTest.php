<?php

use App\Models\Customer;
use App\Models\User;

it('renders the styled 404 page', function () {
    $this->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('Page not found')
        ->assertSee('Smart Digital Khata');
});

it('renders the styled 403 page for another shop record', function () {
    $customer = Customer::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('customers.show', $customer))
        ->assertForbidden()
        ->assertSee('You cannot open this page');
});

it('ships a favicon and a robots file', function () {
    expect(file_exists(public_path('favicon.svg')))->toBeTrue()
        ->and(file_exists(public_path('robots.txt')))->toBeTrue();
});
