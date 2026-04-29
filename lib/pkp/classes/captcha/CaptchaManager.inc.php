<?php
declare(strict_types=1);

/**
 * @file classes/captcha/CaptchaManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CaptchaManager
 * @ingroup captcha
 * @see CaptchaDAO, Captcha
 *
 * @brief Class providing captcha services.
 */

import('lib.pkp.classes.file.FileManager');

class CaptchaManager {
    
    /**
     * Constructor.
     * Create a manager for handling temporary file uploads.
     */
    public function __construct() {
        $this->_performPeriodicCleanup();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CaptchaManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Create a CAPTCHA object.
     * @param int $length The length, in characters, of the CAPTCHA test to create
     * @return Captcha|null
     */
    public function createCaptcha(int $length = 6): ?Captcha {
        $captchaDao = DAORegistry::getDAO('CaptchaDAO');
        $session = Application::get()->getRequest()->getSession();
        
        if ($session && $this->isEnabled()) {
            $captcha = $captchaDao->newDataObject();
            $captcha->setSessionId($session->getId());
            $captcha->setValue(Validation::generatePassword($length));
            $captchaDao->insertCaptcha($captcha);
        } else {
            $captcha = null;
        }
        
        return $captcha;
    }

    /**
     * Get the width, in pixels, of the CAPTCHA test to create
     * @return int Width
     */
    public function getWidth(): int {
        return 300;
    }

    /**
     * Get the height, in pixels, of the CAPTCHA test to create
     * @return int Height
     */
    public function getHeight(): int {
        return 100;
    }

    /**
     * Get the MIME type of the CAPTCHA image that will be generated
     * @return string
     */
    public function getMimeType(): string {
        return 'image/png';
    }

    /**
     * Generate and display the CAPTCHA image.
     * @param Captcha $captcha
     */
    public function generateImage(Captcha $captcha): void {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $value = $captcha->getValue();
        $length = PKPString::strlen($value);

        $image = imagecreatetruecolor($width, $height);
        
        // Background & Foreground
        $fg = imagecolorallocate($image, rand(128, 255), rand(128, 255), rand(128, 255));
        $bg = imagecolorallocate($image, rand(0, 64), rand(0, 64), rand(0, 64));
        
        // Cast to int for PHP 8 strictness
        imagefill($image, (int) ($width / 2), (int) ($height / 2), $bg);

        $xStart = rand((int)($width / 12), (int)($width / 3));
        $xEnd = rand((int)($width * 2 / 3), (int)($width * 11 / 12));
        
        $fontLocation = Config::getVar('captcha', 'font_location');

        for ($i = 0; $i < $length; $i++) {
            // Calculate X position
            $xPos = $xStart + (($xEnd - $xStart) * $i / $length) + rand(-5, 5);
            
            imagefttext(
                $image,
                (float) rand(20, 34),    // Size (float)
                (float) rand(-15, 15),   // Angle (float)
                (int) $xPos,             // X position (int)
                rand(40, 60),            // Y position (int)
                $fg,                     // Colour
                $fontLocation,           // Font
                PKPString::substr($value, $i, 1) // Text
            );
        }

        // Add some noise to the image.
        for ($i = 0; $i < 20; $i++) {
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            for ($j = 0; $j < 20; $j++) {
                imagesetpixel(
                    $image,
                    rand(0, $this->getWidth()),
                    rand(0, $this->getHeight()),
                    $color
                );
            }
        }

        header('Content-type: ' . $this->getMimeType());
        imagepng($image);
        imagedestroy($image);
    }

    /**
     * Determine whether or not CAPTCHA testing is enabled and supported.
     * @return bool
     */
    public function isEnabled(): bool {
        return (
            function_exists('imagecreatetruecolor') &&
            Config::getVar('captcha', 'captcha')
        );
    }

    /**
     * Determine whether CAPTCHA is enabled for a specific context.
     * [WIZDAM FIX] Hormati captcha_on_* dari config.inc.php
     * Jika captcha_on_* tidak dikonfigurasi, fallback ke nilai captcha global.
     * @param string $context 'login'|'register'|'comments'|'mailinglist'
     * @return bool
     */
    public function isEnabledForContext(string $context = ''): bool {
        if (!$this->isEnabled()) return false;
        if (empty($context)) return true;
    
        $contextValue = Config::getVar('captcha', 'captcha_on_' . $context);
    
        // [WIZDAM] Jika captcha_on_* tidak dikonfigurasi sama sekali (null),
        // fallback ke captcha global — berarti aktif di semua halaman.
        if ($contextValue === null) return true;
    
        return (bool) $contextValue;
    }

    /**
     * Private function: Perform periodic cleanup on the CAPTCHA table.
     */
    private function _performPeriodicCleanup(): void {
        if (time() % 100 == 0) {
            $captchaDao = DAORegistry::getDAO('CaptchaDAO');
            $expiredCaptchas = $captchaDao->getExpiredCaptchas();
            foreach ($expiredCaptchas as $expiredCaptcha) {
                $captchaDao->deleteObject($expiredCaptcha);
            }
        }
    }
}

?>