<?php

namespace Skywalker\Otp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in
        if (Auth::check()) {
            $user = Auth::user();

            // If user does not have OTP capability, skip
            if ($user === null || !method_exists($user, 'sendOtp')) {
                return $next($request);
            }

            // Check if OTP verification is bypassed or completed
            if ($request->session()->get('otp_verified') === true) {
                return $next($request);
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
