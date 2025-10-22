<?php

if (!function_exists('mfa_is_user_enrolled')) {
    /**
     * Check if user has enabled MFA
     *
     * @param mixed $user
     * @param string $guard
     * @return bool
     */
    function mfa_is_user_enrolled($user, $guard)
    {
        if (!$user) {
            return false;
        }

        $guardConfig = config('Plugins/MFA.guards.' . $guard);
        if (!$guardConfig) {
            return false;
        }

        $mfaRecord = \App\GP247\Plugins\MFA\Models\TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->where('enabled', 1)
            ->first();

        return $mfaRecord !== null;
    }
}

if (!function_exists('mfa_is_verified')) {
    /**
     * Check if current session has verified MFA
     *
     * @return bool
     */
    function mfa_is_verified()
    {
        $sessionKey = config('Plugins/MFA.session_key', 'mfa_verified');
        return session($sessionKey, false) === true;
    }
}

if (!function_exists('mfa_set_verified')) {
    /**
     * Mark current session as MFA verified
     *
     * @return void
     */
    function mfa_set_verified()
    {
        $sessionKey = config('Plugins/MFA.session_key', 'mfa_verified');
        session([$sessionKey => true]);
    }
}

if (!function_exists('mfa_clear_verified')) {
    /**
     * Clear MFA verification from session
     *
     * @return void
     */
    function mfa_clear_verified()
    {
        $sessionKey = config('Plugins/MFA.session_key', 'mfa_verified');
        session()->forget($sessionKey);
    }
}

if (!function_exists('mfa_get_guard_config')) {
    /**
     * Get MFA configuration for a specific guard
     *
     * @param string $guard
     * @return array|null
     */
    function mfa_get_guard_config($guard)
    {
        return config('Plugins/MFA.guards.' . $guard);
    }
}

if (!function_exists('mfa_generate_recovery_codes')) {
    /**
     * Generate recovery codes
     *
     * @param int $count
     * @return array
     */
    function mfa_generate_recovery_codes($count = 8)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(6))), 0, 8));
        }
        return $codes;
    }
}

