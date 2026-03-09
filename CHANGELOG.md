# Changelog

All notable changes to `skywalker-labs/passwordless` will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## v2.0.0 — 2026-03-09

### 💥 Breaking Changes
- Action classes no longer accept constructor injection. Dependencies (`OtpStore`, `OtpSender`) are now threaded through `execute(...$args)` to comply with the toolkit's `@phpstan-consistent-constructor` contract.
- `HasOtp::sendOtp()` now returns `string` (the OTP). Previously returned `mixed`.
- `HasOtp::verifyOtp()` now returns `bool` (previously `mixed`).
- `OtpToken` properties are now `readonly` — you can no longer reassign them after construction.
- `OtpVerified` event properties are now `readonly` via constructor promotion.
- Backup codes are now **hashed** before storage. Existing plain-text backup codes in the database **must be regenerated** after upgrading.

### ✨ Added
- **Action-Oriented Architecture**: Each use case is a dedicated Action class (`GenerateOtp`, `VerifyOtp`, `GenerateBackupCodes`, `VerifyBackupCode`, `GenerateMagicLink`) all extending `Skywalker\Support\Actions\Action`.
- **Toolkit Foundation**: Package now builds on `skywalker-labs/toolkit` v1.4+:
  - Actions extend `Skywalker\Support\Actions\Action`
  - `OtpToken` extends `Skywalker\Support\Data\ValueObject`
  - `OtpException` extends `Skywalker\Support\Exceptions\PackageException`
  - `OtpService` extends `Skywalker\Support\Services\BaseService`
  - `OtpServiceProvider` extends `Skywalker\Support\Providers\PackageServiceProvider`
- **Hashed Backup Codes**: `GenerateBackupCodes` now stores codes with `Hash::make()`. `VerifyBackupCode` now uses `Hash::check()` — eliminates plain-text comparison.
- **Domain/Infrastructure Separation**: Strict layer boundaries with `Domain/Contracts`, `Domain/ValueObjects`, `Infrastructure/Persistence`, `Infrastructure/Delivery`.
- **OtpGenerated Event**: Dispatched after every successful OTP generation.
- **OtpFailed Event**: Dispatched when delivery fails (e.g. mail/SMS error).
- **Rate Limiting**: Built-in per-identifier rate limits on `sendOtp` (3/min) and `verifyOtp` (5/min) in the controller.
- **Multi-Channel Notification Sender**: `NotificationSender` routes OTPs to `mail`, `log`, `sms`, or `slack` channels.
- **Laravel Pint**: Added `composer format` script for PSR-12 formatting.

### 🔧 Changed
- `OtpAuthController` now injects `OtpService` **interface** (`OtpServiceContract`) instead of the concrete class.
- `routes/web.php` no longer applies a redundant `web` middleware group — this is applied once in the ServiceProvider.
- `HasOtp` trait: generic `\Exception` replaced with `\RuntimeException`.
- `OtpService` cleaned of 6 unused imports (`Cache`, `DB`, `Log`, `Mail`, `Hash`, `Str`, `Carbon`, `OtpToken`, `OtpDeliveryFailedException`).
- Config key `channel` is now correctly read as `passwordless.channel` (was mistakenly read as `passwordless.default_channel` in an earlier version).
- All source files: `declare(strict_types=1)` enforced globally.

### 🛡️ Security
- Backup codes are now **hashed at rest** using `Hash::make()`. Previously stored as plain-text (critical security gap).
- Backup code verification uses constant-time `Hash::check()`, eliminating timing-attack risk.

---

## v1.0.0 — 2026-02-14

### Added
- Initial release.
- Seamless Hybrid Authentication with OTP.
- Magic Login Links.
- Backup Codes support.
- Middleware protection.
- Ready-to-use Blade views.
