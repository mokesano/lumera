<?php
declare(strict_types=1);

/**
 * @file classes/security/Validation.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Validation
 * @ingroup security
 *
 * @brief Class providing user validation/authentication operations.
 * [WIZDAM EDITION] Auto-Migrate Legacy Passwords to Bcrypt
 */

import('classes.security.Role');
import('classes.security.Hashing');

define('IMPLICIT_AUTH_OPTIONAL', 'optional');

class Validation {

    /**
     * Authenticate user credentials and mark the user as logged in in the current session.
     * @param $username string authenticating user's id
     * @param $password string unencrypted password
     * @param $reason string reference to string to receive the reason an account was disabled
     * @param $remember boolean remember a user's session
     * @return User|false
     */
    public static function login($username, $password, &$reason, $remember = false) {
        $implicitAuth = strtolower((string) Config::getVar('security', 'implicit_auth'));

        $reason = null;
        $valid = false;
        $userDao = DAORegistry::getDAO('UserDAO');

        if ($implicitAuth && !$username) { // Implicit auth
            if (!Validation::isLoggedIn()) {
                PluginRegistry::loadCategory('implicitAuth');
                $user = null; // Initialize variable for hook
                HookRegistry::dispatch('ImplicitAuthPlugin::implicitAuth', array(&$user));
                $valid = true;
            }
        } else { 
            // Regular Auth
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $user = $userDao->getUserByEmail($username);
            } else {
                $user = $userDao->getByUsername($username, true);
            }

            if (!isset($user)) {
                return false;
            }
            
            $username = $user->getUsername();

            $auth = null;
            if ($user->getAuthId()) {
                $authDao = DAORegistry::getDAO('AuthSourceDAO');
                $auth = $authDao->getPlugin($user->getAuthId());
            }

            if (isset($auth)) {
                $valid = $auth->authenticate($username, $password);
                if ($valid) {
                    $oldEmail = $user->getEmail();
                    $auth->doGetUserInfo($user);
                    if ($user->getEmail() != $oldEmail) {
                        if ($userDao->userExistsByEmail($user->getEmail())) {
                            $user->setEmail($oldEmail);
                        }
                    }
                }
            } else {
                // [WIZDAM SECURITY CORE]
                // Validate against user database with Auto-Upgrade logic
                
                $storedHash = $user->getPassword();
                $rehash = ''; 
                
                $hashing = new Hashing();
                
                // 1. Cek Modern Hash (Bcrypt)
                if ($hashing->isValid($password, $storedHash)) {
                    $valid = true;
                    // Cek apakah perlu rehash (misal cost factor berubah)
                    if ($hashing->needsRehash($storedHash)) {
                        $rehash = $hashing->getHash($password);
                    }
                } 
                // 2. Cek Legacy Hash (MD5/SHA1 + Salt)
                else {
                    // Coba rekonstruksi hash lama
                    $legacyHash = Validation::encryptCredentials($username, $password, false, true);
                    
                    if ($legacyHash === $storedHash) {
                        $valid = true;
                        // Password benar tapi format lama. JADWALKAN UPGRADE!
                        $rehash = $hashing->getHash($password);
                    }
                }

                if ($valid && !empty($rehash)) {
                    // Update user password to new Bcrypt hash
                    $user->setPassword($rehash);
                    $userDao->updateObject($user);
                }
            }
        }

        if (!$valid) {
            return false;
        } else {
            if ($user->getDisabled()) {
                $reason = $user->getDisabledReason();
                if ($reason === null) $reason = '';
                return false;
            }

            // [WIZDAM ARCHITECTURE FIX]
            // Delegasikan seluruh pengaturan sesi 
            // (Regenerate ID, Set Data, Simpan DB) ke SessionManager agar Validation tetap bersih dan tidak mengurusi teknis DB.
            // Metode renewUserSession ini menjamin data tersimpan sebelum redirect.
            $sessionManager = SessionManager::getManager();
            $sessionManager->renewUserSession($user->getId(), $user->getUsername(), $remember);

            // [NEW 2026] Simpan Last Login lama ke Database (tabel user_settings) sebelum ditimpa
            $oldLastLogin = $user->getDateLastLogin();
            if ($oldLastLogin) {
                $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
                // Simpan dengan nama setting 'previous_login'
                $userSettingsDao->updateSetting($user->getId(), 'previous_login', $oldLastLogin, 'string');
            }

            // Update waktu login terakhir user
            $user->setDateLastLogin(Core::getCurrentDate());
            $userDao->updateObject($user);

            return $user;
        }
    }

    /**
     * Mark the user as logged out in the current session.
     * [MODERNISASI] Made static
     */
    public static function logout() {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $session->unsetSessionVar('userId');
        $session->unsetSessionVar('signedInAs');
        $session->setUserId(null);

        if ($session->getRemember()) {
            $session->setRemember(0);
            $sessionManager->updateSessionLifetime(0);
        }

        $sessionDao = DAORegistry::getDAO('SessionDAO');
        $sessionDao->updateObject($session);

        return true;
    }

    /**
     * Redirect to the login page.
     * [MODERNISASI] Made static
     * @param $message string optional message to display on the login page
     */
    public static function redirectLogin($message = null) {
        $args = array();
        if (isset($_SERVER['REQUEST_URI'])) {
            $args['source'] = $_SERVER['REQUEST_URI'];
        }
        if ($message !== null) {
            $args['loginMessage'] = $message;
        }
        Request::redirect(null, 'login', null, null, $args);
    }

    /**
     * Check if a user's credentials are valid.
     * [MODERNISASI] Updated to use Hashing class logic
     * @param $username string
     * @param $password string
     * @return boolean
     */
    public static function checkCredentials($username, $password) {
        $userDao = DAORegistry::getDAO('UserDAO');
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $userDao->getUserByEmail($username);
        } else {
            $user = $userDao->getByUsername($username, false);
        }

        if (isset($user)) {
            $username = $user->getUsername();
            
            if ($user->getAuthId()) {
                $authDao = DAORegistry::getDAO('AuthSourceDAO');
                $auth = $authDao->getPlugin($user->getAuthId());
            }

            if (isset($auth)) {
                return $auth->authenticate($username, $password);
            } else {
                $hashing = new Hashing();
                $storedHash = $user->getPassword();
                
                // Cek Modern
                if ($hashing->isValid($password, $storedHash)) return true;
                
                // Cek Legacy
                $legacyHash = Validation::encryptCredentials($username, $password, false, true);
                if ($legacyHash === $storedHash) return true;
            }
        }
        return false;
    }

    /**
     * Check if a user is authorized.
     * [MODERNISASI] Made static
     * @param $roleId int
     * @param $journalId int optional journal id to check for, or -1 to check for current journal, or 0 for site-wide roles
     * @return boolean
     */
    public static function isAuthorized($roleId, $journalId = 0) {
        if (!Validation::isLoggedIn()) return false;

        if ($journalId === -1) {
            $journal = Request::getJournal();
            $journalId = $journal == null ? 0 : $journal->getId();
        }

        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $user = $session->getUser();

        $roleDao = DAORegistry::getDAO('RoleDAO');
        return $roleDao->userHasRole($journalId, $user->getId(), $roleId);
    }

    /**
     * Encrypt user passwords for database storage.
     * [MODERNISASI] Supports legacy fallback for verification
     * @param $username string
     * @param $password string
     * @param $encryption string optional encryption method for legacy (md5 or sha1)
     * @param $legacy boolean whether to generate legacy hash (default false)
     * @return string encrypted password
     */
    public static function encryptCredentials($username, $password, $encryption = false, $legacy = false) {
        // Jika mode LEGACY diminta (untuk verifikasi password lama)
        if ($legacy) {
            if ($encryption == false) {
                $encryption = Config::getVar('security', 'encryption', 'md5');
            }
            $salt = Config::getVar('security', 'salt');
            $valueToEncrypt = $password . $salt; 
            
            switch ($encryption) {
                case 'sha1':
                    return sha1($valueToEncrypt);
                case 'md5':
                default:
                    return md5($valueToEncrypt);
            }
        } 
        
        // Mode MODERN (Default): Gunakan Hashing class (Bcrypt)
        $hashing = new Hashing();
        return $hashing->getHash($password);
    }

    /**
     * Generate a random password.
     * @param $length int
     * @return string
     */
    public static function generatePassword($length = 8) {
        return substr(str_shuffle('abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, $length);
    }

    /**
     * Generate a hash value to use for confirmation to reset a password.
     * @param $userId int
     * @param $expiry int optional expiry time for the hash (in seconds since the epoch).
     * @return string|false hash value with expiry time, or false if the user id
     */
    public static function generatePasswordResetHash($userId, $expiry = null) {
        $userDao = DAORegistry::getDAO('UserDAO');
        if (($user = $userDao->getById($userId)) == null) return false;

        $salt = Config::getVar('security', 'salt');
        if (empty($expiry)) {
            $expires = (int) Config::getVar('security', 'reset_seconds', 7200);
            $expiry = time() + $expires;
        }

        $data = $user->getUsername() . $user->getPassword() . $user->getDateLastLogin() . $expiry;

        if (function_exists('hash_hmac')) {
            return hash_hmac('sha256', $data, $salt) . ':' . $expiry;
        }
        return md5($data . $salt) . ':' . $expiry;
    }

    /**
     * Check if provided password reset hash is valid.
     * @param $userId int
     * @param $hash string
     * @return boolean
     */
    public static function verifyPasswordResetHash($userId, $hash) {
        list(, $expiry) = explode(':', $hash . ':');
        if (empty($expiry) || ((int) $expiry < time())) return false;
        return ($hash === Validation::generatePasswordResetHash($userId, $expiry));
    }

    /**
     * Suggest a username.
     * @param $firstName string
     * @param $lastName string
     * @return string
     */
    public static function suggestUsername($firstName, $lastName) {
        $initial = PKPString::substr($firstName, 0, 1);
        $suggestion = PKPString::regexp_replace('/[^a-zA-Z0-9_-]/', '', PKPString::strtolower($initial . $lastName));
        $userDao = DAORegistry::getDAO('UserDAO');
        for ($i = ''; $userDao->userExistsByUsername($suggestion . $i); $i++);
        return $suggestion . $i;
    }

    /**
     * Check if logged in.
     * @return boolean
     */
    public static function isLoggedIn() {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $userId = $session->getUserId();
        return isset($userId) && !empty($userId);
    }

    // --- Shortcuts for Role Checks ---

    /**
     * Shortcut for checking authorization as site admin.
     * @return boolean
     */
    public static function isSiteAdmin() {
        return Validation::isAuthorized(ROLE_ID_SITE_ADMIN);
    }

    /**
     * Shortcut for checking authorization as journal manager.
     * @param $journalId int
     * @return boolean
     */
    public static function isJournalManager($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_JOURNAL_MANAGER, $journalId);
    }

    /**
     * Shortcut for checking authorization as editor.
     * @param $journalId int
     * @return boolean
     */
    public static function isEditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_EDITOR, $journalId);
    }

    /**
     * Shortcut for checking authorization as section editor.
     * @param $journalId int
     * @return boolean
     */
    public static function isSectionEditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_SECTION_EDITOR, $journalId);
    }

    /**
     * Shortcut for checking authorization as layout editor.
     * @param $journalId int
     * @return boolean
     */
    public static function isLayoutEditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_LAYOUT_EDITOR, $journalId);
    }

    /**
     * Shortcut for checking authorization as reviewer.
     * @param $journalId int
     * @return boolean
     */
    public static function isReviewer($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_REVIEWER, $journalId);
    }

    /**
     * Shortcut for checking authorization as copyeditor.
     * @param $journalId int
     * @return boolean
     */
    public static function isCopyeditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_COPYEDITOR, $journalId);
    }

    /**
     * Shortcut for checking authorization as proofreader.
     * @param $journalId int
     * @return boolean
     */
    public static function isProofreader($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_PROOFREADER, $journalId);
    }

    /**
     * Shortcut for checking authorization as author.
     * @param $journalId int
     * @return boolean
     */
    public static function isAuthor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_AUTHOR, $journalId);
    }

    /**
     * Shortcut for checking authorization as reader.
     * @param $journalId int
     * @return boolean
     */
    public static function isReader($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_READER, $journalId);
    }

    /**
     * Shortcut for checking authorization as subscription manager.
     * @param $journalId int
     * @return boolean
     */
    public static function isSubscriptionManager($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_SUBSCRIPTION_MANAGER, $journalId);
    }

    /**
     * Check whether a user is allowed to administer another user.
     * @param $journalId int
     * @param $userId int
     * @return boolean
     */
    public static function canAdminister($journalId, $userId) {
        if (Validation::isSiteAdmin()) return true;
        if (!Validation::isJournalManager($journalId)) return false;

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDao->getRolesByUserId($userId);
        foreach ($roles as $role) {
            if ($role->getRoleId() == ROLE_ID_SITE_ADMIN) return false;
            if (
                $role->getJournalId() != $journalId &&
                !Validation::isJournalManager($role->getJournalId())
            ) return false;
        }
        return true;
    }
}

?>