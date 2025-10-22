<?php
use Illuminate\Support\Facades\Route;

$config = file_get_contents(__DIR__.'/gp247.json');
$config = json_decode($config, true);

if(gp247_extension_check_active($config['configGroup'], $config['configKey'])) {

    // Frontend routes for MFA
    Route::group(
        [
            'middleware' => ['web'],
            'prefix'    => 'mfa',
            'namespace' => 'App\GP247\Plugins\MFA\Controllers',
        ],
        function () {
            // Setup MFA
            Route::get('setup/{guard}', 'MFAController@showSetup')
                ->name('mfa.setup.show');
            
            Route::post('setup/enable', 'MFAController@enable')
                ->name('mfa.setup.enable');

            // Verify MFA
            Route::get('verify/{guard?}', 'MFAController@showVerify')
                ->name('mfa.verify.show');
            
            Route::post('verify', 'MFAController@verify')
                ->name('mfa.verify');

            // Manage MFA
            Route::get('manage/{guard}', 'MFAController@showManage')
                ->name('mfa.manage');
            
            Route::post('disable', 'MFAController@disable')
                ->name('mfa.disable');

            // Recovery codes
            Route::get('recovery-codes/{guard}', 'MFAController@showRecoveryCodes')
                ->name('mfa.recovery_codes');
            
            Route::post('recovery-codes/regenerate', 'MFAController@regenerateRecoveryCodes')
                ->name('mfa.recovery_codes.regenerate');
        }
    );

    // Admin routes for plugin configuration
    Route::group(
        [
            'prefix' => GP247_ADMIN_PREFIX.'/mfa',
            'middleware' => GP247_ADMIN_MIDDLEWARE,
            'namespace' => '\App\GP247\Plugins\MFA\Admin',
        ], 
        function () {
            Route::get('/', 'AdminController@index')
                ->name('admin_mfa.index');
            
            Route::get('/users/{guard?}', 'AdminController@usersManagement')
                ->name('admin_mfa.users');
            
            Route::post('/reset-user/{guard}', 'AdminController@resetUserMFA')
                ->name('admin_mfa.reset_user');
        }
    );
}

