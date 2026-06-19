<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PasswordlessAuthController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:merchants,email',
        ]);

        $merchant = \App\Models\Merchant::where('email', $request->email)->first();

        if (! $merchant) {
            throw ValidationException::withMessages([
                'email' => ['The provided email does not exist in our records.'],
            ]);
        }

        // Generate a unique verification token
        $merchant->sendEmailVerificationNotification();

        return back()->with(['message' => 'Verification email sent.']);
    }

    public function verify($merchant)
    {

        Auth::guard('merchant')->loginUsingId($merchant);

        return redirect()->route('merchant.index')->with('message', 'Email verified successfully.');
    }
}
