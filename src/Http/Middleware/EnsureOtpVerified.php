<?php

declare(strict_types=1);

namespace Skywalker\Otp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Skywalker\Support\Security\ZeroTrust\TrustEngine;

class EnsureOtpVerified
{
    protected TrustEngine $trustEngine;

    public function __construct(TrustEngine $trustEngine)
    {
        $this->trustEngine = $trustEngine;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check if user is logged in
        if (Auth::check()) {
            $user = Auth::user();

            // If user does not have OTP capability, skip
            if ($user === null || ! method_exists($user, 'sendOtp')) {
                return $next($request);
            }

            // Check trust score if enabled
            $minTrust = config('passwordless.min_trust_score', 0.5);
            $trustScore = $this->trustEngine->calculateScore($user);

            // If trust score is very high (e.g., > 0.8), we might bypass OTP if configured
            $bypassHighTrust = (bool) config('passwordless.bypass_high_trust', false);
            if ($bypassHighTrust && $trustScore > 0.8) {
                return $next($request);
            }

            // Check if OTP verification is completed in session
            if ($request->session()->get('otp_verified') === true) {
                // Even if verified, if trust score drops significantly, re-verify
                if ($trustScore < $minTrust) {
                    $request->session()->forget('otp_verified');
                } else {
                    return $next($request);
                }
            }

            // Exclude OTP verify routes to prevent infinite loop
            if ($request->routeIs('otp.verify.view') || $request->routeIs('otp.verify.submit') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Redirect to OTP verification page
            return redirect()->route('otp.verify.view');
        }

        return $next($request);
    }
}
