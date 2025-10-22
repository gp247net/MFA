<?php

return [
    // Default guard to use for MFA
    'default_guard' => 'customer',
    
    // Guards configuration - support for multiple guards
    'guards' => [
        'customer' => [
            'enabled' => 0,
            'forced' => 0,
            'model' => 'GP247\Shop\Models\ShopCustomer',
            'redirect_after_verify' => 'front.home',
            'redirect_after_login' => 'mfa.verify.show',
            'redirect_need_login' => 'customer.login',
            'qr_code_size' => 200,
            'recovery_codes_count' => 8,
            'window' => 1, // Time window in 30-second increments
        ],
        'admin' => [
            'enabled' => 1,
            'forced' => 0,
            'model' => 'GP247\Core\Models\AdminUser',
            'redirect_after_verify' => 'admin.home',
            'redirect_after_login' => 'mfa.verify.show',
            'redirect_need_login' => 'admin.login',
            'qr_code_size' => 200,
            'recovery_codes_count' => 8,
            'window' => 1,
        ],
        'vendor' => [
            'enabled' => 0,
            'forced' => 0,
            'model' => 'App\GP247\Plugins\MultiVendorPro\Models\VendorUser',
            'redirect_after_verify' => 'vendor_admin.home',
            'redirect_after_login' => 'mfa.verify.show',
            'redirect_need_login' => 'vendor.login',
            'qr_code_size' => 200,
            'recovery_codes_count' => 8,
            'window' => 1,
        ],
        'pmo_partner' => [
            'enabled' => 0,
            'forced' => 0,
            'model' => 'App\GP247\Plugins\PmoPartner\Models\PmoPartnerUser', // Change to your PMO model
            'redirect_after_verify' => 'partner.home',
            'redirect_after_login' => 'mfa.verify.show',
            'redirect_need_login' => 'partner.login',
            'qr_code_size' => 200,
            'recovery_codes_count' => 8,
            'window' => 1,
        ],
    ],

    // Application name to display in authenticator apps
    'app_name' => env('APP_NAME', 'GP247'),

    // Enable/disable recovery codes
    'enable_recovery_codes' => true,

    // Session key to store MFA verification status
    'session_key' => 'mfa_verified',

    // Session lifetime for MFA verification (in minutes)
    'session_lifetime' => 120,

    // QR Code configuration
    'qr_code' => [
        'size' => 200,
        'margin' => 0,
        'error_correction' => 'high', // low, medium, quartile, high
    ],
];

