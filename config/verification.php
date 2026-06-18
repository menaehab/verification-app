<?php

return [
    // The verification way to use when verifying email addresses.
    // Supported: "default", "email", "cvt", "passwordless"
    'way' => env('VERIFICATION_WAY', 'passwordless'),
];
