# Skywalker Passwordless (OTP) Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![Total Downloads](https://img.shields.io/packagist/dt/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![License](https://img.shields.io/packagist/l/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)

Seamless Passwordless Authentication for Laravel. Integrate OTP (One-Time Password) Login and 2FA into your default authentication flow with zero-conf middleware and ready-to-use UI.

## Features

- **Automatic Integration**: Simply install the package and add the `HasOtp` trait to your User model.
- **Middleware Protection**: Automatically intercepts authenticated users and redirects them to OTP verification.
- **Session Based**: Works with standard Laravel Session authentication (Breeze, Jetstream, etc.).
- **Multi-channel**: Supports Email and Log drivers (with hooks for SMS).
- **Customizable**: Fully customizable views and configuration.
- **Advanced Features**: Supports Magic Login Links and Backup Codes out of the box.

## Installation

You can install the package via composer:

```bash
composer require skywalker-labs/passwordless
```

## Setup

1. Add the `HasOtp` trait to your `User` model:

```php
use Skywalker\Otp\Traits\HasOtp;

class User extends Authenticatable
{
    use HasOtp;
}
```

2. (Optional) Publish the config file and views:

```bash
php artisan vendor:publish --tag=otp-config
php artisan vendor:publish --tag=otp-views
```

## Usage

Once the trait is added, any user log-in event will automatically trigger an OTP generation and redirect the user to the verification page if they access a protected route.

### Protecting Routes

The package automatically adds an `otp.verified` middleware alias. It is also pushed to the `web` group by default, but you can manually apply it to specific routes:

```php
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```

### Magic Links

Generate a magic login link for a user:

```php
$link = app('otp')->generateMagicLink($user->email);
// Send $link via email
```

### Backup Codes

Generate backup codes for emergency access:

```php
$codes = app('otp')->generateBackupCodes($user->email);
```

Check a backup code:

```php
if (app('otp')->verifyBackupCode($user->email, $request->code)) {
    // Authenticate user...
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
