<?php

namespace App\Services;


class Twilio
{
    public function sendOTP($phone, $otp)
    {
        $sid = env("TWILLO_ACCOUNT_SID");
        $token = env("TWILLO_AUTH_TOKEN");
        $client = new Twilio\Rest\Client($sid, $token);

        // Use the Client to make requests to the Twilio REST API
        $client->messages->create(
            // The number you'd like to send the message to
            '+16516503317',
            [
                // A Twilio phone number you purchased at https://console.twilio.com
                'from' => env("TWILLO_PHONE_NUMBER"),
                // The body of the text message you'd like to send
                'body' => "Hey {$phone}! Your OTP is: {$otp}"
            ]
        );
    }
}
