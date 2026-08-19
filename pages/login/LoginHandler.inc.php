<?php
declare(strict_types=1);

/**
 * @file pages/login/LoginHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LoginHandler
 * @ingroup pages_login
 *
 * @brief Handle login/logout requests.
 * - Added ORCID SSO Integration
 * - Added Google SSO Integration
 */

import('lib.pkp.pages.login.PKPLoginHandler');

class LoginHandler extends PKPLoginHandler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backwards Compatibility
     */
    public function LoginHandler() {
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
     * Magic method to handle dynamic SSO method calls (kebab-case -> camelCase)
     * Example: 'orcid-callback' -> 'orcidCallback'
     * @param mixed $name
     * @param mixed $params
     */
    public function __call($name, $params) {
        $callableOp = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $name))));

        if ($callableOp !== $name && method_exists($this, $callableOp)) {
            return call_user_func_array([$this, $callableOp], $params);
        }

        trigger_error("Call to undefined method " . get_class($this) . "::{$name}()", E_USER_ERROR);
    }

    // 
    // PRIVATE HELPERS
    // 

    /**
     * [LUMERA HELPER] Set session directly for SSO login.
     * @param object $user    User object from UserDAO
     * @param object $session Session from SessionManager
     */
    private function _setSSOSession($user, $session): void {
        // 1. Set atribut session di memori
        $session->setUserId($user->getId());
        $session->setSessionVar('username', $user->getUsername());
        $session->setRemember(1); // Tetapkan 1 agar sesi lebih tahan lama
    
        // 2. Inject user ke Registry agar request ini menganggap user sudah login
        Registry::set('user', $user);
    
        // 3. [KUNCI UTAMA] Paksa pembuatan Session ID baru.
        // Ini akan menginstruksikan browser membuat cookie baru dan 
        // secara otomatis menyimpan status userId yang baru ke tabel 'sessions'.
        SessionManager::getManager()->regenerateSessionId();
    
        // 4. Update tabel 'sessions' secara eksplisit sebagai pengaman ekstra
        // agar tidak ditimpa oleh session_write_close() bawaan PHP
        /** @var SessionDAO $sessionDao */
        $sessionDao = DAORegistry::getDAO('SessionDAO');
        $sessionDao->updateObject($session);

        // 5. Update data terakhir login user
        $user->setDateLastLogin(Core::getCurrentDate());
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $userDao->updateObject($user);
    }

    /**
     * [LUMERA HELPER] Build OAuth redirect URI at site level, never journal context.
     *
     * The redirect URI registered in Google/ORCID Console is always site-level:
     *   https://journals.sangia.org/login/google-callback
     *
     * Using $request->url() would inject the active journal path (e.g. /AGRIKAN/)
     * causing redirect_uri_mismatch. base_url from config is the only correct source.
     * Journal context is carried separately in the OAuth state parameter.
     *
     * @param string $provider 'orcid' or 'google'
     * @return string Site-level redirect URI
     */
    private function _buildRedirectUri(string $provider): string {
        return rtrim(Config::getVar('general', 'base_url'), '/') . '/login/' . $provider . '-callback';
    }

    /**
     * [LUMERA HELPER] Resolve the pre-login return URL, discarding login pages.
     *
     * APP passes 'source' when redirecting to login. loginReturnUrl in session may
     * also hold the login page itself if the user navigated directly to it.
     * Both cases must be discarded so the user is not looped back to login after SSO.
     *
     * @param object $request PKPRequest
     * @param object $session Session instance
     * @return string Valid return URL or empty string
     */
    private function _resolveReturnUrl($request, $session): string {
        // 'source' is the App native parameter set when redirecting to login page
        $returnUrl = $request->getUserVar('source')
                  ?? $request->getUserVar('returnUrl')
                  ?? $session->getSessionVar('loginReturnUrl')
                  ?? '';

        // Discard if it points back to the login page itself
        if ($returnUrl && (
            strpos($returnUrl, '/login') !== false ||
            strpos($returnUrl, 'signIn') !== false
        )) {
            return '';
        }

        return (string) $returnUrl;
    }

    //
    // Database Lookup Helpers
    //

    /**
     * Find user by ORCID URL stored in user_settings.
     * @param string $orcidUrl Full ORCID URL: https://orcid.org/XXXX-XXXX-XXXX-XXXX
     * @return object|null User object or null if not found
     */
    private function _getUserByOrcidUrl(string $orcidUrl): ?object {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getUserByOrcid($orcidUrl);
    }

    /**
     * Find an existing or empty key for a Google email.
     * Supports multi-email: google_email_0 through google_email_4.
     * @param int    $userId
     * @param string $email
     * @param object $userSettingsDao
     * @return string The setting_name to use
     */
    private function _findOrCreateEmailKey(int $userId, string $email, UserSettingsDAO $userSettingsDao): string {
        for ($i = 0; $i <= 4; $i++) {
            $key      = 'google_email_' . $i;
            $existing = $userSettingsDao->getSetting($userId, $key);
            if ($existing === $email || $existing === null || $existing === '') {
                return $key;
            }
        }
        return 'google_email_0';
    }

    // 
    // LUMERA SSO: ORCID INTEGRATION
    // 

    /**
     * Initiate ORCID authentication flow.
     * Redirects the user to the ORCID authorization server.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function orcidAuth($args, $request) {
        if (!Config::getVar('sso', 'sso_enabled') || !Config::getVar('sso', 'orcid')) {
            $request->redirect(null, 'login');
            return;
        }

        $clientId    = Config::getVar('sso', 'orcid_client_id');
        $isSandbox   = (bool) Config::getVar('sso', 'orcid_sandbox');
        $authBaseUrl = $isSandbox
            ? 'https://sandbox.orcid.org/oauth/authorize'
            : 'https://orcid.org/oauth/authorize';

        // Site-level URI — never includes journal context
        $redirectUri = $this->_buildRedirectUri('orcid');

        $sessionManager = SessionManager::getManager();
        $session        = $sessionManager->getUserSession();

        $nonce    = bin2hex(random_bytes(16));
        $nonceKey = 'oauth_nonce_orcid_' . substr($nonce, 0, 8);
        $session->setSessionVar($nonceKey, $nonce);

        $currentJournal = $request->getJournal();
        $contextPath    = $currentJournal ? $currentJournal->getPath() : '';

        // Journal context and returnUrl travel in state, not in redirect_uri
        $returnUrl    = $this->_resolveReturnUrl($request, $session);
        $statePayload = $contextPath . '|' . $nonceKey . '|' . $nonce . '|' . $returnUrl;

        $authUrl = $authBaseUrl
            . '?client_id='    . urlencode($clientId)
            . '&response_type=code&scope=/authenticate'
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&state='        . urlencode($statePayload);

        $request->redirectUrl($authUrl);
    }

    /**
     * Handle callback from ORCID after authentication.
     * Logs in user if ORCID is linked, or redirects to register if not.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function orcidCallback($args, $request) {
        $sessionManager = SessionManager::getManager();
        $session        = $sessionManager->getUserSession();

        // Validate State & Nonce ---
        // state = contextPath|nonceKey|nonce|returnUrl
        $stateParts       = explode('|', (string) $request->getUserVar('state'), 4);
        $rawContext       = $stateParts[0] ?? '';
        $receivedNonceKey = $stateParts[1] ?? '';
        $receivedNonce    = $stateParts[2] ?? '';
        $returnUrl        = $stateParts[3] ?? '';

        $contextPath = preg_match('/^[a-zA-Z0-9_\-\.]+$/', $rawContext) ? $rawContext : null;

        if (!preg_match('/^oauth_nonce_(orcid|google)_[0-9a-f]{8}$/', $receivedNonceKey)) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $storedNonce = $session->getSessionVar($receivedNonceKey);
        $session->unsetSessionVar($receivedNonceKey);

        if (!$storedNonce || !hash_equals($storedNonce, $receivedNonce)) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $code = $request->getUserVar('code');
        if (!$code) {
            $request->redirect($contextPath, 'login');
            return;
        }

        // --- Exchange authorization code for access token ---
        $clientId     = Config::getVar('sso', 'orcid_client_id');
        $clientSecret = Config::getVar('sso', 'orcid_client_secret');
        $isSandbox    = (bool) Config::getVar('sso', 'orcid_sandbox');
        $tokenBaseUrl = $isSandbox
            ? 'https://sandbox.orcid.org/oauth/token'
            : 'https://orcid.org/oauth/token';

        // Must match exactly what was sent in orcidAuth()
        $redirectUri = $this->_buildRedirectUri('orcid');

        $ch = curl_init($tokenBaseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        if (!isset($data->orcid)) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $orcidId  = $data->orcid;
        $orcidUrl = 'https://orcid.org/' . $orcidId;

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        // 
        // LINK ACCOUNT
        // 
        if (Validation::isLoggedIn()) {
            $currentUser  = $request->getUser();
            $existingUser = $this->_getUserByOrcidUrl($orcidUrl);

            if ($existingUser && $existingUser->getId() != $currentUser->getId()) {
                $request->redirect($contextPath, 'user', 'linked-accounts', null, ['error' => 'orcid_in_use']);
                return;
            }

            $currentUser->setData('orcid', $orcidUrl);
            $userDao->updateObject($currentUser);
            Registry::set('user', $userDao->getById($currentUser->getId()));

            $request->redirect($contextPath, 'user', 'linked-accounts', null, ['success' => 'orcid_linked']);
            return;
        }

        // 
        // LOGIN Callback
        // 
        $user = $this->_getUserByOrcidUrl($orcidUrl);

        if ($user) {
            $this->_setSSOSession($user, $session);

            // Return to originating page from state, fallback to journal dashboard
            if ($returnUrl && PKPRequest::isPathValid($returnUrl)) {
                $request->redirectUrl($returnUrl);
            } else {
                $request->redirect($contextPath ?: null, 'user');
            }
            return;

        } else {
            $apiBaseUrl = $isSandbox
                ? 'https://api.sandbox.orcid.org/v3.0/'
                : 'https://pub.orcid.org/v3.0/';

            $chApi = curl_init($apiBaseUrl . $orcidId . '/record');
            curl_setopt($chApi, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chApi, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Authorization: Bearer ' . ($data->access_token ?? ''),
            ]);
            $recordData = json_decode(curl_exec($chApi), true);
            curl_close($chApi);

            $session->setSessionVar('orcid_data', [
                'orcid'       => $orcidUrl,
                'firstName'   => $data->name ?? '',
                'lastName'    => ' ',
                'affiliation' => $recordData['activities-summary']['employments']['affiliation-group'][0]['summaries'][0]['employment-summary']['organization']['name'] ?? '',
                'biography'   => $recordData['person']['biography']['content'] ?? '',
            ]);
            $request->redirect($contextPath ?: null, 'register');  // [WIZDAM] Dipindah ke page="register" tersendiri
        }
    }

    /**
     * Unlink ORCID from the current user account.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function orcidUnlink($args, $request) {
        $this->validate();

        if (!Validation::isLoggedIn()) {
            $request->redirect(null, 'login');
            return;
        }

        $user = $request->getUser();
        if (!$user) {
            $request->redirect(null, 'login');
            return;
        }

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $user->setData('orcid', null);
        $userDao->updateObject($user);

        $freshUser = $userDao->getById($user->getId());
        Registry::set('user', $freshUser);

        $request->redirect(null, 'user', 'linked-accounts', null, ['success' => 'orcid_unlinked']);
    }

    //
    // LUMERA SSO: GOOGLE INTEGRATION
    //

    /**
     * Initiate Google authentication flow.
     * Redirects the user to the Google authorization server.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function googleAuth($args, $request) {
        if (!Config::getVar('sso', 'sso_enabled') || !Config::getVar('sso', 'google')) {
            $request->redirect(null, 'login');
            return;
        }

        $clientId = Config::getVar('sso', 'google_client_id');

        // Site-level URI — never includes journal context
        $redirectUri = $this->_buildRedirectUri('google');

        $sessionManager = SessionManager::getManager();
        $session        = $sessionManager->getUserSession();

        $nonce    = bin2hex(random_bytes(16));
        $nonceKey = 'oauth_nonce_google_' . substr($nonce, 0, 8);
        $session->setSessionVar($nonceKey, $nonce);

        $currentJournal = $request->getJournal();
        $contextPath    = $currentJournal ? $currentJournal->getPath() : '';

        // Journal context and returnUrl travel in state, not in redirect_uri
        $returnUrl    = $this->_resolveReturnUrl($request, $session);
        $statePayload = $contextPath . '|' . $nonceKey . '|' . $nonce . '|' . $returnUrl;

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'email profile',
            'state'         => $statePayload,
            'access_type'   => 'online',
        ]);

        $request->redirectUrl($authUrl);
    }

    /**
     * Handle callback from Google after authentication.
     * Logs in user if Google ID is linked, or redirects to register if not.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function googleCallback($args, $request) {
        $sessionManager = SessionManager::getManager();
        $session        = $sessionManager->getUserSession();

        // Validate State & Nonce ---
        // state = contextPath|nonceKey|nonce|returnUrl
        $stateParts       = explode('|', (string) $request->getUserVar('state'), 4);
        $rawContext       = $stateParts[0] ?? '';
        $receivedNonceKey = $stateParts[1] ?? '';
        $receivedNonce    = $stateParts[2] ?? '';
        $returnUrl        = $stateParts[3] ?? '';

        $contextPath = preg_match('/^[a-zA-Z0-9_\-\.]+$/', $rawContext) ? $rawContext : null;

        if (!preg_match('/^oauth_nonce_(orcid|google)_[0-9a-f]{8}$/', $receivedNonceKey)) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $storedNonce = $session->getSessionVar($receivedNonceKey);
        $session->unsetSessionVar($receivedNonceKey);

        if (!$storedNonce || !hash_equals($storedNonce, $receivedNonce)) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $code = $request->getUserVar('code');
        if (!$code) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $clientId     = Config::getVar('sso', 'google_client_id');
        $clientSecret = Config::getVar('sso', 'google_client_secret');

        // Must match exactly what was sent in googleAuth()
        $redirectUri = $this->_buildRedirectUri('google');

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUri,
        ]));
        $tokenData = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($tokenData['access_token'])) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $chApi = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($chApi, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chApi, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
        $userInfo = json_decode(curl_exec($chApi), true);
        curl_close($chApi);

        if (!isset($userInfo['id'])) {
            $request->redirect($contextPath, 'login');
            return;
        }

        $googleId    = $userInfo['id'];
        $googleEmail = $userInfo['email']       ?? '';
        $firstName   = $userInfo['given_name']  ?? '';
        $lastName    = $userInfo['family_name'] ?? ' ';

        /** @var UserSettingsDAO $userSettingsDao */
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        /** @var UserDAO $userDao */
        $userDao         = DAORegistry::getDAO('UserDAO');

        // 
        // LINK ACCOUNT
        // 
        if (Validation::isLoggedIn()) {
            $currentUser   = $request->getUser();
            $usersIterator = $userSettingsDao->getUsersBySetting('google_id', $googleId);
            $isUsedByOther = false;

            if ($usersIterator && !$usersIterator->eof()) {
                $existingUser = $usersIterator->next();
                if ($existingUser->getId() != $currentUser->getId()) {
                    $isUsedByOther = true;
                }
            }

            if ($isUsedByOther) {
                $request->redirect($contextPath, 'user', 'linked-accounts', null, ['error' => 'google_in_use']);
            } else {
                $userSettingsDao->updateSetting($currentUser->getId(), 'google_id', $googleId, 'string');

                // [MULTI-EMAIL] Store Google email in its own field, never overwrites
                // the primary App email. User may link a different Google account email.
                if (!empty($googleEmail)) {
                    $emailKey = $this->_findOrCreateEmailKey((int) $currentUser->getId(), $googleEmail, $userSettingsDao);
                    $userSettingsDao->updateSetting($currentUser->getId(), $emailKey, $googleEmail, 'string');
                }

                $request->redirect($contextPath, 'user', 'linked-accounts', null, ['success' => 'google_linked']);
            }
            return;
        }

        // 
        // LOGIN
        // 
        $usersIterator = $userSettingsDao->getUsersBySetting('google_id', $googleId);
        $user          = null;

        if ($usersIterator && !$usersIterator->eof()) {
            $user = $usersIterator->next();
        }

        // [GOOGLE] Fallback Auto-Discovery berdasarkan Email Utama
        if (!$user && !empty($googleEmail)) {
            $user = $userDao->getUserByEmail($googleEmail);
            
            // Jika akun ditemukan via email, langsung auto-link Google ID-nya!
            if ($user) {
                $userId = (int) $user->getId();
                
                $userSettingsDao->updateSetting($userId, 'google_id', $googleId, 'string');
                $emailKey = $this->_findOrCreateEmailKey($userId, $googleEmail, $userSettingsDao);
                $userSettingsDao->updateSetting($userId, $emailKey, $googleEmail, 'string');
            }
        }

        if ($user) {
            $this->_setSSOSession($user, $session);

            // Return to originating page from state, fallback to journal dashboard
            if ($returnUrl && PKPRequest::isPathValid($returnUrl)) {
                $request->redirectUrl($returnUrl);
            } else {
                $request->redirect($contextPath ?: null, 'user');
            }
            return;

        } else {
            // Jika benar-benar akun baru (ID tidak ada, Email Utama tidak ada)
            $session->setSessionVar('google_data', [
                'google_id' => $googleId,
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'email'     => $googleEmail,
            ]);
            $request->redirect($contextPath ?: null, 'register');  // [WIZDAM] Dipindah ke page="register" tersendiri
        }
    }

    /**
     * Unlink Google from the current user account.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function googleUnlink($args, $request) {
        $this->validate();

        $user = $request->getUser();
        if (!$user) {
            $request->redirect(null, 'login');
            return;
        }

        /** @var UserDAO $userDao */
        $userDao         = DAORegistry::getDAO('UserDAO');
        /** @var UserSettingsDAO $userSettingsDao */
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');

        $userSettingsDao->deleteSetting($user->getId(), 'google_id');

        // Remove all Google email fields
        for ($i = 0; $i <= 4; $i++) {
            $userSettingsDao->deleteSetting($user->getId(), 'google_email_' . $i);
        }

        $freshUser = $userDao->getById($user->getId());
        Registry::set('user', $freshUser);

        $request->redirect(null, 'user', 'linked-accounts', null, ['success' => 'google_unlinked']);
    }

    // 
    // STANDARD METHODS
    // 

    /**
     * Override validate to ensure proper access control for SSO actions.
     * ORCID/Google Auth & Callback are accessible without prior login.
     * @copydoc PKPHandler::validate()
     */
    function validate($sandbox = false, $requiredContexts = null, $request = null) {
        return parent::validate($sandbox);
    }

    /**
     * Allow site admins and journal managers to sign in as another user for troubleshooting.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function signInAsUser($args, $request) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SITE_ADMIN, ROLE_ID_JOURNAL_MANAGER]));
        $this->validate();

        if (!$request) $request = Application::get()->getRequest();

        if (isset($args[0]) && !empty($args[0])) {
            $userId  = (int) $args[0];
            $journal = $request->getJournal();

            if (!Validation::canAdminister($journal->getId(), $userId)) {
                $this->setupTemplate($request);
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('pageTitle',     'manager.people');
                $templateMgr->assign('errorMsg',      'manager.people.noAdministrativeRights');
                $templateMgr->assign('backLink',      $request->url(null, 'manager', 'people', 'all'));
                $templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
                return $templateMgr->display('common/error.tpl');
            }

            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $newUser = $userDao->getById($userId);
            $session = $request->getSession();

            if (isset($newUser) && $session->getUserId() != $newUser->getId()) {
                $session->setSessionVar('signedInAs', $session->getUserId());
                $session->setSessionVar('userId',    $userId);
                $session->setUserId($userId);
                $session->setSessionVar('username',  $newUser->getUsername());
                $request->redirect(null, 'user');
            }
        }

        $request->redirect(null, $request->getRequestedPage());
    }

    /**
     * Allow site admins and journal managers to sign out from a user they signed in as.
     * @param array  $args
     * @param object $request PKPRequest
     */
    public function signOutAsUser($args, $request) {
        $this->validate();

        if (!$request) $request = Application::get()->getRequest();

        $session    = $request->getSession();
        $signedInAs = $session->getSessionVar('signedInAs');

        if (isset($signedInAs) && !empty($signedInAs)) {
            $signedInAs = (int) $signedInAs;
            /** @var UserDAO $userDao */
            $userDao    = DAORegistry::getDAO('UserDAO');
            $oldUser    = $userDao->getById($signedInAs);
            $session->unsetSessionVar('signedInAs');

            if (isset($oldUser)) {
                $session->setSessionVar('userId',   $signedInAs);
                $session->setUserId($signedInAs);
                $session->setSessionVar('username', $oldUser->getUsername());
            }
        }

        $request->redirect(null, 'user');
    }

    /**
     * Override to ensure login URL is correctly generated for SSO flows.
     * @param object $request PKPRequest
     * @return string
     */
    public function _getLoginUrl($request) {
        return $request->url(null, 'login', 'signIn');
    }

    /**
     * Override to set appropriate "From" address for system emails based on journal settings.
     * @param object      $request PKPRequest
     * @param object      $mail    MailTemplate
     * @param object|null $site    optional
     */
    public function _setMailFrom($request, $mail, $site = null) {
        $site    = $request->getSite();
        $journal = $request->getJournal();

        if ($journal && $journal->getSetting('supportEmail')) {
            $mail->setFrom($journal->getSetting('supportEmail'), $journal->getSetting('supportName'));
        } else {
            $mail->setFrom($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
        }
    }

    /**
     * Override to load locale components for the login page.
     * @param object|null $request PKPRequest
     */
    public function setupTemplate($request = null) {
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_CORE_MANAGER);
        parent::setupTemplate($request);
    }

}
?>