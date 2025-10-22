<?php

namespace App\GP247\Plugins\MFA\Middleware;

use App\GP247\Plugins\MFA\Models\TwoFactorAuth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Determine the guard
        $guard = $guard ?? $this->getGuardFromRequest($request);
        
        $guardConfig = mfa_get_guard_config($guard);
        
        // Check if MFA is enabled for this guard
        if (!$guardConfig || !$guardConfig['enabled']) {
            return $next($request);
        }

        // Check if user is authenticated
        $user = Auth::guard($guard)->user();
        if (!$user) {
            return $next($request);
        }


        // Check if session already verified
        if (mfa_is_verified()) {
            return $next($request);
        }

        // Check if user has MFA enabled
        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->where('enabled', 1)
            ->first();

        // If user doesn't have MFA enabled, check if it's forced
        if (!$mfaRecord) {
            if ($guardConfig['forced'] ?? false) {
                // Redirect to MFA setup if forced
                return redirect()->route('mfa.setup.show', $guard)
                    ->with('warning', gp247_language_render('Plugins/MFA::lang.mfa_required'));
            }
            return $next($request);
        }

        // Store guard in session for verification
        session(['mfa_guard' => $guard]);

        // User has MFA enabled but not verified in this session
        return redirect()->route('mfa.verify.show', $guard);
    }

    /**
     * Get guard from request
     *
     * @param Request $request
     * @return string
     */
    protected function getGuardFromRequest(Request $request)
    {
        // Try to get guard from route parameter
        if ($request->route('guard')) {
            return $request->route('guard');
        }

        // Try to detect from URL path
        $path = $request->path();
        
        if (str_contains($path, '/admin')) {
            return 'admin';
        }
        
        if (str_contains($path, '/vendor')) {
            return 'vendor';
        }
        
        if (str_contains($path, '/pmo')) {
            return 'pmo';
        }

        // Default to customer
        return 'customer';
    }
}

