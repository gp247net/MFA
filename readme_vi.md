# Plugin MFA (Multi-Factor Authentication) cho GP247

## Tổng quan

Plugin MFA cung cấp xác thực 2 lớp (Two-Factor Authentication) cho hệ thống GP247, tăng cường bảo mật tài khoản người dùng. Plugin hỗ trợ đa guards (customer, admin, vendor, pmo...) cho phép linh hoạt trong việc quản lý xác thực người dùng.

## Minh họa giao diện

<p align="center">
  <img src="https://static.gp247.net/product/mfa-user.jpg" alt="Giao diện người dùng GP247 - Đăng ký/Quản lý MFA" />
  <img src="https://static.gp247.net/product/mfa-admin.jpg" alt="Giao diện quản trị viên GP247 - Cài đặt/Quản lý MFA" />
  <img src="https://static.gp247.net/product/mfa-process.jpg" alt="Quy trình xác thực Multi-Factor Authentication trên GP247"/>
</p>


## Tính năng

- ✅ Xác thực 2 lớp sử dụng TOTP (Time-based One-Time Password)
- ✅ Hỗ trợ nhiều guards: customer, admin, vendor, pmo
- ✅ Mã QR dễ dàng quét bằng ứng dụng xác thực
- ✅ Mã khôi phục (Recovery Codes) khi mất thiết bị
- ✅ Quản lý MFA từ Admin panel
- ✅ Thống kê tỷ lệ sử dụng MFA theo guard
- ✅ Có thể bắt buộc MFA cho từng guard
- ✅ Giao diện thân thiện và responsive
- ✅ Đa ngôn ngữ (Tiếng Việt, Tiếng Anh)

## Yêu cầu hệ thống

- GP247 Core >= 1.2
- Laravel 12.x
- PHP >= 8.2
- Các packages:
  - pragmarx/google2fa: ^8.0 hoặc ^9.0
  - bacon/bacon-qr-code: ^2.0 hoặc ^3.0

## Cài đặt

### Bước 1: Cài đặt các package phụ thuộc

Thêm các packages vào `composer.json` của project:

```bash
composer require pragmarx/google2fa
composer require bacon/bacon-qr-code
```

**Lưu ý**: Chạy lệnh này ở thư mục gốc của project GP247, không phải trong thư mục plugin.

### Bước 2: Cài đặt plugin

1. Copy thư mục plugin vào `app/GP247/Plugins/MFA`
2. Truy cập Admin Panel > Extensions > Plugins
3. Tìm "MFA" và click "Install"
4. Plugin sẽ tự động tạo database tables và cấu hình mặc định
5. Click "Enable" để kích hoạt plugin

### Bước 3: Cấu hình

1. Truy cập Admin Panel > Extensions > Plugins
2. Click vào plugin "MFA" để mở trang cấu hình
3. Cấu hình cho từng guard:
   - **Enabled**: Bật/tắt MFA cho guard
   - **Forced**: Bắt buộc người dùng phải bật MFA
   - **QR Code Size**: Kích thước mã QR (100-500px)
   - **Recovery Codes Count**: Số lượng mã khôi phục (4-20)
   - **Window**: Cửa sổ thời gian cho phép (0-5, khuyến nghị 1)
4. Click "Save Settings" để lưu

## Sử dụng

### Cho người dùng cuối

#### Thiết lập MFA

1. Truy cập trang quản lý MFA: `/mfa/setup/customer`
2. Quét mã QR bằng ứng dụng xác thực (Google Authenticator, Authy, Microsoft Authenticator, v.v.)
3. Nhập mã 6 chữ số từ ứng dụng để xác minh
4. Lưu các mã khôi phục ở nơi an toàn

#### Đăng nhập với MFA

1. Đăng nhập bằng username/password như bình thường
2. Sau khi đăng nhập thành công, hệ thống sẽ yêu cầu mã MFA
3. Nhập mã 6 chữ số từ ứng dụng xác thực
4. Hoặc sử dụng mã khôi phục 8 ký tự nếu mất thiết bị

#### Quản lý MFA

- Xem trạng thái MFA: `/mfa/manage/customer`
- Xem mã khôi phục: `/mfa/recovery-codes/customer`
- Tạo lại mã khôi phục: Từ trang quản lý MFA
- Tắt MFA: Từ trang quản lý MFA (yêu cầu xác nhận mật khẩu)

### Cho Admin

#### Xem thống kê

- Truy cập Admin Panel > Extensions > MFA
- Xem tỷ lệ người dùng đã bật MFA theo từng guard
- Xem tổng số người dùng và số người đã bật MFA

#### Cấu hình guards

- Bật/tắt MFA cho từng guard
- Bắt buộc người dùng phải bật MFA
- Điều chỉnh các thông số kỹ thuật

#### Reset MFA cho người dùng

- Từ trang quản lý MFA, Admin có thể reset MFA cho bất kỳ người dùng nào
- Người dùng sẽ phải thiết lập MFA lại từ đầu

## Luồng Xử Lý MFA

### 1. Luồng Setup MFA (Lần Đầu)

```
┌─────────────────────────────────────────────────────────────┐
│                    User Login Success                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
                ┌────────────────────┐
                │  MFA Enabled cho   │
                │  guard này?        │
                └────────┬───────────┘
                         │
        ┌────────────────┴────────────────┐
        │ No                               │ Yes
        ▼                                  ▼
┌───────────────┐              ┌────────────────────┐
│  Allow Access │              │ User đã enroll MFA?│
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

### 2. Luồng Login với MFA (Đã Setup)

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

### 3. Luồng Sử Dụng Recovery Code

```
┌──────────────────────┐
│ User mất điện thoại  │
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

## Tích hợp vào code

### Middleware

Sử dụng middleware `mfa.verify` để bảo vệ các routes:

```php
// Trong routes/web.php
Route::group(['middleware' => ['auth:customer', 'mfa.verify:customer']], function () {
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/profile', 'ProfileController@index');
});

// Cho admin
Route::group(['middleware' => ['auth:admin', 'mfa.verify:admin']], function () {
    Route::get('/admin/dashboard', 'AdminController@index');
});
```

### Helper functions

```php
// Kiểm tra MFA có được bật cho guard không
if (mfa_is_enabled_for_guard('customer')) {
    // MFA is enabled
}

// Kiểm tra user đã đăng ký MFA chưa
if (mfa_is_user_enrolled($user, 'customer')) {
    // User has enrolled MFA
}

// Kiểm tra session đã verify MFA chưa
if (mfa_is_verified()) {
    // Session is verified
}

// Đánh dấu session đã verify
mfa_set_verified();

// Xóa verify khỏi session
mfa_clear_verified();

// Tạo link đến các trang MFA
route('mfa.setup.show', 'customer')        // /mfa/setup/customer
route('mfa.manage', 'customer')            // /mfa/manage/customer
route('mfa.recovery_codes', 'customer')    // /mfa/recovery-codes/customer
route('mfa.verify.show', 'customer')       // /mfa/verify/customer
```

## Cấu trúc thư mục

```
app/GP247/Plugins/MFA/
├── Admin/
│   └── AdminController.php          # Controller cho admin panel
├── Controllers/
│   └── MFAController.php            # Controller chính cho MFA
├── Helpers/
│   └── TwoFactorHelper.php          # Helper cho Google 2FA
├── Lang/
│   ├── en/
│   │   └── lang.php                 # Ngôn ngữ tiếng Anh
│   └── vi/
│       └── lang.php                 # Ngôn ngữ tiếng Việt
├── Middleware/
│   └── TwoFactorAuthentication.php # Middleware xác thực MFA
├── Models/
│   ├── ExtensionModel.php           # Model cài đặt/gỡ bỏ
│   └── TwoFactorAuth.php            # Model lưu trữ MFA
├── Views/
│   ├── Admin.blade.php              # View admin
│   └── Frontend/
│       ├── setup.blade.php          # View thiết lập MFA
│       ├── verify.blade.php         # View xác thực MFA
│       ├── recovery_codes.blade.php # View mã khôi phục
│       └── manage.blade.php         # View quản lý MFA
├── AppConfig.php                     # Cấu hình plugin
├── config.php                        # File cấu hình
├── function.php                      # Helper functions
├── gp247.json                        # Metadata plugin
├── Provider.php                      # Service provider
├── Route.php                         # Routes
└── readme_vi.md                      # Tài liệu này
```

## Bảo mật

### Mã hóa

- Secret key được mã hóa trong database sử dụng Laravel Encryption
- Recovery codes được mã hóa trong database
- Tất cả dữ liệu nhạy cảm đều được bảo vệ

### Best Practices

1. **Bắt buộc MFA cho Admin**: Nên bật "Forced" cho guard admin
2. **Giáo dục người dùng**: Hướng dẫn người dùng lưu mã khôi phục an toàn
3. **Kiểm tra định kỳ**: Theo dõi tỷ lệ sử dụng MFA
4. **Backup**: Đảm bảo database được backup thường xuyên
5. **HTTPS**: Bắt buộc sử dụng HTTPS cho tất cả các trang MFA

## Ứng dụng xác thực được hỗ trợ

Plugin hỗ trợ tất cả các ứng dụng xác thực TOTP:

- Google Authenticator (iOS, Android)
- Microsoft Authenticator (iOS, Android)
- Authy (iOS, Android, Desktop)
- 1Password (iOS, Android, Desktop)
- LastPass Authenticator
- Duo Mobile
- FreeOTP
- Và nhiều ứng dụng TOTP khác

## Xử lý sự cố

### Người dùng mất thiết bị xác thực

1. Người dùng sử dụng mã khôi phục để đăng nhập
2. Sau khi đăng nhập, thiết lập MFA lại với thiết bị mới
3. Lưu mã khôi phục mới

### Mã xác thực không hoạt động

1. Kiểm tra thời gian trên thiết bị có chính xác không
2. Tăng giá trị "Window" trong cấu hình (khuyến nghị 1-2)
3. Đảm bảo đã quét đúng mã QR

### Admin muốn reset MFA cho người dùng

1. Truy cập Admin Panel > Extensions > MFA
2. Tìm người dùng trong danh sách
3. Click "Reset MFA"
4. Người dùng sẽ phải thiết lập MFA lại

## Hỗ trợ

- Website: https://GP247.net
- Email: support@gp247.net
- Documentation: https://gp247.net/vi/product/plugin-mfa.html

## Giấy phép

MIT License

## Tác giả

GP247 Team

## Changelog

### Version 1.0
- Initial release
- Support for Customer, Admin, Vendor, PMO guards
- TOTP authentication with Google Authenticator
- Recovery codes system
- Admin management panel
- Multi-language support (Vietnamese, English)

