# MFA (Multi-Factor Authentication) Plugin for GP247

## Overview

The MFA Plugin provides Two-Factor Authentication for GP247 system, enhancing user account security. The plugin supports multiple guards (customer, admin, vendor, pmo...) for flexible user authentication management.

<p align="center">
  <img src="https://static.gp247.net/product/mfa-user.jpg" alt="GP247 User Interface - Register/Manage MFA" />
  <img src="https://static.gp247.net/product/mfa-admin.jpg" alt="GP247 Admin Interface - Setup/Manage MFA" />
  <img src="https://static.gp247.net/product/mfa-process.jpg" alt="Multi-Factor Authentication Process on GP247" />
</p>


## Features

- ✅ Two-factor authentication using TOTP (Time-based One-Time Password)
- ✅ Multiple guards support: customer, admin, vendor, pmo
- ✅ Easy-to-scan QR codes with authenticator apps
- ✅ Recovery codes for device loss scenarios
- ✅ MFA management from Admin panel
- ✅ Usage statistics by guard
- ✅ Optional forced MFA per guard
- ✅ Responsive and user-friendly interface
- ✅ Multi-language support (Vietnamese, English)

## System Requirements

- GP247 Core >= 1.2
- Laravel 12.x
- PHP >= 8.2
- Required packages:
  - pragmarx/google2fa: ^8.0 or ^9.0
  - bacon/bacon-qr-code: ^2.0 or ^3.0

## Installation

### Step 1: Install dependencies

Add the required packages to your main project's `composer.json`:

```bash
composer require pragmarx/google2fa
composer require bacon/bacon-qr-code
```

**Important**: Run this command in the root directory of your GP247 project, not inside the plugin folder.

### Step 2: Install the plugin

1. Copy the plugin folder to `app/GP247/Plugins/MFA`
2. Access Admin Panel > Extensions > Plugins
3. Find "MFA" and click "Install"
4. The plugin will automatically create database tables and default configurations
5. Click "Enable" to activate the plugin

### Step 3: Configuration

1. Access Admin Panel > Extensions > Plugins
2. Click on "MFA" plugin to open configuration page
3. Configure each guard:
   - **Enabled**: Enable/disable MFA for the guard
   - **Forced**: Force users to enable MFA
   - **QR Code Size**: QR code size (100-500px)
   - **Recovery Codes Count**: Number of recovery codes (4-20)
   - **Window**: Time window allowance (0-5, recommended 1)
4. Click "Save Settings"

## Usage

### For End Users

#### Setup MFA

1. Go to MFA management page: `/mfa/setup/customer`
2. Scan QR code with authenticator app (Google Authenticator, Authy, Microsoft Authenticator, etc.)
3. Enter 6-digit code from app to verify
4. Save recovery codes in a secure place

#### Login with MFA

1. Login with username/password as usual
2. After successful login, system will require MFA code
3. Enter 6-digit code from authenticator app
4. Or use 8-character recovery code if device is lost

#### Manage MFA

- View MFA status: `/mfa/manage/customer`
- View recovery codes: `/mfa/recovery-codes/customer`
- Regenerate recovery codes: From MFA management page
- Disable MFA: From MFA management page (requires password confirmation)

### For Administrators

#### View Statistics

- Access Admin Panel > Extensions > MFA
- View MFA adoption rate by guard
- See total users and enabled users

#### Configure Guards

- Enable/disable MFA for each guard
- Force users to enable MFA
- Adjust technical parameters

#### Reset User MFA

- From MFA management page, admins can reset MFA for any user
- User will need to setup MFA again from scratch

## MFA Flow Diagrams

### 1. MFA Setup Flow (First Time)

```
┌─────────────────────────────────────────────────────────────┐
│                    User Login Success                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
                ┌────────────────────┐
                │  MFA Enabled for   │
                │  this guard?       │
                └────────┬───────────┘
                         │
        ┌────────────────┴────────────────┐
        │ No                               │ Yes
        ▼                                  ▼
┌───────────────┐              ┌────────────────────┐
│  Allow Access │              │ User enrolled MFA? │
└───────────────┘              └─────────┬──────────┘
                                         │
                        ┌────────────────┴────────────────┐
                        │ No                               │ Yes
                        ▼                                  ▼
              ┌──────────────────┐              ┌──────────────────┐
              │  MFA Forced?     │              │ Session verified?│
              └────────┬─────────┘              └────────┬─────────┘
                       │                                 │
         ┌─────────────┴──────────┐           ┌─────────┴─────────┐
         │ No          │ Yes      │           │ No     │ Yes      │
         ▼             ▼          │           ▼        ▼          │
    Allow Access   Redirect      │      Redirect  Allow Access   │
                   to Setup      │      to Verify                │
                       │          │           │                   │
                       ▼          │           ▼                   │
              ┌─────────────┐    │   ┌─────────────────┐        │
              │ Show QR Code│    │   │ Enter 6-digit   │        │
              │ & Secret    │    │   │ code or         │        │
              └──────┬──────┘    │   │ recovery code   │        │
                     │           │   └────────┬────────┘        │
                     ▼           │            │                  │
              ┌─────────────┐    │            ▼                  │
              │ Verify Code │    │   ┌─────────────────┐        │
              └──────┬──────┘    │   │  Code Valid?    │        │
                     │           │   └────────┬────────┘        │
                     ▼           │            │                  │
            ┌─────────────────┐ │   ┌────────┴────────┐        │
            │ Generate        │ │   │ No    │ Yes     │        │
            │ Recovery Codes  │ │   ▼       ▼         │        │
            └────────┬────────┘ │  Error  Set         │        │
                     │          │         Session     │        │
                     ▼          │         Verified    │        │
            ┌─────────────────┐ │            │        │        │
            │ Show & Save     │ │            ▼        │        │
            │ Recovery Codes  │ │    ┌──────────────┐ │        │
            └────────┬────────┘ │    │Allow Access  │ │        │
                     │          │    └──────────────┘ │        │
                     ▼          │                      │        │
            ┌─────────────────┐ │                      │        │
            │ MFA Activated   │ │                      │        │
            └────────┬────────┘ │                      │        │
                     │          │                      │        │
                     └──────────┴──────────────────────┘        │
                                │                               │
                                └───────────────────────────────┘
```

### 2. Login Flow with MFA (Already Setup)

```
┌─────────────────────┐
│ User Login          │
│ (Email + Password)  │
└──────────┬──────────┘
           │
           ▼
┌──────────────────────┐
│ Credentials Valid?   │
└──────────┬───────────┘
           │
    ┌──────┴──────┐
    │ No   │ Yes  │
    ▼      ▼      │
  Error  Check    │
         MFA      │
           │      │
           ▼      │
┌─────────────────────┐
│ User enrolled MFA?  │
└──────────┬──────────┘
           │
    ┌──────┴──────┐
    │ No   │ Yes  │
    ▼      ▼      │
  Allow  Redirect │
  Access to MFA   │
         Verify   │
           │      │
           ▼      │
┌────────────────────────────┐
│ MFA Verification Page      │
│                            │
│ [Enter 6-digit code]       │
│ or                         │
│ [Use recovery code]        │
└──────────┬─────────────────┘
           │
           ▼
┌──────────────────────┐
│ Verify Code          │
│ (TOTP or Recovery)   │
└──────────┬───────────┘
           │
    ┌──────┴──────────┐
    │ Valid │ Invalid │
    ▼       ▼         │
┌─────────────┐  ┌─────────┐
│ Set Session │  │ Show    │
│ Verified    │  │ Error   │
└──────┬──────┘  └────┬────┘
       │              │
       ▼              ▼
┌──────────────┐  ┌──────────┐
│ Allow Access │  │ Try Again│
└──────────────┘  └──────────┘
```

### 3. Recovery Code Flow

```
┌──────────────────────┐
│ User lost device     │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Login Page           │
│ Enter credentials    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────┐
│ MFA Verification Page    │
│                          │
│ Click "Use recovery code"│
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│ Enter 8-char recovery    │
│ code (e.g. ABCD1234)     │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────┐
│ Check recovery code  │
│ in database          │
└──────────┬───────────┘
           │
    ┌──────┴───────┐
    │ Valid│Invalid│
    ▼      ▼       │
┌────────────┐ ┌────────┐
│ Mark code  │ │ Error  │
│ as used    │ └────────┘
└─────┬──────┘     │
      │            │
      ▼            │
┌────────────┐     │
│ Login OK   │     │
└─────┬──────┘     │
      │            │
      ▼            │
┌──────────────────┐
│ Redirect to      │
│ MFA Setup        │
│ (Setup new device)│
└──────────────────┘
```

### 4. Middleware Flow

```
┌──────────────────────────┐
│ Request to protected     │
│ route with mfa.verify    │
└───────────┬──────────────┘
            │
            ▼
┌───────────────────────────┐
│ Middleware: Check Auth    │
└───────────┬───────────────┘
            │
     ┌──────┴──────┐
     │ Not  │ Auth │
     │ Auth │      │
     ▼      ▼      │
   Redirect Check  │
   to Login MFA    │
            │      │
            ▼      │
┌─────────────────────────┐
│ MFA enabled for guard?  │
└───────────┬─────────────┘
            │
     ┌──────┴──────┐
     │ No   │ Yes  │
     ▼      ▼      │
   Allow  Check    │
   Access Session  │
            │      │
            ▼      │
┌──────────────────────────┐
│ Session verified?        │
└───────────┬──────────────┘
            │
     ┌──────┴──────┐
     │ Yes  │ No   │
     ▼      ▼      │
   Allow  Check    │
   Access User     │
          MFA      │
            │      │
            ▼      │
┌──────────────────────────┐
│ User enrolled MFA?       │
└───────────┬──────────────┘
            │
     ┌──────┴──────┐
     │ No   │ Yes  │
     ▼      ▼      │
   Check  Redirect │
   Forced to MFA   │
   MFA    Verify   │
     │             │
     ▼             │
┌──────────────┐   │
│ MFA Forced?  │   │
└──────┬───────┘   │
       │           │
  ┌────┴────┐      │
  │No  │Yes │      │
  ▼    ▼    │      │
Allow Redirect     │
Access to Setup    │
       │           │
       └───────────┘
```

## Code Integration

### Middleware

Use `mfa.verify` middleware to protect routes:

```php
// In routes/web.php
Route::group(['middleware' => ['auth:customer', 'mfa.verify:customer']], function () {
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/profile', 'ProfileController@index');
});

// For admin
Route::group(['middleware' => ['auth:admin', 'mfa.verify:admin']], function () {
    Route::get('/admin/dashboard', 'AdminController@index');
});
```

### Helper Functions

```php
// Check if MFA is enabled for guard
if (mfa_is_enabled_for_guard('customer')) {
    // MFA is enabled
}

// Check if user has enrolled MFA
if (mfa_is_user_enrolled($user, 'customer')) {
    // User has enrolled MFA
}

// Check if session is MFA verified
if (mfa_is_verified()) {
    // Session is verified
}

// Mark session as verified
mfa_set_verified();

// Clear verification from session
mfa_clear_verified();
```

## Directory Structure

```
app/GP247/Plugins/MFA/
├── Admin/
│   └── AdminController.php          # Admin panel controller
├── Controllers/
│   └── MFAController.php            # Main MFA controller
├── Helpers/
│   └── TwoFactorHelper.php          # Google 2FA helper
├── Lang/
│   ├── en/
│   │   └── lang.php                 # English translations
│   └── vi/
│       └── lang.php                 # Vietnamese translations
├── Middleware/
│   └── TwoFactorAuthentication.php # MFA verification middleware
├── Models/
│   ├── ExtensionModel.php           # Install/uninstall model
│   └── TwoFactorAuth.php            # MFA storage model
├── Views/
│   ├── Admin.blade.php              # Admin view
│   └── Frontend/
│       ├── setup.blade.php          # MFA setup view
│       ├── verify.blade.php         # MFA verification view
│       ├── recovery_codes.blade.php # Recovery codes view
│       └── manage.blade.php         # MFA management view
├── AppConfig.php                     # Plugin configuration
├── config.php                        # Config file
├── function.php                      # Helper functions
├── gp247.json                        # Plugin metadata
├── Provider.php                      # Service provider
├── Route.php                         # Routes
└── readme.md                         # This documentation
```

## Security

### Encryption

- Secret keys are encrypted in database using Laravel Encryption
- Recovery codes are encrypted in database
- All sensitive data is protected

### Best Practices

1. **Force MFA for Admins**: Enable "Forced" for admin guard
2. **User Education**: Guide users to store recovery codes safely
3. **Regular Monitoring**: Track MFA adoption rates
4. **Backups**: Ensure database is regularly backed up
5. **HTTPS**: Require HTTPS for all MFA pages

## Supported Authenticator Apps

The plugin supports all TOTP authenticator apps:

- Google Authenticator (iOS, Android)
- Microsoft Authenticator (iOS, Android)
- Authy (iOS, Android, Desktop)
- 1Password (iOS, Android, Desktop)
- LastPass Authenticator
- Duo Mobile
- FreeOTP
- And many other TOTP apps

## Troubleshooting

### User Lost Authenticator Device

1. User uses recovery code to login
2. After login, setup MFA again with new device
3. Save new recovery codes

### Verification Code Not Working

1. Check if device time is accurate
2. Increase "Window" value in configuration (recommended 1-2)
3. Ensure correct QR code was scanned

### Admin Wants to Reset User MFA

1. Access Admin Panel > Extensions > MFA
2. Find user in list
3. Click "Reset MFA"
4. User will need to setup MFA again

## Support

- Website: https://GP247.net
- Email: support@gp247.net
- Documentation: https://gp247.net/en/product/plugin-mfa.html

## License

MIT License

## Author

GP247 Team

## Changelog

### Version 1.0
- Initial release
- Support for Customer, Admin, Vendor, PMO guards
- TOTP authentication with Google Authenticator
- Recovery codes system
- Admin management panel
- Multi-language support (Vietnamese, English)

