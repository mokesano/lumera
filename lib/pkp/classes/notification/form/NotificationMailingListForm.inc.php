<?php
declare(strict_types=1);

/**
 * @defgroup notification_form
 */

/**
 * @file classes/notification/form/NotificationMailingListForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationMailingListForm
 * @ingroup notification_form
 *
 * @brief Form to subscribe to the notification mailing list
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility
 * - TRUE MODULAR SECURITY: Decoupled Default Captcha, reCAPTCHA, and Turnstile
 */

import('lib.pkp.classes.form.Form');
import('classes.notification.Notification');

class NotificationMailingListForm extends Form {
    
    // Tiga pilar keamanan berdiri sendiri (Protected for Encapsulation)
    protected $captchaEnabled = false;
    protected $reCaptchaEnabled = false;
    protected $reCaptchaVersion = 0;
    protected $turnstileEnabled = false;
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('notification/maillist.tpl');

        // [WIZDAM SECURITY] PENENTUAN STATUS KEAMANAN ---

        // PILAR 1 CLOUDFLARE TURNSTILE - MODERN SECURITY (Pilar 1 dan 2 Bisa aktif bersamaan)
        $this->turnstileEnabled = (bool) Config::getVar('turnstile', 'turnstile');
        
        // PILAR 2: GOOGLE reCAPTCHA (v0, v2 atau v3)
        $this->reCaptchaEnabled = (bool) Config::getVar('recaptcha', 'recaptcha');
        if ($this->reCaptchaEnabled) {
            $this->reCaptchaVersion = (int) Config::getVar('recaptcha', 'recaptcha_version');
        }

        // PILAR 3: DEFAULT CAPTCHA (HANYA JIKA TURNSTILE & RECAPTCHA OFF)
        if (!$this->turnstileEnabled && !$this->reCaptchaEnabled) {
            if (Config::getVar('captcha', 'captcha') && Config::getVar('captcha', 'captcha_on_mailinglist')) {
                import('lib.pkp.classes.captcha.CaptchaManager');
                $captchaManager = new CaptchaManager();
                if ($captchaManager->isEnabled()) {
                    $this->captchaEnabled = true;
                }
            }
        }

        // VALIDASI DASAR ---
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'notification.mailList.emailInvalid'));
        
        // Validasi string email confirmation
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'email', 
            'required', 
            'user.register.form.emailsDoNotMatch', 
            function($email, $form) {
                return $email == $form->getData('confirmEmail');
            }, 
            array($this)
        ));
        
        // [WIZDAM SECURITY] Validasi Resolusi DNS & Blokir Disposable Email
        $this->addCheck(new FormValidatorCustom(
            $this, 'email', 'required', 'notification.mailList.emailInvalid', 
            function($email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
                
                $domain = substr(strrchr($email, "@"), 1);
                
                if (checkdnsrr($domain, 'MX')) {
                    $disposableDomains = array('mailinator.com', '10minutemail.com', 'guerrillamail.com', 'temp-mail.org');
                    if (in_array(strtolower($domain), $disposableDomains)) {
                        return false; 
                    }
                    return true;
                }
                return false; 
            }
        ));

        // [WIZDAM SECURITY] INJEKSI VALIDATOR BERDASARKAN PILAR AKTIF ---

        // 1. Validator Default Captcha
        if ($this->captchaEnabled) {
            $this->addCheck(new FormValidatorCaptcha($this, 'captcha', 'captchaId', 'common.captchaField.badCaptcha'));
        }

        // 2. Validator Turnstile
        if ($this->turnstileEnabled) {
            $this->addCheck(new FormValidatorCustom(
                $this, 'cf-turnstile-response', 'required', 'common.captchaField.badCaptcha',
                function($turnstileResponse) {
                    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                        'secret' => Config::getVar('turnstile', 'turnstile_private_key'),
                        'response' => $turnstileResponse
                    )));
                    $result = json_decode(curl_exec($ch));
                    curl_close($ch);
                    return ($result && $result->success);
                }
            ));
        }

        // 3. Validator reCAPTCHA
        if ($this->reCaptchaEnabled) {
            if ($this->reCaptchaVersion === 2 || $this->reCaptchaVersion === 3) {
                $this->addCheck(new FormValidatorCustom(
                    $this, 'g-recaptcha-response', 'required', 'common.captchaField.badCaptcha',
                    function($recaptchaResponse) {
                        $ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                            'secret' => Config::getVar('recaptcha', 'recaptcha_private_key'),
                            'response' => $recaptchaResponse
                        )));
                        $result = json_decode(curl_exec($ch));
                        curl_close($ch);

                        if (!$result || !$result->success) return false;
                        
                        if ($this->reCaptchaVersion === 3 && isset($result->score) && $result->score < 0.5) { 
                            return false; 
                        }
                        return true;
                    }
                ));
            } elseif ($this->reCaptchaVersion === 0) {
                // Legacy v0 Shim
                $this->addCheck(new FormValidatorCustom(
                    $this, 'recaptcha_response_field', 'required', 'common.captchaField.badCaptcha',
                    function($recaptchaResponse) {
                        require_once('lib/recaptcha/recaptchalib.php');
                        $request = Application::get()->getRequest();
                        $resp = recaptcha_check_answer(
                            Config::getVar('recaptcha', 'recaptcha_private_key'),
                            $_SERVER["REMOTE_ADDR"],
                            $request->getUserVar('recaptcha_challenge_field'),
                            $recaptchaResponse
                        );
                        return ($resp && $resp->is_valid);
                    }
                ));
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationMailingListForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::NotificationMailingListForm(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $userVars = array('email', 'confirmEmail');

        // Sinkronisasi tangkapan Turnstile
        if ($this->turnstileEnabled) {
            $userVars[] = 'cf-turnstile-response';
        }

        // Sinkronisasi tangkapan reCAPTCHA
        if ($this->reCaptchaEnabled) {
            if ($this->reCaptchaVersion === 0) {
                $userVars[] = 'recaptcha_challenge_field';
                $userVars[] = 'recaptcha_response_field';
            } else {
                $userVars[] = 'g-recaptcha-response';
            }
        }

        // Sinkronisasi tangkapan CAPTCHA Default
        if ($this->captchaEnabled) {
            $userVars[] = 'captchaId';
            $userVars[] = 'captcha';
        }
        
        $this->readUserVars($userVars);
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('new', true);
        
        // [WIZDAM SECURITY] LEMPAR FLAG KE SMARTY ---
        
        // 1. Turnstile
        $templateMgr->assign('turnstileEnabled', $this->turnstileEnabled);
        if ($this->turnstileEnabled) {
            $templateMgr->assign('turnstilePublicKey', Config::getVar('turnstile', 'turnstile_public_key'));
        }

        // 2. reCAPTCHA
        $templateMgr->assign('reCaptchaEnabled', $this->reCaptchaEnabled);
        if ($this->reCaptchaEnabled) {
            $templateMgr->assign('reCaptchaVersion', $this->reCaptchaVersion);
            $publicKey = Config::getVar('recaptcha', 'recaptcha_public_key');
            $templateMgr->assign('reCaptchaPublicKey', $publicKey);

            if ($this->reCaptchaVersion === 2) {
                $reCaptchaHtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . "\n";
                $reCaptchaHtml .= '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($publicKey, ENT_QUOTES, 'UTF-8') . '"></div>';
                $templateMgr->assign('reCaptchaHtml', $reCaptchaHtml);
            } elseif ($this->reCaptchaVersion === 0) {
                require_once('lib/recaptcha/recaptchalib.php');
                $templateMgr->assign('reCaptchaHtml', recaptcha_get_html($publicKey));
            }
        }

        // 3. Default Captcha OJS
        $templateMgr->assign('captchaEnabled', $this->captchaEnabled);
        if ($this->captchaEnabled) {
            import('lib.pkp.classes.captcha.CaptchaManager');
            $captchaManager = new CaptchaManager();
            $captcha = $captchaManager->createCaptcha();
            if ($captcha) {
                $this->setData('captchaId', $captcha->getId());
                $templateMgr->assign('captchaId', $captcha->getId());
                $templateMgr->assign('captcha', $captcha);
            }
        }

        // Context check untuk OJS legacy settings
        $context = $request ? $request->getContext() : PKPApplication::getRequest()->getContext();
        
        // [WIZDAM ARCHITECTURE FIX]
        // Penuhi kontrak data Smarty yang mengharapkan array $settings
        $settings = array(
            'allowRegReviewer' => false,
            'allowRegAuthor' => false,
            'subscriptionsEnabled' => false // Tambahan proteksi jika ada di maillist.tpl
        );

        if ($context) {
            // Ambil nilai sebenarnya jika konteks jurnal tersedia
            $settings['allowRegReviewer'] = $context->getSetting('allowRegReviewer');
            $settings['allowRegAuthor'] = $context->getSetting('allowRegAuthor');
            
            // Periksa metode langganan jika ini adalah jurnal spesifik
            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            $settings['subscriptionsEnabled'] = $paymentManager->acceptGiftSubscriptionPayments() || $paymentManager->acceptSubscriptionPayments();
        }

        // Lempar array yang sudah divalidasi ke Smarty
        $templateMgr->assign('settings', $settings);

        return parent::display($request, $template);
    }

    /**
     * Save the form
     * @param $object mixed
     */
    public function execute($object = null) {
        $userEmail = $this->getData('email');
        
        $request = PKPApplication::getRequest();
        $context = $request->getContext();

        $notificationMailListDao = DAORegistry::getDAO('NotificationMailListDAO');
        if($password = $notificationMailListDao->subscribeGuest($userEmail, $context->getId())) {
            $notificationManager = new NotificationManager();
            $notificationManager->sendMailingListEmail($request, $userEmail, $password, 'NOTIFICATION_MAILLIST_WELCOME');
            return true;
        } else {
            $request->redirect(null, 'notification', 'mailListSubscribed', array('error'));
            return false;
        }
    }
}
?>