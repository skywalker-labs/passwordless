# Contributing to Skywalker Passwordless

Contributions are welcome and will be fully credited. Please read this guide before submitting a pull request.

---

## 🔀 Pull Request Process

1. **Fork** the repository and create your branch from `main`.
2. **Write tests** for any new functionality or bug fix.
3. **Update documentation** if you change any public API.
4. **Ensure the full test suite passes** before submitting.
5. **Ensure PHPStan Level 9 passes** with zero errors.
6. **Format your code** with Laravel Pint before submitting.
7. **Open the pull request** with a clear description of what changed and why.

---

## 🧪 Development Commands

```bash
# Run the test suite
composer test

# Run static analysis (PHPStan Level 9)
composer analyse

# Format code with Laravel Pint (PSR-12)
composer format
```

---

## 🏗️ Architecture Guidelines

This package follows a strict **Action-Oriented + Domain-Driven** architecture:

- **One Action class per use case** — each extending `Skywalker\Support\Actions\Action`.
- **No business logic in the controller** — the controller is a thin HTTP adapter only.
- **No business logic in the service** — `OtpService` is a thin orchestrator that delegates to Actions.
- **Interfaces over concretions** — always inject `OtpStore`, `OtpSender`, `OtpService` contracts, not concrete classes.
- **Value Objects are immutable** — all `OtpToken` properties must be `readonly`.
- **Events for side-effects** — use `OtpGenerated`, `OtpVerified`, `OtpFailed` instead of coupling logic to the action.

---

## 🔒 Code Style

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards.
- Use `declare(strict_types=1)` at the top of every PHP file.
- Always add proper return types and parameter types — no `mixed` where avoidable.
- Use PHP 8.2+ features: `readonly` properties, named arguments, match expressions, enums.

---

## 🧩 Adding a New Feature

1. **Define a contract** in `src/Domain/Contracts/` if the feature is extensible.
2. **Create an Action** in `src/Actions/` — extend `Skywalker\Support\Actions\Action` and implement `execute(...$args)`.
3. **Add a method** to `OtpService` that delegates to the new Action.
4. **Dispatch an event** for the happy path and error path (if applicable).
5. **Write a feature test** in `tests/Feature/`.
6. **Update `README.md`** and **`CHANGELOG.md`** with the new feature.

---

## 🔐 Security Vulnerabilities

If you discover a security vulnerability, please email **skywalkerlknw@gmail.com** instead of opening a public issue. All security issues will be addressed promptly.

---

## 📄 License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).
