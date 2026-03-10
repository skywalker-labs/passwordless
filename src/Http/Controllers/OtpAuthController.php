<?php

declare(strict_types=1);

namespace Skywalker\Otp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;
use Skywalker\Otp\Events\OtpVerified;
use Skywalker\Support\Security\ZeroTrust\TrustEngine;

class OtpAuthController extends Controller
{
    /**
     * @param  mixed  $data
     */
    protected function apiSuccess($data = null, string $message = 'Success', int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @param  mixed  $errors
     */
    protected function apiError(string $message = 'Error', int $status = 400, $errors = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected OtpServiceContract $otpService;

    protected TrustEngine $trustEngine;

    public function __construct(OtpServiceContract $otpService, TrustEngine $trustEngine)
    {
        $this->otpService = $otpService;
        $this->trustEngine = $trustEngine;
    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        Validator::make($request->all(), [
            'identifier' => 'required|string',
        ])->validate();

        $identifierInput = $request->input('identifier', '');
        $identifier = is_string($identifierInput) ? $identifierInput : '';

        $userModelConfig = config('auth.providers.users.model', 'App\\Models\\User');
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = is_string($userModelConfig) ? $userModelConfig : 'App\\Models\\User';

        // Check if user exists
        $user = $userModel::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        $key = 'otp-send:'.$identifier;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->apiError("Too many OTP requests. Please try again in {$seconds} seconds.", 429);
        }

        RateLimiter::hit($key, 60);

        if ($user === null) {
            // Return generic success to prevent user enumeration
            return $this->apiSuccess(null, 'If an account exists, an OTP has been sent.');
        }

        $trustScore = $this->trustEngine->calculateScore($user);

        try {
            $otp = $this->otpService->generate($identifier);

            // Store trust score in session for verification phase
            $request->session()->put('otp_trust_score', $trustScore);

            return $this->apiSuccess(['trust_score' => $trustScore], 'OTP sent successfully.');
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
        Validator::make($request->all(), [
            'otp' => 'required|string',
        ])->validate();

        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('login');
        }

        // Use the trait's verify method which resolves identifier
        if (method_exists($user, 'verifyOtp') && $user->verifyOtp($request->input('otp')) === true) {
            $request->session()->put('otp_verified', true);

            return redirect()->intended('/'); // Redirect to intended page or home
        }

        return back()->with('error', 'Invalid or expired OTP.');
    }

    public function resendOtp(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null && method_exists($user, 'sendOtp')) {
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
        Validator::make($request->all(), [
            'identifier' => 'required|string',
            'otp' => 'required|string',
        ])->validate();

        $identifierInput = $request->input('identifier', '');
        $identifier = is_string($identifierInput) ? $identifierInput : '';

        $tokenInput = $request->input('otp', '');
        $token = is_string($tokenInput) ? $tokenInput : '';

        $key = 'otp-verify:'.$identifier;

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
                    $trustScore = $this->trustEngine->calculateScore($user);

                    event(new OtpVerified($user, $request));

                    $request->session()->regenerate(); // Prevent session fixation
                    $request->session()->put('otp_verified', true);
                    $request->session()->put('otp_trust_score', $trustScore);

                    return $this->apiSuccess([
                        'user' => $user,
                        'trust_score' => $trustScore,
                    ], 'OTP verified successfully.');
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
        if (\Illuminate\Support\Facades\URL::hasValidSignature($request) === false) {
            abort(401, 'Invalid or expired magic link.');
        }

        $identifierInput = $request->identifier ?? '';
        $identifier = is_string($identifierInput) ? $identifierInput : '';

        $key = 'otp-magic:'.$identifier;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return redirect()->route('login')->with('error', 'Too many login attempts. Please try again later.');
        }

        RateLimiter::hit($key, 300); // 5 attempts per 5 minutes

        $userModelConfig = config('auth.providers.users.model', 'App\\Models\\User');
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = is_string($userModelConfig) ? $userModelConfig : 'App\\Models\\User';

        $user = $userModel::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if ($user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            RateLimiter::clear($key);
            $trustScore = $this->trustEngine->calculateScore($user);

            event(new OtpVerified($user, $request));

            $request->session()->regenerate(); // Prevent session fixation
            $request->session()->put('otp_verified', true);
            $request->session()->put('otp_trust_score', $trustScore);

            return redirect()->intended('/');
        }

        return redirect()->route('login')->with('error', 'User not found.');
    }
}
