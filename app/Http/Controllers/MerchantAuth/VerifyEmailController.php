<?php

namespace App\Http\Controllers\MerchantAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantEmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(MerchantEmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user('merchant')->hasVerifiedEmail()) {
            return redirect()->intended(route('merchant.index', absolute: false).'?verified=1');
        }

        if ($request->user('merchant')->markEmailAsVerified()) {
            event(new Verified($request->user('merchant')));
        }

        return redirect()->intended(route('merchant.index', absolute: false).'?verified=1');
    }
}
