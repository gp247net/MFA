<?php

namespace App\GP247\Plugins\MFA\Admin;

use App\GP247\Plugins\MFA\Models\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    /**
     * Show MFA admin configuration page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $allGuards = config('Plugins/MFA.guards');
        $settings = [];
        $enabledGuards = [];
        
        // Load settings from config file
        foreach ($allGuards as $guard => $guardConfig) {
            if ($guardConfig['enabled'] ?? false) {
                $enabledGuards[] = $guard;
            }
        }

        // Get statistics
        $stats = $this->getStatistics();

        return view('Plugins/MFA::Admin', [
            'title' => gp247_language_render('Plugins/MFA::lang.admin_title'),
            'settings' => $allGuards,
            'guards' => array_keys($allGuards), // For displaying all settings
            'enabledGuards' => $enabledGuards, // For select dropdown
            'stats' => $stats,
        ]);
    }


    /**
     * Get MFA statistics
     *
     * @return array
     */
    protected function getStatistics()
    {
        $guards = ['customer', 'admin', 'vendor', 'pmo'];
        $stats = [];

        foreach ($guards as $guard) {
            $guardConfig = mfa_get_guard_config($guard);
            if (!$guardConfig) {
                continue;
            }

            $modelClass = $guardConfig['model'];
            
            // Total users
            try {
                $totalUsers = $modelClass::count();
            } catch (\Throwable $e) {
                $totalUsers = 0;
            }

            // Users with MFA enabled
            $mfaEnabledUsers = TwoFactorAuth::where('user_type', $modelClass)
                ->where('enabled', 1)
                ->count();

            // Users with MFA setup but not enabled
            $mfaSetupUsers = TwoFactorAuth::where('user_type', $modelClass)
                ->where('enabled', 0)
                ->count();

            $stats[$guard] = [
                'total_users' => $totalUsers,
                'mfa_enabled' => $mfaEnabledUsers,
                'mfa_setup' => $mfaSetupUsers,
                'percentage' => $totalUsers > 0 ? round(($mfaEnabledUsers / $totalUsers) * 100, 2) : 0,
            ];
        }

        return $stats;
    }

    /**
     * Show users management page with pagination
     *
     * @param Request $request
     * @param string $guard
     * @return \Illuminate\View\View
     */
    public function usersManagement(Request $request, $guard = null)
    {
        $allGuards = config('Plugins/MFA.guards');
        $enabledGuards = [];
        
        // Get enabled guards
        foreach ($allGuards as $guardName => $guardConfig) {
            if ($guardConfig['enabled'] ?? false) {
                $enabledGuards[] = $guardName;
            }
        }

        // If no guard specified, use first enabled guard
        if (!$guard && !empty($enabledGuards)) {
            $guard = $enabledGuards[0];
        }

        $guardConfig = mfa_get_guard_config($guard);
        $users = collect([]);
        $errorMsg = null;
        
        if ($guardConfig) {
            $modelClass = $guardConfig['model'];
            
            try {
                // Get users with pagination
                $users = $modelClass::paginate(20);

                // Get MFA status for each user
                $userIds = $users->pluck('id')->toArray();
                $mfaRecords = TwoFactorAuth::where('user_type', $modelClass)
                    ->whereIn('user_id', $userIds)
                    ->get()
                    ->keyBy('user_id');

                // Attach MFA data to users and add display_name field
                $users->getCollection()->transform(function ($user) use ($mfaRecords) {
                    // Add display_name field - use different column names based on what exists
                    if (isset($user->name)) {
                        $user->display_name = $user->name;
                    } elseif (isset($user->first_name) && isset($user->last_name)) {
                        $user->display_name = trim($user->first_name . ' ' . $user->last_name);
                    } elseif (isset($user->first_name)) {
                        $user->display_name = $user->first_name;
                    } else {
                        $user->display_name = $user->email;
                    }
                    
                    $user->two_factor_auth = $mfaRecords->get($user->id);
                    return $user;
                });
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
            }
        } else {
            $errorMsg = gp247_language_render('Plugins/MFA::lang.invalid_guard');
        }

        return view('Plugins/MFA::Users', [
            'title' => gp247_language_render('Plugins/MFA::lang.users_management'),
            'users' => $users,
            'enabledGuards' => $enabledGuards,
            'currentGuard' => $guard,
            'errorMsg' => $errorMsg,
        ]);
    }

    /**
     * Reset MFA for a user (admin action)
     *
     * @param Request $request
     * @param string $guard
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetUserMFA(Request $request, $guard)
    {
        try {
            $request->validate([
                'user_id' => 'required',
            ]);

            $userId = $request->input('user_id');
            
            $guardConfig = mfa_get_guard_config($guard);
            if (!$guardConfig) {
                return response()->json([
                    'error' => 1,
                    'msg' => gp247_language_render('Plugins/MFA::lang.invalid_guard'),
                ]);
            }

            $mfaRecord = TwoFactorAuth::where('user_type', $guardConfig['model'])
                ->where('user_id', $userId)
                ->first();

            if ($mfaRecord) {
                $mfaRecord->delete();
                $message = gp247_language_render('Plugins/MFA::lang.mfa_reset_successfully');
            } else {
                $message = gp247_language_render('Plugins/MFA::lang.mfa_not_setup');
            }

            return response()->json([
                'error' => 0,
                'msg' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 1,
                'msg' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 1,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
}

