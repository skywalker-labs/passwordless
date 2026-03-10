# Security Policy

## Supported Versions

Only the latest major version of Skywalker Passwordless receives security updates.

| Version | Supported          |
| ------- | ------------------ |
| 2.x     | :white_check_mark: |
| 1.x     | :x:                |

## Security Architecture

We apply a multi-layered defense strategy:

1.  **Hashed Persistance**: OTPs and Backup Codes are never stored as plain-text. We use Laravel's `Hash` facade (Argon2 or Bcrypt) to store cryptographically secure hashes.
2.  **Risk-Based Authentication**: Integration with the `TrustEngine` allows for adaptive security measures based on user behavior and environment.
3.  **User Enumeration Protection**: Critical endpoints return generic responses to prevent sensitive information disclosure.
4.  **Rate Limiting**: Integrated throttling on all authentication routes prevents brute-force and denial-of-service attacks.
5.  **Timing-Attack Protection**: Verification logic uses constant-time string comparisons.
6.  **Extreme Static Analysis**: We maintain 100% compliance with `phpstan-strict-rules` to eliminate logic bypasses and type-related vulnerabilities.

## Reporting a Vulnerability

If you discover a security vulnerability within Skywalker Passwordless, please send an e-mail to Mradul Sharma via [skywalkerlknw@gmail.com](mailto:skywalkerlknw@gmail.com). All security vulnerabilities will be promptly addressed.

Please do not report security vulnerabilities via public GitHub issues.
