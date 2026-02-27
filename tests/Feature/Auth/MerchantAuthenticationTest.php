<?php

use App\Models\Merchant;

it('merchant login screen can be rendered', function () {
    $response = $this->get('/merchant/login');

    $response->assertStatus(200);
});

it('merchants can authenticate using the login screen', function () {
    $merchant = Merchant::factory()->create();

    $response = $this->post('/merchant/login', [
        'email' => $merchant->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('merchant');
    $response->assertRedirect(route('merchant.index', absolute: false));
});

it('merchants cannot authenticate with invalid password', function () {
    $merchant = Merchant::factory()->create();

    $this->post('/merchant/login', [
        'email' => $merchant->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('merchant');
});

it('merchants can logout', function () {
    $merchant = Merchant::factory()->create();

    $response = $this->actingAs($merchant, 'merchant')->post('/merchant/logout');

    $this->assertGuest('merchant');
    $response->assertRedirect(route('merchant.login', absolute: false));
});
