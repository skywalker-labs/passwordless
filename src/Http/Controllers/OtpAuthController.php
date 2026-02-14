<?php

namespace Skywalker\Otp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Skywalker\Otp\Services\OtpService;
use Illuminate\Support\Facades\RateLimiter;
use Skywalker\Support\Http\Concerns\ApiResponse;

class OtpAuthController extends Controller
{
    use ApiResponse;

    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // validate email or phone
        ]);

        $identifier = $request->input('identifier');
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        
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

        $otp = $this->otpService->generate($identifier);
        
        // In sendOtp, we might want to actually send the OTP here, 
        // verify method in service does sending, but wait, `generate` calls `send`.
        // So this is fine.

        // Return 200 OK
        return $this->apiSuccess(null, 'OTP sent successfully.');
    }

    public function showVerifyForm()
    {
        return view('passwordless::otp-verify');
    }

    public function verifyOtpSubmit(Request $request)
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

    public function resendOtp(Request $request)
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

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string',
        ]);

        $identifier = $request->input('identifier');
        $token = $request->input('otp');

        $key = 'otp-verify:' . $identifier;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->apiError("Too many verification attempts. Please try again in {$seconds} seconds.", 429);
        }

        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        if ($this->otpService->verify($identifier, $token)) {
            RateLimiter::clear($key); // Clear attempts on success
            
            $user = $userModel::where('email', $identifier)
                        ->orWhere('phone', $identifier)
                        ->first();

            if ($user) {
                Auth::login($user);
                return $this->apiSuccess(['user' => $user], 'Logged in successfully.');
            }
        }

        RateLimiter::hit($key, 60); // 5 attempts per minute

        return $this->apiError('Invalid or expired OTP.', 401);
    }

    public function loginMagic(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired magic link.');
        }

        $identifier = $request->identifier;
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        
        $user = $userModel::where('email', $identifier)
                    ->orWhere('phone', $identifier)
                    ->first();

        if ($user) {
            Auth::login($user);
            $request->session()->put('otp_verified', true);
            return redirect()->intended('/');
        }

        return redirect()->route('login')->with('error', 'User not found.');
    }
}
