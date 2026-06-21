<?php

namespace App\Models;

use App\Notifications\MerchantEmailVerification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

class Merchant extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\MerchantFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function generateOTP()
    {
        if (config('verification.way') === 'otp') {
            $otp = rand(100000, 999999);
            $this->otp = $otp;
            $this->otp_till = now()->addMinutes(10);
            $this->save();
            return $otp;
        }
    }

    public function verifyOTP($otp)
    {
        if (config('verification.way') === 'otp') {
            if ($this->otp === $otp && $this->otp_till > now()) {
                $this->otp = null;
                $this->otp_till = null;
                $this->save();
                return true;
            }
            return false;
        }
    }

    public function sendEmailVerificationNotification()
    {
        if (config('verification.way') === 'email') {
            $url = URL::temporarySignedRoute(
                'merchant.verification.verify',
                now()->addMinutes(60),
                ['id' => $this->getKey(), 'hash' => sha1($this->getEmailForVerification())]
            );
            $this->notify(new MerchantEmailVerification($url));
        }

        if (config('verification.way') === 'cvt') {
            $this->generateVerificationToken();
            $url = URL::temporarySignedRoute(
                'merchant.verification.verify',
                now()->addMinutes(60),
                ['token' => $this->verification_token]
            );
            $this->notify(new MerchantEmailVerification($url));
        }

        if (config('verification.way') === 'passwordless') {
            $url = URL::temporarySignedRoute(
                'merchant.verification.verify',
                now()->addMinutes(60),
                ['id' => $this->getKey(), 'hash' => sha1($this->getEmailForVerification())]
            );
        }
    }


    public function generateVerificationToken()
    {
        if (config('verification.way') === 'cvt') {
            $token = \Str::random(64);
            $this->verification_token = $token;
            $this->save();
            return $token;
        }
    }

    public function verifyUsingVerificationToken($token)
    {
        if (config('verification.way') === 'cvt') {
            if ($this->verification_token === $token && $this->email_verified_at === null) {
                $this->email_verified_at = now();
                $this->verification_token = null;
                $this->save();
                return true;
            }
            return false;
        }
    }
}
