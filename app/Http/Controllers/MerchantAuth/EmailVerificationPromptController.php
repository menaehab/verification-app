<?php

namespace App\Http\Controllers\MerchantAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user('merchant')->hasVerifiedEmail()
                    ? redirect()->intended(route('merchant.index', absolute: false))
                    : view('merchant.auth.verify-email');
    }
}
