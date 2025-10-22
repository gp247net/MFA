<?php
/**
 * Provides everything needed for the Extension
 */

 $config = file_get_contents(__DIR__.'/gp247.json');
 $config = json_decode($config, true);
 $extensionPath = $config['configGroup'].'/'.$config['configKey'];
 
 $this->loadTranslationsFrom(__DIR__.'/Lang', $extensionPath);
 
 if (gp247_extension_check_active($config['configGroup'], $config['configKey'])) {
     
     $this->loadViewsFrom(__DIR__.'/Views', $extensionPath);
     
     if (file_exists(__DIR__.'/config.php')) {
         $this->mergeConfigFrom(__DIR__.'/config.php', $extensionPath);
     }
 
     if (file_exists(__DIR__.'/function.php')) {
         require_once __DIR__.'/function.php';
     }

     app('router')->aliasMiddleware('mfa.verify', \App\GP247\Plugins\MFA\Middleware\TwoFactorAuthentication::class);

    //  // For admin
     $admin = (array) config('gp247-config.admin.middleware', []);
     if (!in_array('mfa.verify:admin', $admin, true)) {
         $admin[] = 'mfa.verify:admin';
         config(['gp247-config.admin.middleware' => $admin]);
     }
 
     // For front
     $customer = (array) config('gp247-config.shop.middleware', []);    // customer
     if (!in_array('mfa.verify', $customer, true)) {
         $customer[] = 'mfa.verify:customer';
         config(['gp247-config.shop.middleware' => $customer]);
     }
 
 
     // Ensure runtime Router groups receive middleware even if groups were already registered
     app('router')->prependMiddlewareToGroup('admin', 'mfa.verify:admin');
     app('router')->prependMiddlewareToGroup('customer', 'mfa.verify:customer');
     app('router')->prependMiddlewareToGroup('vendor', 'mfa.verify:vendor');
     app('router')->prependMiddlewareToGroup('pmo_partner', 'mfa.verify:pmo_partner');
 }
