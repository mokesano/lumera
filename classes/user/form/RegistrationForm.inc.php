<?php
declare(strict_types=1);

/**
 * @defgroup user_form
 */

/**
 * @file classes/user/form/RegistrationForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RegistrationForm
 * @ingroup user_form
 *
 * @brief Form for user registration.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility
 * - Strict Visibility
 * - TRUE MODULAR SECURITY: Decoupled Default Captcha, reCAPTCHA, and Turnstile
 */

import('lib.pkp.classes.form.Form');

class RegistrationForm extends Form {

    public $existingUser;
    public $defaultAuth;
    public $implicitAuth;

    // Tiga pilar keamanan berdiri sendiri (Decoupled Flags)
    public $captchaEnabled = false;
    public $reCaptchaEnabled = false;
    public $reCaptchaVersion = 0;
    public $turnstileEnabled = false;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('user/register.tpl');
        
        $this->implicitAuth = strtolower((string) Config::getVar('security', 'implicit_auth'));

        if (Validation::isLoggedIn()) {
            $this->existingUser = 1;
        } else {
            $this->existingUser = Request::getUserVar('existingUser') ? 1 : 0;

            // PILAR 1: CLOUDFLARE TURNSTILE
            $this->turnstileEnabled = (bool) Config::getVar('turnstile', 'turnstile');

            // PILAR 2: GOOGLE reCAPTCHA (v2 atau v3)
            // WIZDAM: Keduanya diizinkan aktif bersamaan sebagai perlindungan berlapis
            $this->reCaptchaEnabled = (bool) Config::getVar('recaptcha', 'recaptcha');
            if ($this->reCaptchaEnabled) {
                $version = Config::getVar('recaptcha', 'recaptcha_version');
                // Beri nilai default 3 jika config kosong agar tidak jatuh ke legacy
                $this->reCaptchaVersion = $version ? (int) $version : 3; 
            }

            // PILAR 3: DEFAULT CAPTCHA (Gambar)
            // WIZDAM: Diabaikan mutlak jika salah satu atau kedua keamanan modern aktif
            $isModernSecurityActive = ($this->turnstileEnabled || $this->reCaptchaEnabled);
            
            if (!$isModernSecurityActive && Config::getVar('captcha', 'captcha') && Config::getVar('captcha', 'captcha_on_register')) {
                import('lib.pkp.classes.captcha.CaptchaManager');
                $captchaManager = new CaptchaManager();
                if ($captchaManager->isEnabled()) {
                    $this->captchaEnabled = true;
                } else {
                    $this->captchaEnabled = false;
                }
            } else {
                $this->captchaEnabled = false;
            }

            // Validation checks standard...
            $this->addCheck(new FormValidator($this, 'username', 'required', 'user.profile.form.usernameRequired'));
            $this->addCheck(new FormValidator($this, 'password', 'required', 'user.profile.form.passwordRequired'));

            if ($this->existingUser) {
                $this->addCheck(new FormValidatorCustom(
                    $this, 'username', 'required', 'user.login.loginError', 
                    function($username) {
                        return Validation::checkCredentials($username, $this->getData('password'));
                    }
                ));
            } else {
                $site = Request::getSite();
                $this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.register.form.usernameExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByUsername'), array(), true));
                $this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.register.form.usernameAlphaNumeric', function($username) {
                    return (bool) preg_match('/^[a-zA-Z0-9_.-]+$/', $username);
                }));
                
                // [WIZDAM SECURITY] Validator min password length
                $this->addCheck(new FormValidatorLength($this, 'password', 'required', 'user.register.form.passwordLengthTooShort', '>=', (int)$site->getMinPasswordLength()));
                $this->addCheck(new FormValidatorCustom($this, 'password', 'required', 'user.register.form.passwordsDoNotMatch', function($password) {
                    return $password == $this->getData('password2');
                }));
                
                // [WIZDAM SECURITY] Validator 5 kriteria kompleksitas password
                $this->addCheck(new FormValidatorCustom(
                    $this, 'password', 'required', 'user.register.form.passwordTooWeak', 
                    function($password) {
                        // Cek Huruf Besar
                        if (!preg_match('/[A-Z]/', $password)) return false;
                        // Cek Angka
                        if (!preg_match('/[0-9]/', $password)) return false;
                        // Cek Karakter Spesial
                        if (!preg_match('/[^a-zA-Z0-9]/', $password)) return false;
                        
                        return true;
                    }
                ));
                
                // Pengaturan yang memastikan field first dan last name
                $this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
                $this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
                
                $this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
                $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
                
                $this->addCheck(new FormValidatorORCID($this, 'orcid', 'optional', 'user.profile.form.orcidInvalid'));

                // Memastikan field Affiliasi (Instansi) wajib diisi
                $this->addCheck(new FormValidator($this, 'affiliation', 'required', 'user.profile.form.affiliationRequired'));
                
                // Memastikan field Negara (Country) wajib diisi
                $this->addCheck(new FormValidator($this, 'country', 'required', 'user.profile.form.countryRequired'));
                
                // [WIZDAM SECURITY] DNS Validatio & Disposable Email
                $this->addCheck(new FormValidatorCustom(
                    $this, 'email', 'required', 'user.profile.form.emailRequired', 
                    function($email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
                        
                        $domain = substr(strrchr($email, "@"), 1);
                        
                        // Cek ketersediaan server email (MX Record)
                        if (checkdnsrr($domain, 'MX')) {
                            // Daftar hitam email sekali pakai
                            $disposableDomains = array('mailinator.com', '10minutemail.com', 'guerrillamail.com', 'temp-mail.org');
                            if (in_array(strtolower($domain), $disposableDomains)) {
                                return false; 
                            }
                            return true;
                        }
                        return false; 
                    }
                ));
                $this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array(), true));
                
                // MODULAR VALIDATORS: Ditambahkan HANYA jika flag aktif
                
                // 1. Validator Default Captcha
                if ($this->captchaEnabled) {
                    $this->addCheck(new FormValidatorCaptcha($this, 'captcha', 'captchaId', 'common.captchaField.badCaptcha'));
                }

                // 2. Validator Turnstile
                if ($this->turnstileEnabled) {
                    $this->addCheck(new FormValidatorCustom(
                        $this, 'cf-turnstile-response', 'required', 'user.register.form.turnstileError', // WIZDAM FIX: Error key terpisah
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
                            $this, 'g-recaptcha-response', 'required', 'user.register.form.recaptchaError', // WIZDAM FIX: Error key terpisah
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
                                
                                // Jika v3, cek skor bot
                                if ($this->reCaptchaVersion === 3 && isset($result->score) && $result->score < 0.5) { 
                                    return false; 
                                }
                                return true;
                            }
                        ));
                        
                    } elseif ($this->reCaptchaVersion === 0) {
                        // [LEGACY SHIM] Validasi reCAPTCHA v1
                        $this->addCheck(new FormValidatorCustom(
                            $this, 'recaptcha_response_field', 'required', 'user.register.form.recaptchaError', // WIZDAM FIX
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

                $authDao = DAORegistry::getDAO('AuthSourceDAO');
                $this->defaultAuth = $authDao->getDefaultPlugin();
                if (isset($this->defaultAuth)) {
                    $authPlugin = $this->defaultAuth; 
                    $this->addCheck(new FormValidatorCustom(
                        $this, 'username', 'required', 'user.register.form.usernameExists', 
                        function($username) use ($authPlugin) {
                            return (!$authPlugin->userExists($username) || $authPlugin->authenticate($username, $this->getData('password')));
                        }
                    ));
                }
            }
        }
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RegistrationForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class " . get_class($this) . " uses deprecated constructor parent::RegistrationForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $site = Request::getSite();
        $templateMgr->assign('minPasswordLength', $site->getMinPasswordLength());
        $journal = Request::getJournal();

        // Lempar flag status independen ke Smarty
        $templateMgr->assign('captchaEnabled', $this->captchaEnabled);
        $templateMgr->assign('reCaptchaEnabled', $this->reCaptchaEnabled);
        $templateMgr->assign('turnstileEnabled', $this->turnstileEnabled);
        
        // 1. Eksekusi logic UI berdasarkan Flag
        if ($this->captchaEnabled) {
            import('lib.pkp.classes.captcha.CaptchaManager');
            $captchaManager = new CaptchaManager();
            $captcha = $captchaManager->createCaptcha();
            if ($captcha) {
                $this->setData('captchaId', $captcha->getId());
            }
        }

        // 2. reCAPTCHA (SELARAS DENGAN SMARTY ANDA)
        if ($this->reCaptchaEnabled) {
            // v2 dan v3 di-handle oleh Smarty menggunakan key
            $templateMgr->assign('reCaptchaVersion', $this->reCaptchaVersion);
            $publicKey = Config::getVar('recaptcha', 'recaptcha_public_key');
            $templateMgr->assign('reCaptchaPublicKey', $publicKey);

            // Backend HANYA merender HTML jika versinya 0
            if ($this->reCaptchaVersion === 0) {
                require_once('lib/recaptcha/recaptchalib.php');
                $templateMgr->assign('reCaptchaHtml', recaptcha_get_html($publicKey));
            }
        }

        // 3. Sinkronisasi tangkapan Turnstile
        if ($this->turnstileEnabled) {
            $templateMgr->assign('turnstilePublicKey', Config::getVar('turnstile', 'turnstile_public_key'));
        }

        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
        $templateMgr->assign_by_ref('countries', $countries);
        $userDao = DAORegistry::getDAO('UserDAO');
        $templateMgr->assign('genderOptions', $userDao->getGenderOptions());
        if ($journal) {
            $templateMgr->assign('privacyStatement', $journal->getLocalizedSetting('privacyStatement'));
        } else {
            $templateMgr->assign('privacyStatement', $site->getLocalizedSetting('privacyStatement'));
        }
        $templateMgr->assign('allowRegReader', $journal ? $journal->getSetting('allowRegReader') : false);
        $templateMgr->assign('enableOpenAccessNotification', $journal ? $journal->getSetting('enableOpenAccessNotification') : false);
        $templateMgr->assign('allowRegAuthor', $journal ? $journal->getSetting('allowRegAuthor') : false);
        $templateMgr->assign('allowRegReviewer', $journal ? $journal->getSetting('allowRegReviewer') : false);
        $templateMgr->assign('source', Request::getUserVar('source'));
        $templateMgr->assign('availableLocales', $site->getSupportedLocaleNames());
        $templateMgr->assign('helpTopicId', 'user.registerAndProfile');
        
        parent::display($request, $template);
    }

    /**
     * Get locale field names.
     * @return array
     */
    public function getLocaleFieldNames() {
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getLocaleFieldNames();
    }

    /**
     * Initialize default data.
     */
    public function initData() {
        // [WIZDAM] Tetapkan nilai default
        // --- 1. Kode Asli/Default ---
        $this->setData('registerAsReader', 1);
        $this->setData('registerAsAuthor', 1);
        $this->setData('existingUser', $this->existingUser);
        $currentLocale = AppLocale::getLocale();
        $this->setData('userLocales', array($currentLocale));
        $this->setData('sendPassword', 0);

        // [WIZDAM SSO] Cek apakah ada data dari ORCID di session
        // --- 2. Injeksi WIZDAM SSO (Ambil dari Session) ---
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        
        $orcidData = $session->getSessionVar('orcid_data');
        $googleData = $session->getSessionVar('google_data');

        if ($orcidData) {
            // Jika ada data ORCID, gunakan data tersebut
            $this->setData('firstName', $orcidData['firstName']);
            $this->setData('lastName', $orcidData['lastName']);
            $this->setData('orcid', $orcidData['orcid']);
            
            // Tangkap hasil sinkronisasi WIZDAM MAGIC
            if (!empty($orcidData['affiliation'])) {
                $this->setData('affiliation', [$currentLocale => $orcidData['affiliation']]);
            }
            if (!empty($orcidData['biography'])) {
                $this->setData('biography', [$currentLocale => $orcidData['biography']]);
            }
            
            // Email tetap kosong agar diisi manual
            $this->setData('email', Request::getUserVar('email'));
            // Hapus session agar bersih
            $session->unsetSessionVar('orcid_data');
        } elseif ($googleData) {
            // Jika login dari Google
            $this->setData('firstName', $googleData['firstName']);
            $this->setData('lastName', $googleData['lastName']);
            // Google otomatis memberikan email
            $this->setData('email', $googleData['email']);
            
            $session->unsetSessionVar('google_data');
        } else {
            // Jika tidak ada SSO, gunakan data request standar (fallback)
            // --- 3. Fallback jika registrasi normal ---
            $this->setData('firstName', Request::getUserVar('firstName'));
            $this->setData('lastName', Request::getUserVar('lastName'));
            $this->setData('email', Request::getUserVar('email'));
            $this->setData('orcid', Request::getUserVar('orcid'));
            
            $requestedAffiliation = Request::getUserVar('affiliation');
            $currentLocale = AppLocale::getLocale();
            if (is_array($requestedAffiliation)) {
                // Jika dari request sudah berupa array (sangat jarang terjadi pada GET, tapi aman)
                $this->setData('affiliation', $requestedAffiliation);
            } else {
                // Jika request kosong (null) atau berupa string URL biasa,
                // inisialisasi sebagai struktur array multi-bahasa
                $this->setData('affiliation', array(
                    $currentLocale => $requestedAffiliation ? (string) $requestedAffiliation : ''
                ));
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $userVars = array(
            'username', 'password', 'password2', 'salutation', 'firstName', 'middleName', 'lastName', 'suffix', 'gender', 'initials', 
            'country', 'affiliation', 'email', 'orcid', 'userUrl', 'googleScholar', 'sintaId', 'scopusId', 'dimensionId', 
            'researcherId', 'phone', 'fax', 'signature', 'mailingAddress', 'biography', 'interestsTextOnly', 'keywords', 'userLocales',
            'registerAsReader', 'openAccessNotification', 'registerAsAuthor', 'registerAsReviewer', 'existingUser', 'sendPassword'
        );
        
        // Tangkap variabel POST secara mandiri berdasarkan pilar yang aktif
        if ($this->captchaEnabled) {
            $userVars[] = 'captchaId';
            $userVars[] = 'captcha';
        }
        
        if ($this->turnstileEnabled) {
            $userVars[] = 'cf-turnstile-response';
        }

        if ($this->reCaptchaEnabled) {
            if ($this->reCaptchaVersion === 0) {
                $userVars[] = 'recaptcha_challenge_field';
                $userVars[] = 'recaptcha_response_field';
            } else {
                $userVars[] = 'g-recaptcha-response';
            }
        }

        // 1. OJS secara native membaca variabel dari $_POST
        $this->readUserVars($userVars);
        
        // 2. NORMALISASI NATIVE: Pastikan struktur data multi-bahasa  konsisten
        $affiliation = $this->getData('affiliation');
        if (!is_array($affiliation)) {
            $this->setData('affiliation', array(AppLocale::getLocale() => (string) $affiliation));
        }

        if ($this->getData('userLocales') == null || !is_array($this->getData('userLocales'))) {
            $this->setData('userLocales', array());
        }
        $username = $this->getData('username');
        if ($username != null) {
            $this->setData('username', strtolower((string) $username));
        }
        $keywords = $this->getData('keywords');
        if ($keywords != null && is_array($keywords) && isset($keywords['interests']) && is_array($keywords['interests'])) {
            $this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
        }
    }

    /**
     * Register a new user.
     * @param object|null $object
     * @return boolean|null
     */
    public function execute($object = null) {
        $requireValidation = Config::getVar('email', 'require_validation');

        if ($this->existingUser) { 
            $userDao = DAORegistry::getDAO('UserDAO');

            if ($this->implicitAuth) { 
                $sessionManager = SessionManager::getManager();
                $session = $sessionManager->getUserSession();
                $user = $userDao->getByUsername($session->getSessionVar('username'));
            } else {
                $user = $userDao->getByUsername($this->getData('username'));
            }

            if ($user == null) { return false; }

            parent::execute($user);
            $userId = $user->getId();
        } else {
            $user = new User();

            $user->setUsername($this->getData('username'));
            $user->setSalutation($this->getData('salutation'));
            $user->setFirstName($this->getData('firstName'));
            $user->setMiddleName($this->getData('middleName'));
            $user->setInitials($this->getData('initials'));
            $user->setLastName($this->getData('lastName'));
            $user->setSuffix($this->getData('suffix'));
            $user->setGender($this->getData('gender'));
            $user->setAffiliation($this->getData('affiliation'), null); 
            $user->setSignature($this->getData('signature'), null); 
            $user->setEmail($this->getData('email'));
            $user->setData('orcid', $this->getData('orcid'));
            $user->setUrl($this->getData('userUrl'));
            $user->setGoogleScholar($this->getData('googleScholar'));
            $user->setSintaId($this->getData('sintaId'));
            $user->setScopusId($this->getData('scopusId'));
            $user->setDimensionId($this->getData('dimensionId'));
            $user->setResearcherId($this->getData('researcherId'));
            $user->setPhone($this->getData('phone'));
            $user->setFax($this->getData('fax'));
            $user->setMailingAddress($this->getData('mailingAddress'));
            $user->setBiography($this->getData('biography'), null); 
            $user->setDateRegistered(Core::getCurrentDate());
            $user->setCountry($this->getData('country'));

            $site = Request::getSite();
            $availableLocales = $site->getSupportedLocales();

            $locales = array();
            $userLocales = $this->getData('userLocales');
            if (is_array($userLocales)) {
                foreach ($userLocales as $locale) {
                    if (AppLocale::isLocaleValid($locale) && in_array($locale, $availableLocales)) {
                        array_push($locales, $locale);
                    }
                }
            }
            $user->setLocales($locales);

            if (isset($this->defaultAuth)) {
                $user->setPassword($this->getData('password'));
                $this->defaultAuth->doCreateUser($user);
                $user->setAuthId($this->defaultAuth->authId);
            }
            $user->setPassword(Validation::encryptCredentials($this->getData('username'), $this->getData('password')));

            if ($requireValidation) {
                $user->setDisabled(true);
                $user->setDisabledReason(__('user.login.accountNotValidated'));
            }

            parent::execute($user);
            $userDao = DAORegistry::getDAO('UserDAO');
            $userDao->insertUser($user);
            $userId = $user->getId();
            if (!$userId) { return false; }

            $interests = $this->getData('interestsKeywords') ? $this->getData('interestsKeywords') : $this->getData('interestsTextOnly');
            import('lib.pkp.classes.user.InterestManager');
            $interestManager = new InterestManager();
            $interestManager->setInterestsForUser($user, $interests);

            $sessionManager = SessionManager::getManager();
            $session = $sessionManager->getUserSession();
            $session->setSessionVar('username', $user->getUsername());
        }

        $journal = Request::getJournal();
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $allowedRoles = array('reader' => 'registerAsReader', 'author' => 'registerAsAuthor', 'reviewer' => 'registerAsReviewer');

        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        if (!$journalSettingsDao->getSetting($journal->getId(), 'allowRegReader')) { unset($allowedRoles['reader']); }
        if (!$journalSettingsDao->getSetting($journal->getId(), 'allowRegAuthor')) { unset($allowedRoles['author']); }
        if (!$journalSettingsDao->getSetting($journal->getId(), 'allowRegReviewer')) { unset($allowedRoles['reviewer']); }

        foreach ($allowedRoles as $k => $v) {
            $roleId = $roleDao->getRoleIdFromPath($k);
            if ($this->getData($v) && !$roleDao->userHasRole($journal->getId(), $userId, $roleId)) {
                $role = new Role();
                $role->setJournalId($journal->getId());
                $role->setUserId($userId);
                $role->setRoleId($roleId);
                $roleDao->insertRole($role);
            }
        }

        if (!$this->existingUser) {
            import('classes.mail.MailTemplate');
            if ($requireValidation) {
                import('lib.pkp.classes.security.AccessKeyManager');
                $accessKeyManager = new AccessKeyManager();
                $accessKey = $accessKeyManager->createKey('RegisterContext', $user->getId(), null, Config::getVar('email', 'validation_timeout'));

                $mail = new MailTemplate('USER_VALIDATE');
                $mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                
                // --- KODE BARU UNTUK ROLE DINAMIS ---
                $rolesSelected = [];
                if ($this->getData('readerRole')) {
                    $rolesSelected[] = __('user.role.reader');
                }
                if ($this->getData('authorRole')) {
                    $rolesSelected[] = __('user.role.author');
                }
                if ($this->getData('reviewerRole')) {
                    $rolesSelected[] = __('user.role.reviewer');
                }
                
                $userRole = !empty($rolesSelected) ? implode(', ', $rolesSelected) : 'Registered User';
                // ------------------------------------
                
                $mail->assignParams(array(
                    'userFullName'   => $user->getFullName(),
                    'activateUrl'    => Request::url($journal->getPath(), 'user', 'activateUser', array($this->getData('username'), $accessKey)),
                    
                    // Variabel untuk Email Template:
                    'userEmail'      => $user->getEmail(),
                    'username'       => $this->getData('username'),
                    'dateRegistered' => Core::getCurrentDate(),
                    'userRole'       => $userRole 
                ));
                
                $mail->addRecipient($user->getEmail(), $user->getFullName());
                $mail->send();
                unset($mail);
            }
            if ($this->getData('sendPassword')) {
                $mail = new MailTemplate('USER_REGISTER');
                $mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                $mail->assignParams(array(
                    'username' => $this->getData('username'),
                    'password' => PKPString::substr($this->getData('password'), 0, 30),
                    'userFullName' => $user->getFullName()
                ));
                $mail->addRecipient($user->getEmail(), $user->getFullName());
                $mail->send();
                unset($mail);
            }
        }

        if (isset($allowedRoles['reader']) && $this->getData('openAccessNotification')) {
            $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
            $userSettingsDao->updateSetting($userId, 'openAccessNotification', true, 'bool', $journal->getId());
        }
        
        return true;
    }
}
?>