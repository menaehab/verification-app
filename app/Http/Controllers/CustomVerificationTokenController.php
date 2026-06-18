<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomVerificationTokenController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function notice(Request $request)
    {
        return $request->user('merchant')->hasVerifiedEmail()
                    ? redirect()->intended(route('merchant.index', absolute: false))
                    : view('merchant.auth.verify-email');
    }


    public function verify(Request $request, $token)
    {
        if ($request->user('merchant')->hasVerifiedEmail()) {
            return redirect()->intended(route('merchant.index', absolute: false).'?verified=1');
        }

        if ($request->user('merchant')->verifyUsingVerificationToken($token)) {
            event(new \Illuminate\Auth\Events\Verified($request->user('merchant')));
            return redirect()->intended(route('merchant.index', absolute: false).'?verified=1');
        }

        return redirect()->route('merchant.verification.notice');
    }

    /**
     * Send a new verification email.
     */
    public function resend(Request $request)
    {
        if ($request->user('merchant')->hasVerifiedEmail()) {
            return redirect()->intended(route('merchant.index', absolute: false));
        }

        $request->user('merchant')->sendEmailVerificationNotification();

        return back(delay: 60)->with('status', 'verification-link-sent');
    }
}
