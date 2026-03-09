<?php

declare(strict_types=1);

namespace Skywalker\Otp\Rules;

use Illuminate\Contracts\Validation\Rule;
use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;

class ValidOtp implements Rule
{
    protected string $identifier;
    protected OtpServiceContract $otpService;

    /**
     * Create a new rule instance.
     *
     * @param  string  $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        
        /** @var OtpServiceContract $service */
        $service = app(OtpServiceContract::class);
        $this->otpService = $service;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        $otpValue = is_string($value) ? $value : (is_numeric($value) ? (string) $value : '');
        return $this->otpService->verify($this->identifier, $otpValue);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid or expired.';
    }
}
