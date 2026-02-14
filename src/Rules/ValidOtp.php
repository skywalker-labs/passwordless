<?php

namespace Skywalker\Otp\Rules;

use Illuminate\Contracts\Validation\Rule;
use Skywalker\Otp\Services\OtpService;

class ValidOtp implements Rule
{
    protected $identifier;
    protected $otpService;

    /**
     * Create a new rule instance.
     *
     * @param  string  $identifier
     * @return void
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        $this->otpService = app('otp');
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

        return $this->otpService->verify($this->identifier, $value);
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
