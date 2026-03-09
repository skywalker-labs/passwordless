<?php

namespace Skywalker\Otp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Skywalker\Otp\Services\OtpService;
use Illuminate\Support\Facades\RateLimiter;
use Skywalker\Otp\Events\OtpVerified;
use Skywalker\Support\Http\Concerns\ApiResponse;

class OtpAuthController extends Controller
{
    use ApiResponse;

    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string', // validate email or phone
        ]);

        $identifierInput = $request->input('identifier', '');
        $identifier = is_string($identifierInput) ? $identifierInput : '';

        $userModelConfig = config('auth.providers.users.model', 'App\\Models\\User');
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = is_string($userModelConfig) ? $userModelConfig : 'App\\Models\\User';

        // Check if user exists
        $user = $userModel::where('email', $identifier)
            ->orWhere('phone', $identifier) // Assuming phone column exists if using phone
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (!$identifier) {
            return $this->apiError('User identifier not found.', 400);
        }

        $key = 'otp-send:' . $identifier;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->apiError("Too many OTP requests. Please try again in {$seconds} seconds.", 429);
        }

        RateLimiter::hit($key, 60); // 3 attempts per minute

        try {
            $otp = $this->otpService->generate($identifier);
            return $this->apiSuccess(null, 'OTP sent successfully.');
        } catch (\Skywalker\Otp\Exceptions\OtpDeliveryFailedException $e) {
            return $this->apiError($e->getMessage(), 500);
        }
    }

    public function showVerifyForm(): \Illuminate\View\View
    {
        /** @var view-string $view */
        $view = 'passwordless::otp-verify';
        return view($view);
    }

    public function verifyOtpSubmit(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Use the trait's verify method which resolves identifier
        if (method_exists($user, 'verifyOtp') && $user->verifyOtp($request->input('otp'))) {
            $request->session()->put('otp_verified', true);
            return redirect()->intended('/'); // Redirect to intended page or home
        }

        return back()->with('error', 'Invalid or expired OTP.');
    }

    public function resendOtp(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'sendOtp')) {
            $user->sendOtp();

            if ($request->wantsJson()) {
                return $this->apiSuccess(null, 'OTP Resent successfully.');
            }
            return back()->with('success', 'OTP Resent successfully.');
        }

        if ($request->wantsJson()) {
            return $this->apiError('Unable to resend OTP.', 400);
        }
        return back()->with('error', 'Unable to resend OTP.');
    }

    public function verifyOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string',
        ]);

        $identifierInput = $request->input('identifier', '');
        $identifier = is_string($identifierInput) ? $identifierInput : '';

        $tokenInput = $request->input('otp', '');
        $token = is_string($tokenInput) ? $tokenInput : '';

        $key = 'otp-verify:' . $identifier;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->apiError("Too many verification attempts. Please try again in {$seconds} seconds.", 429);
        }

        $userModelConfig = config('auth.providers.users.model', 'App\\Models\\User');
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = is_string($userModelConfig) ? $userModelConfig : 'App\\Models\\User';

        try {
            if ($this->otpService->verify($identifier, $token)) {
                RateLimiter::clear($key); // Clear attempts on success

                $user = $userModel::where('email', $identifier)
                    ->orWhere('phone', $identifier)
                    ->first();

                if ($user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
                    event(new OtpVerified($user, $request));
                    return $this->apiSuccess(['user' => $user], 'OTP verified successfully.');
                }
            }
        } catch (\Skywalker\Otp\Exceptions\InvalidOtpException $e) {
            RateLimiter::hit($key, 60); // 5 attempts per minute
            return $this->apiError($e->getMessage(), 401);
        }

        return $this->apiError('Invalid or expired OTP.', 401);
    }

    public function loginMagic(Request $request): \Illuminate\Http\RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired magic link.');
        }

        $identifierInput = $request->identifier ?? '';
        $identifier = is_string($identifierInput) ? $identifierInput : '';

        $userModelConfig = config('auth.providers.users.model', 'App\\Models\\User');
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = is_string($userModelConfig) ? $userModelConfig : 'App\\Models\\User';

        $user = $userModel::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if ($user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            event(new OtpVerified($user, $request));
            $request->session()->put('otp_verified', true);
            return redirect()->intended('/');
        }

        return redirect()->route('login')->with('error', 'User not found.');
    }
}
