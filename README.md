# Skywalker Passwordless (OTP) Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![Total Downloads](https://img.shields.io/packagist/dt/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![License](https://img.shields.io/packagist/l/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg?style=flat-square)](https://www.php.net/)

Seamless Passwordless Authentication for Laravel. Integrate OTP (One-Time Password) Login and 2FA into your default authentication flow with zero-conf middleware and ready-to-use UI. Built on top of [Skywalker Toolkit](https://github.com/skywalker-labs/toolkit).

## 🚀 Features

- **Hashed OTP Storage**: Tokens are stored securely using Laravel's hashing (Bcrypt/Argon2).
- **Event-Driven Login**: Verification logic is decoupled via the `OtpVerified` event.
- **Custom Exceptions**: Granular error handling with `InvalidOtpException` and `OtpDeliveryFailedException`.
- **Extensible OTP Generation**: Runtime customizable OTP generators.
- **Contract-Based Design**: Fully compliant with modern Laravel patterns using Interfaces and Facades.
- **Automatic Event Listening**: Automatically intercepts logins and triggers OTP verification.
- **Middleware Protection**: `otp.verified` middleware protects your routes with built-in throttling.
- **Multi-channel Notifications**: Supports **Email**, **Slack**, **SMS (Twilio)**, and **Log** channels.
- **Magic Login Links**: Signed, temporary links for one-click authentication.
- **Backup Codes**: Emergency access codes for account recovery.

## 📦 Installation

```bash
composer require skywalker-labs/passwordless
```

## 🛠️ Setup

### 1. Prepare your User Model

Add the `HasOtp` trait to your `User` model.

```php
use Skywalker\Otp\Traits\HasOtp;

class User extends Authenticatable
{
    use HasOtp; 
}
```

### 2. Publish Assets & Migrate

```bash
php artisan vendor:publish --tag=passwordless-config
php artisan vendor:publish --tag=passwordless-migrations
php artisan migrate
```

## 🎯 Usage

### 🚀 Using the Facade (Industry Standard)

The package provides an expressive Facade for all operations:

```php
use Skywalker\Otp\Facades\Otp;

// Generate OTP
$otp = Otp::generate($email);

// Verify OTP
try {
    Otp::verify($email, $token);
} catch (InvalidOtpException $e) {
    // Handle failure
}
```

### 🛡️ Dependency Injection

For better testability, inject the `OtpService` contract:

```php
use Skywalker\Otp\Contracts\OtpService;

public function __construct(OtpService $otp)
{
    $this->otp = $otp;
}
```

### ⚡ Customizing OTP Generation

```php
use Skywalker\Otp\Facades\Otp;

Otp::useGenerator(fn() => (string) random_int(1000, 9999));
```

### 🔔 Handling Verification Events

Listen for the `OtpVerified` event to implement custom post-login logic:

```php
use Skywalker\Otp\Events\OtpVerified;

// In your EventServiceProvider
protected $listen = [
    OtpVerified::class => [
        HandleSuccessfulOtp::class,
    ],
];
```

## 🧪 Testing

```bash
composer test
```

## 🛡️ Security & Quality

- **Strict Typing**: All files use `declare(strict_types=1);`.
- **Static Analysis**: Compliant with **PHPStan/Larastan**.
- **Hashed Storage**: OTPs are never stored in plain text.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md).
