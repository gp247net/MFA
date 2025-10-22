<?php

namespace App\GP247\Plugins\MFA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuth extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'two_factor_auth';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'secret',
        'recovery_codes',
        'enabled',
        'enabled_at',
        'last_used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the two factor auth.
     * This is a polymorphic relationship
     */
    public function user()
    {
        return $this->morphTo(__FUNCTION__, 'user_type', 'user_id');
    }

    /**
     * Get decrypted secret
     *
     * @return string
     */
    public function getDecryptedSecretAttribute()
    {
        return $this->secret ? Crypt::decryptString($this->secret) : null;
    }

    /**
     * Set encrypted secret
     *
     * @param string $value
     * @return void
     */
    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted recovery codes
     *
     * @return array|null
     */
    public function getDecryptedRecoveryCodesAttribute()
    {
        if (!$this->recovery_codes) {
            return null;
        }
        
        $decrypted = Crypt::decryptString($this->recovery_codes);
        return json_decode($decrypted, true);
    }

    /**
     * Set encrypted recovery codes
     *
     * @param array $value
     * @return void
     */
    public function setRecoveryCodesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['recovery_codes'] = Crypt::encryptString(json_encode($value));
        } else {
            $this->attributes['recovery_codes'] = null;
        }
    }

    /**
     * Check if a recovery code is valid
     *
     * @param string $code
     * @return bool
     */
    public function hasRecoveryCode($code)
    {
        $codes = $this->getDecryptedRecoveryCodesAttribute();
        if (!$codes) {
            return false;
        }

        return in_array(strtoupper($code), array_map('strtoupper', $codes));
    }

    /**
     * Use a recovery code (mark it as used)
     *
     * @param string $code
     * @return bool
     */
    public function useRecoveryCode($code)
    {
        $codes = $this->getDecryptedRecoveryCodesAttribute();
        if (!$codes) {
            return false;
        }

        $codeUpper = strtoupper($code);
        $index = array_search($codeUpper, array_map('strtoupper', $codes));
        
        if ($index !== false) {
            unset($codes[$index]);
            $this->setRecoveryCodesAttribute(array_values($codes));
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Regenerate recovery codes
     *
     * @param int $count
     * @return array
     */
    public function regenerateRecoveryCodes($count = 8)
    {
        $codes = mfa_generate_recovery_codes($count);
        $this->setRecoveryCodesAttribute($codes);
        $this->save();
        
        return $codes;
    }
}

