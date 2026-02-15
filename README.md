# Skywalker Passwordless (OTP) Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![Total Downloads](https://img.shields.io/packagist/dt/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![License](https://img.shields.io/packagist/l/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)

Seamless Passwordless Authentication for Laravel. Integrate OTP (One-Time Password) Login and 2FA into your default authentication flow with zero-conf middleware and ready-to-use UI. Built on top of [Skywalker Toolkit](https://github.com/skywalker-labs/toolkit).

## Features

- **Automatic Integration**: Simply install the package and add the `HasOtp` trait to your User model.
- **Middleware Protection**: Automatically intercepts authenticated users and redirects them to OTP verification.
- **Multi-channel Notifications**: Supports Email, Slack, SMS (Twilio), and Log channels out of the box.
- **Flexible Storage**: Store OTPs in Cache or Database.
- **Magic Login Links**: Secure, signed temporary links for one-click login.
- **Backup Codes**: Emergency access codes for when the secondary device is unavailable.
- **Premium UI**: Ready-to-use views for OTP verification.
- **Rate Limiting**: Built-in protection against brute-force attacks.

## Installation

You can install the package via composer:

```bash
composer require skywalker-labs/passwordless
```

The package will automatically register its service provider.

## Setup

### 1. Prepare your User Model

Add the `HasOtp` trait to your `User` model:

```php
use Skywalker\Otp\Traits\HasOtp;

class User extends Authenticatable
{
    use HasOtp;
}
```

### 2. Publish Assets

Publish the configuration and migration files:

```bash
php artisan vendor:publish --tag=passwordless-config
php artisan vendor:publish --tag=passwordless-migrations
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file allows you to customize the OTP behavior:

```php
// config/passwordless.php

return [
    'length' => 6,
    'expiry' => 10, // minutes
    'driver' => 'cache', // cache or database
    'channel' => 'mail', // mail, log, sms, slack
    'services' => [
        'twilio' => [ ... ],
        'slack' => [ ... ],
    ],
];
```

## Usage

### OTP Verification Flow

Once a user logs in, the package automatically listens for the `Login` event and sends an OTP if the user has the `HasOtp` trait. The `otp.verified` middleware is automatically pushed to the `web` middleware group.

Any route protected by the `web` (and optionally `auth`) middleware will redirect the user to `/otp/verify` if they haven't verified their OTP yet.

### Protecting Specific Routes

You can manually apply the `otp.verified` middleware to specific groups:

```php
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```

### Magic Login Links

Generate a magic login link:

```php
$link = app('otp')->generateMagicLink($user->email);
// Send this link to the user
```

### Backup Codes

Generate backup codes:

```php
$codes = app('otp')->generateBackupCodes($user->email);
```

Verify a backup code:

```php
if (app('otp')->verifyBackupCode($user->email, $request->code)) {
    // Verified!
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
