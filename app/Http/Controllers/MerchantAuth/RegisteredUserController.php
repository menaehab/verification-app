<?php

namespace App\Http\Controllers\MerchantAuth;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('merchant.auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.server_key'),
            'response' => $request->input('g-recaptcha-response'),
        ]);

        $result = $response->json();

        if (!$result['success'] || $result['score'] < 0.5) {
            return back()->with('error', 'reCAPTCHA verification failed');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Merchant::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $merchant = Merchant::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($merchant));

        Auth::guard('merchant')->login($merchant);

        return redirect(route('merchant.index', absolute: false));
    }
}
