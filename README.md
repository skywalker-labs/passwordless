# Skywalker Passwordless — OTP & Magic Link Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![Total Downloads](https://img.shields.io/packagist/dt/skywalker-labs/passwordless.svg?style=flat-square)](https://packagist.org/packages/skywalker-labs/passwordless)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-Level%209-brightgreen.svg?style=flat-square)](https://phpstan.org)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg?style=flat-square)](https://www.php.net/)
[![License](https://img.shields.io/packagist/l/skywalker-labs/passwordless.svg?style=flat-square)](LICENSE.md)

Elegant **passwordless authentication** for Laravel. Drop-in OTP login, 2FA enforcement, magic links, and backup codes — all built on [Skywalker Toolkit](https://github.com/skywalker-labs/toolkit) with action-oriented architecture, contract-based design, and **PHPStan Level 9** compliance.

---

## ✨ Features

| Feature | Detail |
|---|---|
| **OTP Login** | Generate & verify time-limited one-time passwords |
| **Hashed Storage** | OTPs and backup codes stored with `Hash::make()` — never plain-text |
| **Magic Login Links** | Signed, temporary URLs for one-click authentication |
| **Backup Codes** | Hashed emergency recovery codes |
| **Multi-Channel** | Email, SMS (Twilio), Slack, and Log channels |
| **Event-Driven** | `OtpGenerated`, `OtpVerified`, `OtpFailed` events for full extensibility |
| **Middleware Gate** | `otp.verified` middleware with infinite-loop protection |
| **Rate Limiting** | Built-in per-identifier request throttling on all routes |
| **Action Architecture** | Each operation is a dedicated Action class (SRP) |
| **PHPStan Level 9** | Zero static analysis errors across the entire codebase |
| **Strict Types** | `declare(strict_types=1)` everywhere |

---

## 📦 Installation

```bash
composer require skywalker-labs/passwordless
```

> **Requires:** PHP ≥ 8.2, Laravel ≥ 11.0

---

## 🛠️ Setup

### 1. Add the `HasOtp` Trait to Your User Model

```php
use Skywalker\Otp\Concerns\HasOtp;

class User extends Authenticatable
{
    use HasOtp;
}
```

The trait provides `sendOtp(): string` and `verifyOtp(string $token): bool` methods.

### 2. Publish Config & Migrations

```bash
php artisan vendor:publish --tag=passwordless-config
php artisan vendor:publish --tag=passwordless-migrations
php artisan migrate
```

### 3. Configure (`config/passwordless.php`)

```php
return [
    'length'     => 6,            // OTP digit length
    'expiry'     => 10,           // Minutes until OTP expires
    'driver'     => 'cache',      // 'cache' or 'database'
    'channel'    => 'mail',       // 'mail' | 'log' | 'sms' | 'slack'
    'middleware' => ['web', 'throttle:6,1'],

    'services'   => [
        'twilio' => [
            'sid'   => env('TWILIO_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from'  => env('TWILIO_FROM'),
        ],
        'slack'  => [
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
    ],
];
```

---

## 🎯 Usage

### Using the Facade

```php
use Skywalker\Otp\Facades\Otp;

// Send an OTP to an email or phone
$otp = Otp::generate('user@example.com');

// Verify the submitted OTP
try {
    Otp::verify('user@example.com', $request->otp);
} catch (\Skywalker\Otp\Exceptions\InvalidOtpException $e) {
    // Invalid or expired OTP
}
```

### Dependency Injection (Recommended)

Inject the contract for testable, SOLID-compliant code:

```php
use Skywalker\Otp\Domain\Contracts\OtpService;

public function __construct(private readonly OtpService $otp) {}

public function send(string $identifier): void
{
    $this->otp->generate($identifier);
}
```

### Magic Login Links

```php
// Generate a signed link valid for 15 minutes (configurable via 'expiry')
$link = Otp::generateMagicLink('user@example.com');
// → https://your-app.com/magic-login?identifier=...&signature=...

// Route: GET /magic-login  → passwordless.magic-login
// Validated automatically by hasValidSignature() in the controller
```

### Backup Codes

```php
// Generate 8 hashed recovery codes (stored securely in DB)
$codes = Otp::generateBackupCodes('user@example.com');
// Returns: ['AbcD123fGh', 'xYz987Qrst', ...]  ← shown once, stored hashed

// Verify & consume a backup code (uses Hash::check internally)
$ok = Otp::verifyBackupCode('user@example.com', $submittedCode);
```

### Custom OTP Generator

```php
use Skywalker\Otp\Facades\Otp;

// Use a custom generator at runtime (e.g. alphanumeric, UUID-style)
Otp::useGenerator(fn() => strtoupper(substr(md5(microtime()), 0, 6)));
```

### Listen to Events

```php
// In your EventServiceProvider or a Listener
use Skywalker\Otp\Events\OtpVerified;
use Skywalker\Otp\Events\OtpGenerated;
use Skywalker\Otp\Events\OtpFailed;

protected $listen = [
    OtpVerified::class  => [LogSuccessfulLogin::class],
    OtpGenerated::class => [NotifySecurityTeam::class],
    OtpFailed::class    => [AlertOnRepeatedFailures::class],
];
```

### Middleware Gate

Add the `otp.verified` middleware to any route to enforce OTP verification before access:

```php
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

The middleware automatically:
- Skips users without the `HasOtp` trait
- Allows access once `otp_verified` is set in the session
- Excludes OTP verify routes to prevent redirect loops

---

## 🏗️ Architecture

The package follows a strict **Action-Oriented + Domain-Driven** architecture:

```
src/
├── Actions/                    ← One class per operation (SRP)
│   ├── GenerateOtp             ← Hash::make() + event dispatch
│   ├── VerifyOtp               ← Hash::check() + timing-safe
│   ├── GenerateBackupCodes     ← Hash::make() per code
│   ├── VerifyBackupCode        ← Hash::check() against stored hashes
│   └── GenerateMagicLink       ← Signed URL generation
├── Concerns/
│   └── HasOtp                  ← User model trait (sendOtp / verifyOtp)
├── Domain/
│   ├── Contracts/              ← OtpStore, OtpSender, OtpService interfaces
│   └── ValueObjects/
│       └── OtpToken            ← Immutable (readonly) value object
├── Events/
│   ├── OtpGenerated, OtpFailed, OtpVerified
├── Exceptions/
│   ├── OtpException            ← Extends PackageException (toolkit)
│   ├── InvalidOtpException
│   └── OtpDeliveryFailedException
├── Facades/
│   └── Otp                     ← Static access via Laravel facade
├── Http/
│   ├── Controllers/OtpAuthController  ← Injects OtpService contract
│   └── Middleware/EnsureOtpVerified
├── Infrastructure/
│   ├── Delivery/NotificationSender    ← Multi-channel sender
│   └── Persistence/
│       ├── CacheOtpStore              ← driver=cache
│       └── DatabaseOtpStore           ← driver=database
├── Services/
│   └── OtpService              ← Orchestrator, delegates to Actions
└── OtpServiceProvider.php      ← Bindings, routes, events, middleware
```

**Toolkit foundation:**

| Our Class | Extends |
|---|---|
| All 5 Action classes | `Skywalker\Support\Actions\Action` |
| `OtpToken` | `Skywalker\Support\Data\ValueObject` |
| `OtpException` | `Skywalker\Support\Exceptions\PackageException` |
| `OtpService` | `Skywalker\Support\Services\BaseService` |
| `OtpServiceProvider` | `Skywalker\Support\Providers\PackageServiceProvider` |

---

## 🧪 Testing & Analysis

```bash
# Run tests
composer test

# Static analysis (PHPStan Level 9)
composer analyse

# Format code (Laravel Pint / PSR-12)
composer format
```

---

## 🔒 Security & Quality

- **PHPStan Level 9** — zero static analysis errors
- **Hashed OTPs** — stored with `Hash::make()`, verified with `Hash::check()`
- **Hashed Backup Codes** — same approach as OTPs
- **Signed Magic Links** — protection against link tampering
- **Rate Limiting** — 3 send / 5 verify attempts per minute per identifier
- **Strict Types** — `declare(strict_types=1)` in all source files

---

## 🛣️ Available Routes

| Method | URI | Name | Auth |
|---|---|---|---|
| `POST` | `/otp/send` | `otp.send` | Public |
| `POST` | `/otp/verify` | `otp.verify` | Public |
| `GET` | `/otp/verify` | `otp.verify.view` | `auth` |
| `POST` | `/otp/verify-submit` | `otp.verify.submit` | `auth` |
| `POST` | `/otp/resend` | `otp.resend` | `auth` |
| `GET` | `/magic-login` | `passwordless.magic-login` | Signed URL |

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md).
