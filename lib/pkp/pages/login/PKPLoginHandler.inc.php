<?php
declare(strict_types=1);

/**
 * @file pages/login/PKPLoginHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPLoginHandler
 * @ingroup pages_login
 *
 * @brief Handle login/logout requests.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * - Integrated Modular Security: Turnstile & reCAPTCHA v2/v3
 */

import('classes.handler.Handler');

class PKPLoginHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display user login form.
     * Redirect to user index page if user is already validated.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate($request);
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (Validation::isLoggedIn()) {
            $request->redirect(null, 'user');
        }

        if (Config::getVar('security', 'force_login_ssl') && $request->getProtocol() != 'https') {
            // Force SSL connections for login if configured
            $request->redirectSSL();
        }

        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $templateMgr = TemplateManager::getManager();

        // Display any messages passed in the URL (e.g. from a redirect after logout)
        $loginMessage = $request->getUserVar('loginMessage');
        if ($loginMessage) {
            $templateMgr->assign('loginMessage', htmlspecialchars((string) $loginMessage, ENT_QUOTES, 'UTF-8'));
        }

        // [WIZDAM FIX] Baca error dari session hasil PRG dari signIn()
        $loginError = $session->getSessionVar('loginError');
        if ($loginError) {
            $templateMgr->assign('error',    $loginError);
            $templateMgr->assign('reason',   $session->getSessionVar('loginErrorReason'));
            $templateMgr->assign('username', $session->getSessionVar('loginUsername'));
            $templateMgr->assign('remember', (int) $session->getSessionVar('loginRemember'));
            $session->unsetSessionVar('loginError');
            $session->unsetSessionVar('loginErrorReason');
            $session->unsetSessionVar('loginUsername');
            $session->unsetSessionVar('loginRemember');
        } else {
            $templateMgr->assign('username', $session->getSessionVar('username'));
            $templateMgr->assign('remember', (int) $request->getUserVar('remember'));
        }
        
        $source = trim((string) $request->getUserVar('source'));
        if (!empty($source) && !PKPRequest::isPathValid($source)) { $source = ''; }
        $templateMgr->assign('source', $source);
        $templateMgr->assign('showRemember', Config::getVar('general', 'session_lifetime') > 0);

        // [WIZDAM SECURITY] Lempar Variabel Keamanan ke Template Login
        $this->_assignSecurityVariables($templateMgr, 'login');

        // Generate login URL (considering SSL settings)
        $loginUrl = $this->_getLoginUrl($request);
        if (Config::getVar('security', 'force_login_ssl')) {
            $loginUrl = PKPString::regexp_replace('/^http:/', 'https:', $loginUrl);
        }
        $templateMgr->assign('loginUrl', $loginUrl);

        // Suppress HTMLPurifier errors during login template rendering
        $oldErrorReporting = error_reporting();
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);

        $templateMgr->display('user/login.tpl');

        // Restore error reporting
        error_reporting($oldErrorReporting);
    }

    /**
     * Validate user's credentials and log the user in.
     * @param array $args
     * @param PKPRequest $request
     */
    public function signIn($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate($request);
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (Validation::isLoggedIn()) {
            $request->redirect(null, 'user');
        }

        // [WIZDAM SECURITY] Validasi Token Keamanan Sebelum Login
        if (!$this->_validateSecurityTokens($request, 'login')) {
            $templateMgr = TemplateManager::getManager();
            $this->_assignSecurityVariables($templateMgr, 'login');
            $templateMgr->assign('username', $request->getUserVar('username'));
            $templateMgr->assign('error', 'common.captchaField.badCaptcha');
            return $templateMgr->display('user/login.tpl');
        }

        if (Config::getVar('security', 'force_login_ssl') && $request->getProtocol() != 'https') {
            // Force SSL connections for login if configured
            $request->redirectSSL();
        }
        
        $username = trim((string) $request->getUserVar('username'));
        $password = (string) $request->getUserVar('password');
        $remember = (int) $request->getUserVar('remember');
        
        $reason = null;
        $user = Validation::login($username, $password, $reason, $remember == 1);

        if ($user !== false) {
            if ($user->getMustChangePassword()) {
                // User must change their password in order to log in
                Validation::logout();
                $request->redirect(null, null, 'changePassword', $user->getUsername());
            } else {
                $source = trim((string) $request->getUserVar('source'));
                if (!empty($source) && !PKPRequest::isPathValid($source)) { 
                    $source = ''; 
                }

                $redirectNonSsl = Config::getVar('security', 'force_login_ssl') && !Config::getVar('security', 'force_ssl');
                if (!empty($source)) {
                    $request->redirectUrl($source);
                } elseif ($redirectNonSsl) {
                    $request->redirectNonSSL();
                } else {
                    $this->_redirectAfterLogin($request);
                }
            }
        } else {
            // [WIZDAM FIX] PRG Pattern — hindari ERR_CACHE_MISS saat reload
            // Render langsung dari POST endpoint menyebabkan browser menyimpan
            // POST state — reload meminta konfirmasi resubmit.
            $sessionManager = SessionManager::getManager();
            $session = $sessionManager->getUserSession();
            $session->setSessionVar('loginError', $reason === null
                ? 'user.login.loginError'
                : ($reason === '' ? 'user.login.accountDisabled' : 'user.login.accountDisabledWithReason')
            );
            $session->setSessionVar('loginErrorReason',   (string) ($reason ?? ''));
            $session->setSessionVar('loginUsername',       $username);
            $session->setSessionVar('loginRemember',       $remember);
        
            $source = trim((string) $request->getUserVar('source'));
            if (!empty($source) && !PKPRequest::isPathValid($source)) { $source = ''; }
        
            $request->redirect(null, 'login', null, null, !empty($source) ? ['source' => $source] : []);
        }
    }

    /**
     * Handle login when implicitAuth is enabled.
     * Redirects to WAYF for authentication and then back to the site.
     * @param array $args
     * @param PKPRequest $request
     */
    public function implicitAuthLogin($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if ($request->getProtocol() != 'https') $request->redirectSSL();

        $wayf_url = Config::getVar('security', 'implicit_auth_wayf_url');

        if ($wayf_url == '') 
            die('Error in implicit authentication. WAYF URL not set in config file.');

        $url = $wayf_url . '?target=https://' . $request->getServerHost() . $request->getBasePath() . '/index.php/index/login/implicitAuthReturn';

        if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) { 
            $request->redirectUrl($url); 
        } else { 
            $request->redirect(null, 'user'); 
        }
    }

    /**
     * Handle return from WAYF after implicit authentication.
     * Validates the user and logs them in if successful.
     * @param array $args
     * @param PKPRequest $request
     */
    public function implicitAuthReturn($args, $request) {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
    
        // 1. Jika sudah login (sesi OJS aktif), langsung redirect
        if (Validation::isLoggedIn()) {
            $request->redirect(null, 'user');
        }
    
        // 2. Baca identitas dari Web Server (Shibboleth/WAYF)
        //    BUKAN dari POST/GET — ini kunci perbedaannya
        $implicitUsername = '';
        
        // Cek berbagai header yang umum dipakai IdP
        $serverVarCandidates = ['REMOTE_USER', 'HTTP_REMOTE_USER', 'HTTP_EPPN', 'HTTP_PERSISTENT_ID'];
        foreach ($serverVarCandidates as $var) {
            if (!empty($_SERVER[$var])) {
                $implicitUsername = trim((string) $_SERVER[$var]);
                break;
            }
        }
    
        // 3. Jika tidak ada identitas dari server, tolak
        //    Ini menangkap penyerang yang panggil endpoint langsung
        if (empty($implicitUsername)) {
            $request->redirect(null, 'login');
            return;
        }
    
        // 4. Cari user di database berdasarkan identitas dari IdP
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getUserByUsername($implicitUsername)
              ?? $userDao->getUserByEmail($implicitUsername); // Beberapa IdP kirim email
    
        // 5. Jika user tidak ditemukan di OJS, tolak
        if (!$user) {
            $request->redirect(null, 'login');
            return;
        }
    
        // 6. Login tanpa password (kepercayaan diserahkan ke Web Server/IdP)
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $session->setSessionVar('username', $user->getUsername());
        $session->setUserId($user->getId());
        $session->setUser($user);
    
        $request->redirect(null, 'user');
    }

    /**
     * Helper: Get login URL (considering SSL settings)
     * @param PKPRequest $request
     * @return string Login URL
     */
    public function _redirectAfterLogin($request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        $request->redirectHome();
    }

    /**
     * Log the user out and redirect to the login page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function signOut($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate($request);
        
        if (!$request) $request = Application::get()->getRequest();

        if (Validation::isLoggedIn()) {
            Validation::logout();
        }

        // 1. Ambil parameter 'source' jika ada (untuk fleksibilitas)
        $source = trim((string) $request->getUserVar('source'));

        // 2. LAPISAN KEAMANAN: Cegah redirect ke luar situs (Open Redirect)
        if (!empty($source) && !PKPRequest::isPathValid($source)) {
            $source = '';
        }

        // 3. LOGIKA PENGALIHAN
        if (!empty($source)) {
            // Jika ada sumber yang valid, ikuti sumber tersebut
            $request->redirectUrl($request->getProtocol() . '://' . $request->getServerHost() . $source, false);
        } else {
            // PERBAIKAN DI SINI:
            // Jika tidak ada 'source', jangan gunakan getRequestedPage() karena itu adalah 'login'.
            // Kita paksa arahkan ke beranda jurnal/site saat ini.
            
            $journal = $request->getJournal();
            if ($journal) {
                // Redirect ke URL bersih jurnal (tanpa /index)
                $request->redirectUrl($request->url($journal->getPath()));
            } else {
                // Redirect ke home site jika di luar konteks jurnal
                $request->redirect(null, 'index');
            }
        }
    }

    /**
     * Display the lost password form.
     * @param array $args
     * @param PKPRequest $request
     */
    public function lostPassword($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate($request);
        
        // Lempar variabel keamanan ke halaman Lupa Password
        $templateMgr = TemplateManager::getManager();
        $this->_assignSecurityVariables($templateMgr, 'login');
        $templateMgr->display('user/lostPassword.tpl');
    }

    /**
     * Handle password reset request and send confirmation email.
     * @param array $args
     * @param PKPRequest $request
     */
    public function requestResetPassword($args, $request) {
        $this->validate();
        $this->setupTemplate($request);
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager();

        // Validasi keamanan sebelum kirim email reset
        if (!$this->_validateSecurityTokens($request, 'login')) {
            $this->_assignSecurityVariables($templateMgr, 'login');
            $templateMgr->assign('error', 'common.captchaField.badCaptcha');
            return $templateMgr->display('user/lostPassword.tpl');
        }

        $email = trim((string) $request->getUserVar('email'));
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getUserByEmail($email);

        if ($user == null || ($hash = Validation::generatePasswordResetHash($user->getId())) == false) {
            $templateMgr->assign('error', 'user.login.lostPassword.invalidUser');
            $this->_assignSecurityVariables($templateMgr, 'login');
            $templateMgr->display('user/lostPassword.tpl');
        } else {
            $site = $request->getSite();
            import('classes.mail.MailTemplate');
            $mail = new MailTemplate('PASSWORD_RESET_CONFIRM');
            $this->_setMailFrom($request, $mail, $site);
            $mail->assignParams([
                'url' => $request->url(null, 'login', 'resetPassword', $user->getUsername(), ['confirm' => $hash]),
                'siteTitle' => $site->getLocalizedTitle()
            ]);
            $mail->addRecipient($user->getEmail(), $user->getFullName());
            $mail->send();
            $templateMgr->assign('pageTitle',  'user.login.resetPassword');
            $templateMgr->assign('message', 'user.login.lostPassword.confirmationSent');
            $templateMgr->assign('backLink', $request->url(null, $request->getRequestedPage()));
            $templateMgr->assign('backLinkLabel',  'user.login');
            $templateMgr->display('common/message.tpl');
        }
    }

    /**
     * Handle password reset confirmation and update password.
     * @param array $args
     * @param PKPRequest $request
     */
    public function resetPassword($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate($request);
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $site = $request->getSite();
        $oneStepReset = $site->getSetting('oneStepReset') ? true : false;

        $username = isset($args[0]) ? $args[0] : null;
        $userDao = DAORegistry::getDAO('UserDAO');
        $confirmHash = trim((string) $request->getUserVar('confirm'));

        if ($username == null || ($user = $userDao->getByUsername($username)) == null) {
            $request->redirect(null, null, 'lostPassword');
        }

        $templateMgr = TemplateManager::getManager();
        if (!Validation::verifyPasswordResetHash($user->getId(), $confirmHash)) {
            $templateMgr->assign('errorMsg', 'user.login.lostPassword.invalidHash');
            $templateMgr->assign('backLink', $request->url(null, null, 'lostPassword'));
            $templateMgr->assign('backLinkLabel',  'user.login.resetPassword');
            $templateMgr->display('common/error.tpl');
        } elseif (!$oneStepReset) {
            // Logika reset password otomatis...
            // Reset password
            $newPassword = Validation::generatePassword();
            $auth = null;

            if ($user->getAuthId()) {
                $authDao = DAORegistry::getDAO('AuthSourceDAO');
                $auth = $authDao->getPlugin($user->getAuthId());
            }

            if ($auth) {
                $auth->doSetUserPassword($user->getUsername(), $newPassword);
                $user->setPassword(Validation::encryptCredentials($user->getId(), Validation::generatePassword()));
            } else {
                $user->setPassword(Validation::encryptCredentials($user->getUsername(), $newPassword));
            }
            
            $user->setMustChangePassword(1);
            $userDao->updateObject($user);

            // Send email with new password
            import('classes.mail.MailTemplate');
            $mail = new MailTemplate('PASSWORD_RESET');
            $this->_setMailFrom($request, $mail, $site);
            $mail->assignParams([
                'username' => $user->getUsername(),
                'password' => $newPassword,
                'siteTitle' => $site->getLocalizedTitle()
            ]);
            $mail->addRecipient($user->getEmail(), $user->getFullName());
            $mail->send();
            $templateMgr->assign('pageTitle',  'user.login.resetPassword');
            $templateMgr->assign('message', 'user.login.lostPassword.passwordSent');
            $templateMgr->assign('backLink', $request->url(null, $request->getRequestedPage()));
            $templateMgr->assign('backLinkLabel',  'user.login');
            $templateMgr->display('common/message.tpl');
        } else {
            import('classes.user.form.LoginChangePasswordForm');

            $passwordForm = new LoginChangePasswordForm($confirmHash);
            $passwordForm->initData();
            if (isset($args[0])) {
                $passwordForm->setData('username', $username);
            }
            $passwordForm->display();
        }
    }

    /**
     * Display the change password form for users who must change their password.
     * @param array $args
     * @param PKPRequest $request
     */
    public function changePassword($args, $request) {
        $this->validate();
        $this->setupTemplate($request);

        import('classes.user.form.LoginChangePasswordForm');

        $passwordForm = new LoginChangePasswordForm();
        $passwordForm->initData();
        if (isset($args[0])) {
            $passwordForm->setData('username', $args[0]);
        }
        $passwordForm->display();
    }

    /**
     * Handle the submission of the change password form.
     * @param array $args
     * @param PKPRequest $request
     */
    public function savePassword($args, $request) {
        $this->validate();
        $this->setupTemplate($request);
        if (!$request) $request = Application::get()->getRequest();

        $site = $request->getSite();
        $oneStepReset = $site->getSetting('oneStepReset') ? true : false;
        $confirmHash = null;
        if ($oneStepReset) {
            $confirmHash = trim((string) $request->getUserVar('confirmHash'));
        }
        import('classes.user.form.LoginChangePasswordForm');
        $passwordForm = new LoginChangePasswordForm($confirmHash);
        $passwordForm->readInputData();

        if ($passwordForm->validate()) {
            if ($passwordForm->execute()) {
                $reason = null;
                Validation::login($passwordForm->getData('username'), $passwordForm->getData('password'), $reason);
            }
            $request->redirect(null, 'user');
        } else {
            $passwordForm->display();
        }
    }

    /**
     * Helper: Set email sender for password reset emails
     * @param PKPRequest $request
     * @param MailTemplate $mail
     * @param Site $site
     * @return bool True if sender is set successfully, False otherwise
     */
    public function _setMailFrom($request, $mail, $site) {
        $mail->setReplyTo($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
        return true;
    }
}
?>