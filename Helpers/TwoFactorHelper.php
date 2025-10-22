<?php

namespace App\GP247\Plugins\MFA\Helpers;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorHelper
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key
     *
     * @return string
     */
    public function generateSecretKey()
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL
     *
     * @param string $email
     * @param string $secret
     * @param string $appName
     * @return string
     */
    public function getQRCodeUrl($email, $secret, $appName = null)
    {
        $appName = $appName ?? config('Plugins/MFA.app_name', 'GP247');
        return $this->google2fa->getQRCodeUrl($appName, $email, $secret);
    }

    /**
     * Generate QR code as SVG
     *
     * @param string $email
     * @param string $secret
     * @param string $appName
     * @param int $size
     * @return string
     */
    public function getQRCodeSvg($email, $secret, $appName = null, $size = 200)
    {
        $qrCodeUrl = $this->getQRCodeUrl($email, $secret, $appName);
        
        $renderer = new ImageRenderer(
            new RendererStyle($size, 0),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify a one-time password
     *
     * @param string $secret
     * @param string $code
     * @param int $window
     * @return bool
     */
    public function verifyCode($secret, $code, $window = 1)
    {
        return $this->google2fa->verifyKey($secret, $code, $window);
    }

    /**
     * Get current timestamp
     *
     * @return int
     */
    public function getCurrentTimestamp()
    {
        return $this->google2fa->getTimestamp();
    }

    /**
     * Generate recovery codes
     *
     * @param int $count
     * @return array
     */
    public function generateRecoveryCodes($count = 8)
    {
        return mfa_generate_recovery_codes($count);
    }

    /**
     * Get Google2FA instance
     *
     * @return Google2FA
     */
    public function getGoogle2FA()
    {
        return $this->google2fa;
    }
}

