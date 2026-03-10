<?php

declare(strict_types=1);

namespace Skywalker\Otp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;

class ValidOtp implements ValidationRule
{
    protected string $identifier;

    protected OtpServiceContract $otpService;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;

        /** @var OtpServiceContract $service */
        $service = app(OtpServiceContract::class);
        $this->otpService = $service;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            $fail('The :attribute is required.');

            return;
        }

        $otpValue = is_string($value) ? $value : (is_numeric($value) ? (string) $value : '');

        if (! $this->otpService->verify($this->identifier, $otpValue)) {
            $fail('The :attribute is invalid or expired.');
        }
    }
}
