<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\TwilloService;
use Illuminate\Http\Request;

class OTPController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'email|required',
        ]);

        $merchant = Merchant::where('email', $request->email)->first();

        if (!$merchant) {
            return back()->with('error', 'Merchant not found');
        }

        $otp = $merchant->generateOTP();
        $merchant->phone = $request->phone;
        $merchant->save();

        TwilloService::sendOTP($request->phone, $otp);

        return back()->with('success', 'OTP sent successfully');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'email|required',
            'otp' => 'required',
        ]);

        $merchant = Merchant::where('email', $request->email)->first();

        if (!$merchant) {
            return back()->with('error', 'Merchant not found');
        }

        if ($merchant->verifyOTP($request->otp)) {
            return back()->with('success', 'OTP verified successfully');
        }

        return back()->with('error', 'Invalid OTP');
    }
}
