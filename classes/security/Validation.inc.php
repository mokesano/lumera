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
 */

import('classes.security.Role');
import('classes.security.Hashing');

define('IMPLICIT_AUTH_OPTIONAL', 'optional');

class Validation {

    /**
     * Authenticate user credentials and mark the user as logged in in the current session.
     * @param string $username
     * @param string $password
     * @param string|null $reason
     * @param bool $remember
     * @return User|false
     */
    public static function login($username, $password, &$reason, $remember = false) {
        $implicitAuth = strtolower((string) Config::getVar('security', 'implicit_auth'));

        $reason = null;
        $valid = false;
        $user = null; // [LUMERA] Initialize to prevent undefined variable warnings

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        if ($implicitAuth && !$username) {
            if (!Validation::isLoggedIn()) {
                PluginRegistry::loadCategory('implicitAuth');
                HookRegistry::dispatch('ImplicitAuthPlugin::implicitAuth', [&$user]);
                $valid = true;
            }
        } else {
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $user = $userDao->getUserByEmail($username);
            } else {
                $user = $userDao->getByUsername($username, true);
            }

            if ($user === null) {
                return false;
            }
            
            $username = (string) $user->getUsername();
            $auth = null;
            $authId = $user->getAuthId();

            if ($authId !== null && $authId !== 0) {
                /** @var AuthSourceDAO $authDao */
                $authDao = DAORegistry::getDAO('AuthSourceDAO');
                $auth = $authDao->getPlugin((int) $authId);
            }

            if ($auth !== null) {
                $valid = $auth->authenticate($username, $password);
                if ($valid) {
                    $oldEmail = $user->getEmail();
                    $auth->doGetUserInfo($user);
                    if ($user->getEmail() !== $oldEmail) {
                        if ($userDao->userExistsByEmail($user->getEmail())) {
                            $user->setEmail($oldEmail);
                        }
                    }
                }
            } else {
                // [WIZDAM SECURITY CORE] Validate against user database with Auto-Upgrade logic
                $storedHash = (string) $user->getPassword();
                $rehash = ''; 
                $hashing = new Hashing();
                
                // 1. Check Modern Hash (Bcrypt)
                if ($hashing->isValid($password, $storedHash)) {
                    $valid = true;
                    if ($hashing->needsRehash($storedHash)) {
                        $rehash = $hashing->getHash($password);
                    }
                } 
                // 2. Check Legacy Hash (MD5/SHA1 + Salt)
                else {
                    $legacyHash = Validation::encryptCredentials($username, $password, false, true);
                    if ($legacyHash === $storedHash) {
                        $valid = true;
                        $rehash = $hashing->getHash($password);
                    }
                }

                if ($valid && $rehash !== '') {
                    $user->setPassword($rehash);
                    $userDao->updateObject($user);
                }
            }
        }

        if (!$valid || $user === null) {
            return false;
        }

        if ($user->getDisabled()) {
            $reason = $user->getDisabledReason();
            if ($reason === null) {
                $reason = '';
            }
            return false;
        }

        // [WIZDAM ARCHITECTURE FIX] Delegate session management
        $sessionManager = SessionManager::getManager();
        $sessionManager->renewUserSession((int) $user->getId(), (string) $user->getUsername(), (bool) $remember);

        // Save old last login to user_settings before overwriting
        $oldLastLogin = $user->getDateLastLogin();
        if ($oldLastLogin !== null && $oldLastLogin !== '') {
            /** @var UserSettingsDAO $userSettingsDao */
            $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
            $userSettingsDao->updateSetting((int) $user->getId(), 'previous_login', (string) $oldLastLogin, 'string');
        }

        $user->setDateLastLogin(Core::getCurrentDate());
        $userDao->updateObject($user);

        return $user;
    }

    /**
     * Mark the user as logged out in the current session.
     * @return bool
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

        /** @var SessionDAO $sessionDao */
        $sessionDao = DAORegistry::getDAO('SessionDAO');
        $sessionDao->updateObject($session);

        return true;
    }

    /**
     * Redirect to the login page.
     * @param string|null $message optional message to display on the login page
     * @return void
     */
    public static function redirectLogin($message = null) {
        $args = [];
        if (isset($_SERVER['REQUEST_URI'])) {
            $args['source'] = (string) $_SERVER['REQUEST_URI'];
        }
        if ($message !== null) {
            $args['loginMessage'] = (string) $message;
        }
        
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $request->redirect(null, 'login', null, null, $args);
    }

    /**
     * Check if a user's credentials are valid.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public static function checkCredentials($username, $password) {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = null;
        
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $userDao->getUserByEmail($username);
        } else {
            $user = $userDao->getByUsername($username, false);
        }

        if ($user !== null) {
            $username = (string) $user->getUsername();
            $auth = null;
            $authId = $user->getAuthId();

            if ($authId !== null && $authId !== 0) {
                /** @var AuthSourceDAO $authDao */
                $authDao = DAORegistry::getDAO('AuthSourceDAO');
                $auth = $authDao->getPlugin((int) $authId);
            }

            if ($auth !== null) {
                return $auth->authenticate($username, $password);
            } else {
                $hashing = new Hashing();
                $storedHash = (string) $user->getPassword();
                
                // Check Modern
                if ($hashing->isValid($password, $storedHash)) {
                    return true;
                }
                
                // Check Legacy
                $legacyHash = Validation::encryptCredentials($username, $password, false, true);
                if ($legacyHash === $storedHash) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if a user is authorized.
     * @param int $roleId
     * @param int $journalId
     * @return bool
     */
    public static function isAuthorized($roleId, $journalId = 0) {
        if (!Validation::isLoggedIn()) {
            return false;
        }

        if ($journalId === -1) {
            // Lumera Singleton Fallback
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();
            $journalId = $journal !== null ? (int) $journal->getId() : 0;
        }

        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $user = $session->getUser();

        if ($user === null) {
            return false;
        }

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        return $roleDao->userHasRole((int) $journalId, (int) $user->getId(), (int) $roleId);
    }

    /**
     * Encrypt user passwords for database storage.
     * @param string $username
     * @param string $password
     * @param string|bool $encryption optional encryption method for legacy (md5 or sha1)
     * @param bool $legacy
     * @return string encrypted password
     */
    public static function encryptCredentials($username, $password, $encryption = false, $legacy = false) {
        if ($legacy === true) {
            if ($encryption === false) {
                $encryption = (string) Config::getVar('security', 'encryption', 'md5');
            }
            $salt = (string) Config::getVar('security', 'salt');
            $valueToEncrypt = (string) $password . $salt; 
            
            if ($encryption === 'sha1') {
                return sha1($valueToEncrypt);
            }
            return md5($valueToEncrypt);
        } 
        
        $hashing = new Hashing();
        return $hashing->getHash((string) $password);
    }

    /**
     * Generate a random password.
     * @param int $length
     * @return string
     */
    public static function generatePassword($length = 8) {
        return substr(str_shuffle('abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, (int) $length);
    }

    /**
     * Generate a hash value to use for confirmation to reset a password.
     * @param int $userId
     * @param int|null $expiry
     * @return string|false
     */
    public static function generatePasswordResetHash($userId, $expiry = null) {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getById((int) $userId);
        
        if ($user === null) {
            return false;
        }

        $salt = (string) Config::getVar('security', 'salt');
        if ($expiry === null) {
            $expires = (int) Config::getVar('security', 'reset_seconds', 7200);
            $expiry = time() + $expires;
        }

        $data = (string) $user->getUsername() . (string) $user->getPassword() . (string) $user->getDateLastLogin() . (int) $expiry;

        if (function_exists('hash_hmac')) {
            return hash_hmac('sha256', $data, $salt) . ':' . (int) $expiry;
        }
        return md5($data . $salt) . ':' . (int) $expiry;
    }

    /**
     * Check if provided password reset hash is valid.
     * @param int $userId
     * @param string $hash
     * @return bool
     */
    public static function verifyPasswordResetHash($userId, $hash) {
        $parts = explode(':', (string) $hash . ':');
        $expiry = isset($parts[1]) ? $parts[1] : null;
        
        if ($expiry === null || $expiry === '' || (int) $expiry < time()) {
            return false;
        }
        return $hash === Validation::generatePasswordResetHash((int) $userId, (int) $expiry);
    }

    /**
     * Suggest a username.
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    public static function suggestUsername($firstName, $lastName) {
        $initial = PKPString::substr((string) $firstName, 0, 1);
        $suggestion = PKPString::regexp_replace('/[^a-zA-Z0-9_-]/', '', PKPString::strtolower((string) $initial . (string) $lastName));
        
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $i = '';
        while ($userDao->userExistsByUsername($suggestion . $i)) {
            $i++;
        }
        return $suggestion . $i;
    }

    /**
     * Check if logged in.
     * @return bool
     */
    public static function isLoggedIn() {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $userId = $session->getUserId();
        return $userId !== null && $userId !== '' && $userId !== 0;
    }

    // --- Shortcuts for Role Checks ---

    /**
     * Shortcut for checking authorization as site admin.
     * @return bool
     */
    public static function isSiteAdmin() {
        return Validation::isAuthorized(ROLE_ID_SITE_ADMIN);
    }

    /**
     * Shortcut for checking authorization as journal manager.
     * @param int $journalId
     * @return bool
     */
    public static function isJournalManager($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_JOURNAL_MANAGER, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as editor.
     * @param int $journalId
     * @return bool
     */
    public static function isEditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_EDITOR, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as section editor.
     * @param int $journalId
     * @return bool
     */
    public static function isSectionEditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_SECTION_EDITOR, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as layout editor.
     * @param int $journalId
     * @return bool
     */
    public static function isLayoutEditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_LAYOUT_EDITOR, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as reviewer.
     * @param int $journalId
     * @return bool
     */
    public static function isReviewer($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_REVIEWER, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as copyeditor.
     * @param int $journalId
     * @return bool
     */
    public static function isCopyeditor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_COPYEDITOR, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as proofreader.
     * @param int $journalId
     * @return bool
     */
    public static function isProofreader($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_PROOFREADER, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as author.
     * @param int $journalId
     * @return bool
     */
    public static function isAuthor($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_AUTHOR, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as reader.
     * @param int $journalId
     * @return bool
     */
    public static function isReader($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_READER, (int) $journalId);
    }

    /**
     * Shortcut for checking authorization as subscription manager.
     * @param int $journalId
     * @return bool
     */
    public static function isSubscriptionManager($journalId = -1) {
        return Validation::isAuthorized(ROLE_ID_SUBSCRIPTION_MANAGER, (int) $journalId);
    }

    /**
     * Check whether a user is allowed to administer another user.
     * @param int $journalId
     * @param int $userId
     * @return bool
     */
    public static function canAdminister($journalId, $userId) {
        if (Validation::isSiteAdmin()) {
            return true;
        }
        if (!Validation::isJournalManager((int) $journalId)) {
            return false;
        }

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDao->getRolesByUserId((int) $userId);
        
        foreach ($roles as $role) {
            if ($role->getRoleId() === ROLE_ID_SITE_ADMIN) {
                return false;
            }
            if (
                $role->getJournalId() !== (int) $journalId &&
                !Validation::isJournalManager((int) $role->getJournalId())
            ) {
                return false;
            }
        }
        return true;
    }

}
?>