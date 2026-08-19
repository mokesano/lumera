<?php
declare(strict_types=1);

/**
 * @file pages/register/RegistrationHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RegistrationHandler
 * @ingroup pages_register
 *
 * @brief Handle requests for user registration.
 */

import('pages.user.UserHandler');
import('lib.wizdam.security.WizdamSecurity');

class RegistrationHandler extends UserHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RegistrationHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * [WIZDAM] Alias untuk register() -- dipanggil saat halaman diakses
     * lewat path telanjang page="register" (tanpa op eksplisit), karena
     * router memetakan op kosong ke 'index' (ROUTER_DEFAULT_OP).
     * Mengikuti pola pages/login: LoginHandler::index() menampilkan form
     * login, di sini index() menampilkan form registrasi.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        $this->register($args, $request);
    }

    /**
     * Display registration form for new users.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function register($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        if ($request->isPost()) {
            $this->registerUser($args, $request);
            return; // Hentikan eksekusi render GET
        }
        
        // --- [WIZDAM FIX] SINGLE ACCOUNT PARADIGM ---
        // Jika user sudah login, tidak perlu akses halaman register lagi.
        // Langsung lempar ke User Dashboard/Profile.
        if (Validation::isLoggedIn()) {
            $request->redirect(null, 'user');
            return; // Pastikan eksekusi berhenti di sini
        }
        // --- [END FIX] ---

        // [WIZDAM FIX] $request di posisi argumen yang benar
        $this->validate(null, $request);
        
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();

        if ($journal != null) {
            import('classes.user.form.RegistrationForm');

            $regForm = new RegistrationForm();
            if ($regForm->isLocaleResubmit()) {
                $regForm->readInputData();
            } else {
                $regForm->initData();
            }
            
            // [WIZDAM FIX] Inject security variables dengan konteks register
            $templateMgr = TemplateManager::getManager();
            // display() sudah handle semua assign
            // $this->_assignSecurityVariables($templateMgr, 'register');
            $regForm->display();

        } else {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournals(true);
            $templateMgr = TemplateManager::getManager();
            
            // [SECURITY FIX] Sanitasi parameter source
            $source = htmlspecialchars(trim((string) $request->getUserVar('source')), ENT_QUOTES, 'UTF-8');
            
            $templateMgr->assign('source', $source);
            $templateMgr->assign('journals', $journals);
            $templateMgr->display('user/registerSite.tpl');
        }
    }

    /**
     * Validate user registration information and register new user.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function registerUser($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (WizdamSecurity::isRateLimited($request, 'register')) {
            $this->setupTemplate($request, true);
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('pageTitle', 'user.register');
            $templateMgr->assign('errorMsg', 'user.register.rateLimitExceeded');
            $templateMgr->assign('backLink', $request->url(null, 'login'));
            $templateMgr->assign('backLinkLabel', 'user.login');
            return $templateMgr->display('common/error.tpl');
        }

        $this->validate(null, $request);
        $this->setupTemplate($request, true);

        // [WIZDAM FIX] Letakkan IMPORT di sini agar kelas dipastikan ada 
        // di memori terlepas dari apakah validasi token sukses atau gagal.
        import('classes.user.form.RegistrationForm');

        $regForm = new RegistrationForm();
        $regForm->readInputData();

        if ($regForm->validate()) {
            $regForm->execute();

            WizdamSecurity::resetAttempts($request, 'register');

            $reason = null;

            $implicitAuth = strtolower((string) Config::getVar('security', 'implicit_auth'));
            if ($implicitAuth === 'true') { 
                Validation::login('', '', $reason);
            } elseif ($implicitAuth === IMPLICIT_AUTH_OPTIONAL) {
                if ($regForm->getData('username') && $regForm->getData('password')) {
                    Validation::login($regForm->getData('username'), $regForm->getData('password'), $reason);
                } else {
                    Validation::login('', '', $reason);
                }
            } else {
                Validation::login($regForm->getData('username'), $regForm->getData('password'), $reason);
            }

            if (!Validation::isLoggedIn()) {
                if (Config::getVar('email', 'require_validation')) {
                    $this->setupTemplate($request, true);
                    $templateMgr = TemplateManager::getManager();
                    $templateMgr->assign('pageTitle', 'user.register.emailValidation');
                    $templateMgr->assign('errorMsg', 'user.register.emailValidationDescription');
                    $templateMgr->assign('backLink', $request->url(null, 'login'));
                    $templateMgr->assign('backLinkLabel', 'user.login');
                    return $templateMgr->display('common/error.tpl');
                }
            }

            if ($reason !== null) {
                $this->setupTemplate($request, true);
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('pageTitle', 'user.login');
                $templateMgr->assign('errorMsg', $reason==''?'user.login.accountDisabled':'user.login.accountDisabledWithReason');
                $templateMgr->assign('errorParams', ['reason' => $reason]);
                $templateMgr->assign('backLink', $request->url(null, 'login'));
                $templateMgr->assign('backLinkLabel', 'user.login');
                return $templateMgr->display('common/error.tpl');
            }
            
            // [SECURITY FIX] Validasi 'source' untuk mencegah Open Redirect
            $source = trim((string) $request->getUserVar('source'));
            
            if ($source) {
                if (preg_match('#^($|/|index\.php)#', $source)) {
                    $request->redirectUrl($source);
                } else {
                    $request->redirect(null, 'login');
                }
            } else {
                $request->redirect(null, 'login');
            }

        } else {
            WizdamSecurity::incrementAttempts($request, 'register');
            $regForm->display();
        }
    }

    /**
     * Show error message if user registration is not allowed.
     * @param object|null $request PKPRequest
     */
    public function registrationDisabled($request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageTitle', 'user.register');
        $templateMgr->assign('errorMsg', 'user.register.registrationDisabled');
        $templateMgr->assign('backLink', $request->url(null, 'login'));
        $templateMgr->assign('backLinkLabel', 'user.login');
        
        // [WIZDAM] Smarty otomatis mencari template di direktori tema aktif.
        // Fallback ke common/error.tpl jika template custom tidak ditemukan.
        $customTemplate = 'user/regDisabled.tpl';
        if ($templateMgr->template_exists($customTemplate)) {
            $templateMgr->display($customTemplate);
        } else {
            $templateMgr->display('common/error.tpl');
        }
    }

    /**
     * Check credentials and activate a new user
     * @param array $args
     * @param object|null $request PKPRequest
     * @author Marc Bria <marc.bria@uab.es>
     */
    public function activateUser($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $username = array_shift($args);
        $accessKeyCode = array_shift($args);

        $journal = $request->getJournal();
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getByUsername($username);
        if (!$user) $request->redirect(null, 'login');

        // Checks user & token
        import('lib.pkp.classes.security.AccessKeyManager');
        $accessKeyManager = new AccessKeyManager();
        $accessKeyHash = $accessKeyManager->generateKeyHash($accessKeyCode);
        $accessKey = $accessKeyManager->validateKey(
            'RegisterContext',
            $user->getId(),
            $accessKeyHash
        );

        if ($accessKey != null && $user->getDateValidated() === null) {
            // Activate user
            $user->setDisabled(false);
            $user->setDisabledReason('');
            $user->setDateValidated(Core::getCurrentDate());
            $userDao->updateObject($user);

            $this->setupTemplate($request, true);
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('message', 'user.login.activated');
            return $templateMgr->display('common/message.tpl');
        }
        $request->redirect(null, 'login');
    }

    /**
     * Validation check.
     * Checks if journal allows user registration.
     * @param mixed $requiredContexts (Legacy boolean loginCheck or context)
     * @param object|null $request PKPRequest
     * @return boolean
     */     
    public function validate($requiredContexts = null, $request = null) {
        // [WIZDAM FIX] Fallback request if null
        if ($request === null) {
            $request = Application::get()->getRequest();
        }
        
        // [WIZDAM FIX] PENTING: 
        // Kita harus mengirim 'false' ke parent::validate() sebagai parameter pertama.
        // Parameter pertama UserHandler::validate adalah $loginCheck.
        // Jika true (atau null yang dianggap default), maka user dilempar ke login.
        // Kita set 'false' karena halaman Register harus bisa diakses Guest.
        if (!parent::validate(false, $request)) {
            return false;
        }
        
        // Cek settingan Jurnal
        $journal = $request->getJournal();
        if ($journal != null) {
            /** @var JournalSettingsDAO $journalSettingsDao */
            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            
            // Debugging: Uncomment untuk cek setting database
            // error_log('WIZDAM DEBUG: disableUserReg status = ' . $journalSettingsDao->getSetting($journal->getId(), 'disableUserReg'));

            if ($journalSettingsDao->getSetting($journal->getId(), 'disableUserReg')) {
                // Users cannot register themselves for this journal
                $this->registrationDisabled($request);
                exit;
            }
        }
        
        return true;
    }

}
?>