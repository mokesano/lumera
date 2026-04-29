<?php
declare(strict_types=1);

/**
 * @file classes/core/Handler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Handler
 * @ingroup handler
 *
 * @brief Base request handler application class
 * WIZDAM EDITION: PHP 8 Compatibility (Clean Inheritance) & Modular Security
 */

import('lib.pkp.classes.handler.PKPHandler');
import('classes.handler.validation.HandlerValidatorJournal');
import('classes.handler.validation.HandlerValidatorSubmissionComment');

class Handler extends PKPHandler {
    
    /**
     * Constructor
     * @param $request PKPRequest
     */
    public function __construct($request = null) {
        parent::__construct($request);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Handler($request = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Handler(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct($request);
    }

    /**
     * [WIZDAM SECURITY] Helper: Assign security flags and keys to template
     * @param TemplateManager $templateMgr
     */
    protected function _assignSecurityVariables($templateMgr, string $context = '') {
        $turnstileEnabled = (bool) Config::getVar('turnstile', 'turnstile');
        $reCaptchaEnabled = (bool) Config::getVar('recaptcha', 'recaptcha');

        // PILAR 1: CLOUDFLARE TURNSTILE
        $templateMgr->assign('turnstileEnabled', $turnstileEnabled);
        if ($turnstileEnabled) {
            $templateMgr->assign('turnstilePublicKey', Config::getVar('turnstile', 'turnstile_public_key'));
        }

        // PILAR 2: GOOGLE reCAPTCHA (v0, v2, v3)
        $templateMgr->assign('reCaptchaEnabled', $reCaptchaEnabled);
        if ($reCaptchaEnabled) {
            $reCaptchaVersion = (int) Config::getVar('recaptcha', 'recaptcha_version');
            $reCaptchaPublicKey = Config::getVar('recaptcha', 'recaptcha_public_key');
            
            $templateMgr->assign('reCaptchaVersion', $reCaptchaVersion);
            $templateMgr->assign('reCaptchaPublicKey', $reCaptchaPublicKey);

            if ($reCaptchaVersion === 2) {
                $reCaptchaHtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . "\n";
                $reCaptchaHtml .= '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($reCaptchaPublicKey, ENT_QUOTES, 'UTF-8') . '"></div>';
                $templateMgr->assign('reCaptchaHtml', $reCaptchaHtml);
            } elseif ($reCaptchaVersion === 0) {
                require_once('lib/recaptcha/recaptchalib.php');
                $templateMgr->assign('reCaptchaHtml', recaptcha_get_html($reCaptchaPublicKey));
            }
        }

        // PILAR 3: DEFAULT CAPTCHA (Fallback)
        // Hanya render Captcha gambar jika Turnstile DAN reCAPTCHA dimatikan
        if (!$turnstileEnabled && !$reCaptchaEnabled) {
            import('lib.pkp.classes.captcha.CaptchaManager');
            $captchaManager = new CaptchaManager();
            $captchaEnabled = $captchaManager->isEnabledForContext($context);
            
            $templateMgr->assign('captchaEnabled', $captchaEnabled);
            
            if ($captchaEnabled) {
                $captcha = $captchaManager->createCaptcha();
                if ($captcha) {
                    $templateMgr->assign('captchaId', $captcha->getId());
                    $templateMgr->assign('captcha', $captcha); 
                }
            }
        } else {
            // Matikan trigger frontend OJS untuk gambar captcha
            $templateMgr->assign('captchaEnabled', false);
        }
        
        // [WIZDAM SSO] Lempar status SSO ke template
        $templateMgr->assign('orcidSsoEnabled', Config::getVar('sso', 'orcid'));
        $templateMgr->assign('googleSsoEnabled', Config::getVar('sso', 'google'));
    }

    /**
     * [WIZDAM SECURITY] Helper: Validasi Token Paralel & Fallback Legacy
     * @param PKPRequest $request
     * @return bool True jika valid, False jika gagal.
     */
    protected function _validateSecurityTokens($request, string $context = ''): bool {
        $turnstileEnabled = (bool) Config::getVar('turnstile', 'turnstile');
        $reCaptchaEnabled = (bool) Config::getVar('recaptcha', 'recaptcha');

        // --- LAYER 1: VALIDASI MODERN (Paralel) ---
        // Jika salah satu atau keduanya aktif, Captcha bawaan diabaikan total
        if ($turnstileEnabled || $reCaptchaEnabled) {
            
            // 1A. Validasi Turnstile (jika diaktifkan)
            if ($turnstileEnabled) {
                $turnstileResponse = $request->getUserVar('cf-turnstile-response');
                if (empty($turnstileResponse)) return false;

                $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'secret' => Config::getVar('turnstile', 'turnstile_private_key'),
                    'response' => $turnstileResponse
                ]));
                $result = json_decode(curl_exec($ch));
                curl_close($ch);
                
                // Jika Turnstile gagal, langsung tolak login
                if (!$result || !$result->success) return false; 
            }

            // 1B. Validasi reCAPTCHA (jika diaktifkan)
            if ($reCaptchaEnabled) {
                $reCaptchaVersion = (int) Config::getVar('recaptcha', 'recaptcha_version');
                
                if ($reCaptchaVersion === 2 || $reCaptchaVersion === 3) {
                    $response = $request->getUserVar('g-recaptcha-response');
                    if (empty($response)) return false;

                    $ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                        'secret' => Config::getVar('recaptcha', 'recaptcha_private_key'),
                        'response' => $response
                    ]));
                    $result = json_decode(curl_exec($ch));
                    curl_close($ch);

                    if (!$result || !$result->success) return false;
                    if ($reCaptchaVersion === 3 && isset($result->score) && $result->score < 0.5) return false;
                    
                } elseif ($reCaptchaVersion === 0) {
                    require_once('lib/recaptcha/recaptchalib.php');
                    $privateKey = Config::getVar('recaptcha', 'recaptcha_private_key');
                    $resp = recaptcha_check_answer(
                        $privateKey,
                        $_SERVER["REMOTE_ADDR"],
                        $request->getUserVar('recaptcha_challenge_field'),
                        $request->getUserVar('recaptcha_response_field')
                    );
                    
                    if (!$resp || !$resp->is_valid) return false;
                }
            }

            // Jika eksekusi mencapai titik ini, berarti semua modul modern yang aktif berhasil divalidasi
            return true;
        }

        // --- LAYER 2: FALLBACK DEFAULT CAPTCHA ---
        // Dieksekusi HANYA JIKA Turnstile OFF dan reCAPTCHA OFF
        import('lib.pkp.classes.captcha.CaptchaManager');
        $captchaManager = new CaptchaManager();
        $captchaEnabled = $captchaManager->isEnabledForContext($context);
        
        if ($captchaEnabled) {
            $captchaId = $request->getUserVar('captchaId');
            $captchaValue = $request->getUserVar('captcha');
            
            if (!$captchaId) return false;
            
            $captchaDao = DAORegistry::getDAO('CaptchaDAO');
            $captcha = $captchaDao->getCaptcha($captchaId);
            
            if (!$captcha || $captcha->getValue() !== $captchaValue) {
                return false;
            }
            
            // Hapus dari DB agar tidak bisa di-replay
            $captchaDao->deleteCaptcha($captcha);
            return true; 
        }

        // --- LAYER 3: BYPASS ---
        // Jika ketiga fitur keamanan tidak ada yang aktif di config.inc.php
        return true; 
    }
    
    /**
     * Setup common template variables.
     * [WIZDAM] Override PKPHandler::setupTemplate() untuk inject
     * reCAPTCHA v3 public key secara global ke semua halaman.
     * v3 passive monitoring tidak memerlukan form submission —
     * cukup script tag di header dengan public key tersedia.
     * @param PKPRequest|null $request
     * @param bool $subclass
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate();
    
        // [WIZDAM SECURITY] Inject reCAPTCHA v3 public key secara global.
        // Hanya v3 yang perlu inject global — v2 dan Turnstile
        // ditangani eksplisit via _assignSecurityVariables() di form handler.
        $reCaptchaEnabled = (bool) Config::getVar('recaptcha', 'recaptcha');
        $reCaptchaVersion = (int) Config::getVar('recaptcha', 'recaptcha_version');
    
        if ($reCaptchaEnabled && $reCaptchaVersion === 3) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('reCaptchaEnabled',   true);
            $templateMgr->assign('reCaptchaVersion',   3);
            $templateMgr->assign('reCaptchaPublicKey', Config::getVar('recaptcha', 'recaptcha_public_key'));
        }
    }

}

?>