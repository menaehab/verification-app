<?php

use App\Models\Merchant;

it('merchant registration screen can be rendered', function () {
    $response = $this->get('/merchant/register');

    $response->assertStatus(200);
});

it('merchants can register', function () {
    $response = $this->post('/merchant/register', [
        'name' => 'Test Merchant',
        'email' => 'merchant@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $merchant = Merchant::where('email', 'merchant@example.com')->first();
    expect($merchant)->not->toBeNull();

    $this->assertAuthenticated('merchant');
    $response->assertRedirect(route('merchant.index', absolute: false));
});

it('registration requires a unique email', function () {
    Merchant::factory()->create(['email' => 'foo@bar.com']);

    $response = $this->post('/merchant/register', [
        'name' => 'Test',
        'email' => 'foo@bar.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
});
