<?php

use App\Models\Merchant;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('merchant email verification screen can be rendered', function () {
    $merchant = Merchant::factory()->unverified()->create();

    $response = $this->actingAs($merchant, 'merchant')->get('/merchant/verify-email');

    $response->assertStatus(200);
});

test('merchant email can be verified using cvt', function () {
    $merchant = Merchant::factory()->unverified()->create();

    Event::fake();

    $merchant->generateVerificationToken();
    $token = $merchant->verification_token;

    $verificationUrl = URL::temporarySignedRoute(
        'merchant.verification.verify',
        now()->addMinutes(60),
        ['token' => $token]
    );

    $response = $this->actingAs($merchant, 'merchant')->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($merchant->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('merchant.index', absolute: false) . '?verified=1');
});

test('merchant email is not verified with invalid cvt token', function () {
    $merchant = Merchant::factory()->unverified()->create();

    $merchant->generateVerificationToken();

    $verificationUrl = URL::temporarySignedRoute(
        'merchant.verification.verify',
        now()->addMinutes(60),
        ['token' => 'invalid-token-here']
    );

    $response = $this->actingAs($merchant, 'merchant')->get($verificationUrl);

    expect($merchant->fresh()->hasVerifiedEmail())->toBeFalse();
    $response->assertRedirect(route('merchant.verification.notice'));
});
