# Skywalker Passwordless (OTP) Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![Total Downloads](https://img.shields.io/packagist/dt/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![License](https://img.shields.io/packagist/l/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg?style=flat-square)](https://www.php.net/)

Seamless Passwordless Authentication for Laravel. Integrate OTP (One-Time Password) Login and 2FA into your default authentication flow with zero-conf middleware and ready-to-use UI. Built on top of [Skywalker Toolkit](https://github.com/skywalker-labs/toolkit).

## 🚀 Features

- **Automatic Event Listening**: Automatically intercepts logins and triggers OTP verification.
- **Middleware Protection**: `otp.verified` middleware protects your routes and redirects to verification when needed.
- **Multi-channel Notifications**: Supports **Email**, **Slack**, **SMS (Twilio)**, and **Log** channels out of the box.
- **Flexible Storage**: Choose between `Cache` (fast) or `Database` (persistent) for OTP storage.
- **Magic Login Links**: Signed, temporary links for seamless one-click authentication.
- **Backup Codes**: Emergency alphanumeric access codes for account recovery.
- **Premium UI**: Beautiful, ready-to-use Tailwind-friendly views for OTP verification.
- **Security First**: Level 9 Static Analysis (PHPStan) compliance and built-in rate limiting.

## 📦 Installation

You can install the package via composer:

```bash
composer require skywalker-labs/passwordless
```

## 🛠️ Setup

### 1. Prepare your User Model

Add the `HasOtp` trait to your `User` model. This allows the package to generate and verify codes for the user.

```php
use Skywalker\Otp\Traits\HasOtp;

class User extends Authenticatable
{
    use HasOtp; // 👈 Add this
}
```

### 2. Publish Assets & Migrate

Publish the configuration and migrations, then run the database updates:

```bash
php artisan vendor:publish --tag=passwordless-config
php artisan vendor:publish --tag=passwordless-migrations
php artisan migrate
```

## ⚙️ Configuration

The configuration file `config/passwordless.php` allows you to customize every aspect:

```php
return [
    'length' => 6,        // Number of digits (default: 6)
    'expiry' => 10,       // Expiry in minutes (default: 10)
    'driver' => 'cache',  // Storage driver: 'cache' or 'database'
    'channel' => 'mail',  // Default delivery channel: 'mail', 'log', 'sms', 'slack'
    
    'services' => [
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        'slack' => [
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
    ],
];
```

## 🎯 Usage

### Automatic OTP Flow

By default, the package listens for the standard Laravel `Login` event. If a user logs in and has the `HasOtp` trait, they will be redirected to the OTP verification screen before they can access any `web` middleware routes.

### Protecting Routes

The `otp.verified` middleware is automatically pushed to the `web` group. If you want to protect specific route groups or API endpoints:

```php
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/vault', [SecuredController::class, 'index']);
});
```

### Advanced API (`OtpService`)

You can access the core service through the `otp` facade or helper:

#### Magic Login Links
```php
$url = app('otp')->generateMagicLink($user->email);
// Send $url via any custom channel
```

#### Backup Codes
```php
// Generate 8 new alphanumeric backup codes
$codes = app('otp')->generateBackupCodes($user->email, 8);

// Verify and consume a backup code
if (app('otp')->verifyBackupCode($user->email, 'CODE-1234')) {
    // Access Granted
}
```

## 🧪 Testing

The package is built with testing in mind. Run the suite using:

```bash
composer test
```

## 🛡️ Static Analysis

This package is strictly typed and compliant with **PHPStan Level 9**.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
