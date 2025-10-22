<?php

namespace App\GP247\Plugins\MFA\Controllers;

use App\GP247\Plugins\MFA\Models\TwoFactorAuth;
use App\GP247\Plugins\MFA\Models\MfaSetting;
use App\GP247\Plugins\MFA\Helpers\TwoFactorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;

class MFAController extends Controller
{
    protected $twoFactorHelper;

    public function __construct()
    {
        $this->twoFactorHelper = new TwoFactorHelper();
    }

    /**
     * Show MFA setup page
     *
     * @param Request $request
     * @param string $guard
     * @return \Illuminate\View\View
     */
    public function showSetup(Request $request, $guard = 'customer')
    {
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        // Check if user already has MFA enabled
        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->first();

        if ($mfaRecord && $mfaRecord->enabled) {
            return redirect()->route($guardConfig['redirect_after_verify']);
        }

        // Generate new secret if not exists
        if (!$mfaRecord) {
            $secret = $this->twoFactorHelper->generateSecretKey();
            $mfaRecord = TwoFactorAuth::create([
                'user_type' => $guardConfig['model'],
                'user_id' => $user->id,
                'secret' => $secret,
                'enabled' => 0,
            ]);
        }

        // Get QR code
        $qrCodeSvg = $this->twoFactorHelper->getQRCodeSvg(
            $user->email,
            $mfaRecord->getDecryptedSecretAttribute(),
            config('Plugins/MFA.app_name'),
            $guardConfig['qr_code_size'] ?? 200
        );

        return view('Plugins/MFA::Frontend.setup', [
            'guardConfig' => $guardConfig,
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $mfaRecord->getDecryptedSecretAttribute(),
            'guard' => $guard,
            'user' => $user,
        ]);
    }

    /**
     * Enable MFA for user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'guard' => 'required|string',
        ]);

        $guard = $request->input('guard');
        $code = $request->input('code');
        
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->first();

        if (!$mfaRecord) {
            return back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_setup'));
        }

        // Verify code
        $isValid = $this->twoFactorHelper->verifyCode(
            $mfaRecord->getDecryptedSecretAttribute(),
            $code,
            $guardConfig['window'] ?? 1
        );

        if (!$isValid) {
            return back()->with('error', gp247_language_render('Plugins/MFA::lang.invalid_code'));
        }

        // Generate recovery codes
        $recoveryCodes = $this->twoFactorHelper->generateRecoveryCodes(
            $guardConfig['recovery_codes_count'] ?? 8
        );

        // Enable MFA
        $mfaRecord->recovery_codes = $recoveryCodes;
        $mfaRecord->enabled = 1;
        $mfaRecord->enabled_at = now();
        $mfaRecord->save();

        return redirect()->route('mfa.recovery_codes', $guard)
            ->with('success', gp247_language_render('Plugins/MFA::lang.mfa_enabled_successfully'));
    }

    /**
     * Show recovery codes
     *
     * @param Request $request
     * @param string $guard
     * @return \Illuminate\View\View
     */
    public function showRecoveryCodes(Request $request, $guard = 'customer')
    {
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->first();

        if (!$mfaRecord || !$mfaRecord->enabled) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled'));
        }

        $recoveryCodes = $mfaRecord->getDecryptedRecoveryCodesAttribute();

        return view('Plugins/MFA::Frontend.recovery_codes', [
            'guardConfig' => $guardConfig,
            'recoveryCodes' => $recoveryCodes,
            'guard' => $guard,
            'user' => $user,
        ]);
    }

    /**
     * Show MFA verification page
     *
     * @param Request $request
     * @param string|null $guard
     * @return \Illuminate\View\View
     */
    public function showVerify(Request $request, $guard = null)
    {
        $guard = $guard ?? session('mfa_guard', 'customer');
        
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        return view('Plugins/MFA::Frontend.verify', [
            'guardConfig' => $guardConfig,
            'guard' => $guard,
            'user' => $user,
        ]);
    }

    /**
     * Verify MFA code
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'guard' => 'required|string',
        ]);

        $guard = $request->input('guard');
        $code = $request->input('code');
        
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->where('enabled', 1)
            ->first();

        if (!$mfaRecord) {
            return back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled'));
        }

        // Try to verify as 6-digit code first
        if (strlen($code) === 6) {
            $isValid = $this->twoFactorHelper->verifyCode(
                $mfaRecord->getDecryptedSecretAttribute(),
                $code,
                $guardConfig['window'] ?? 1
            );

            if ($isValid) {
                // Update last used
                $mfaRecord->last_used_at = now();
                $mfaRecord->save();

                // Set MFA verified in session
                mfa_set_verified();
                session(['mfa_guard' => $guard]);

                return redirect()->route($guardConfig['redirect_after_verify'])
                    ->with('success', gp247_language_render('Plugins/MFA::lang.verification_successful'));
            }
        }

        // Try recovery code if 6-digit code failed
        if (strlen($code) === 8) {
            if ($mfaRecord->hasRecoveryCode($code)) {
                $mfaRecord->useRecoveryCode($code);
                
                // Set MFA verified in session
                mfa_set_verified();
                session(['mfa_guard' => $guard]);

                return redirect()->route($guardConfig['redirect_after_verify'])
                    ->with('warning', gp247_language_render('Plugins/MFA::lang.recovery_code_used'));
            }
        }

        return back()->with('error', gp247_language_render('Plugins/MFA::lang.invalid_code'));
    }

    /**
     * Show MFA management page
     *
     * @param Request $request
     * @param string $guard
     * @return \Illuminate\View\View
     */
    public function showManage(Request $request, $guard = 'customer')
    {
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->first();

        return view('Plugins/MFA::Frontend.manage', [
            'guardConfig' => $guardConfig,
            'mfaRecord' => $mfaRecord,
            'guard' => $guard,
            'user' => $user,
        ]);
    }

    /**
     * Disable MFA for user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'guard' => 'required|string',
        ]);

        $guard = $request->input('guard');
        $password = $request->input('password');
        
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            return back()->with('error', gp247_language_render('Plugins/MFA::lang.incorrect_password'));
        }

        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->first();

        if ($mfaRecord) {
            $mfaRecord->delete();
        }

        return redirect()->back()->with('success', gp247_language_render('Plugins/MFA::lang.mfa_disabled_successfully'));
    }

    /**
     * Regenerate recovery codes
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'guard' => 'required|string',
        ]);

        $guard = $request->input('guard');
        $password = $request->input('password');
        
        // Validate guard exists in config
        $guardConfig = mfa_get_guard_config($guard);
        if (!$guardConfig || !$guardConfig['enabled']) {
            return redirect()->back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled_for_guard'));
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return redirect()->route($guardConfig['redirect_need_login'])->with('error', gp247_language_render('Plugins/MFA::lang.user_not_authenticated'));
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            return back()->with('error', gp247_language_render('Plugins/MFA::lang.incorrect_password'));
        }

        $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
            ->where('user_id', $user->id)
            ->where('enabled', 1)
            ->first();

        if (!$mfaRecord) {
            return back()->with('error', gp247_language_render('Plugins/MFA::lang.mfa_not_enabled'));
        }

        $mfaRecord->regenerateRecoveryCodes($guardConfig['recovery_codes_count'] ?? 8);

        return redirect()->route('mfa.recovery_codes', $guard)
            ->with('success', gp247_language_render('Plugins/MFA::lang.recovery_codes_regenerated'));
    }
}

