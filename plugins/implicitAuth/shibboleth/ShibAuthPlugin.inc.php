<?php
declare(strict_types=1);

/**
 * @file plugins/generic/implicitAuth/shibboleth/ShibAuthPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ShibAuthPlugin
 * @ingroup plugins_implicitAuth_shibboleth
 *
 * @brief Shibboleth implicit authentication plugin.
 */

import('classes.plugins.ImplicitAuthPlugin');

class ShibAuthPlugin extends ImplicitAuthPlugin {

    /**
     * Register the plugin.
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        HookRegistry::register('ImplicitAuthPlugin::implicitAuth', [$this, 'implicitAuth']);

        $success = parent::register($category, $path);
        $this->addLocaleData();
        
        return $success;
    }

    /**
     * Get the plugin name.
     * @return string
     */
    public function getName(): string {
        return 'ShibAuthPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.implicitAuth.shibboleth.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.implicitAuth.shibboleth.description');
    }

    /**
     * Check if this is a site-wide plugin.
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * Authenticate user via Shibboleth headers.
     * @return bool
     */
    public function implicitAuth() {
        $arguments = func_get_args();
        $args = $arguments[1] ?? [];

        $uinHeader = Config::getVar('security', 'implicit_auth_header_uin');
        if (empty($uinHeader)) {
            die('Implicit Auth enabled in config file - but implicit_auth_header_uin not defined.');
        }

        if (!isset($_SERVER[$uinHeader])) {
            syslog(LOG_ERR, 'Implicit Auth: expected header variables not found.');
            Validation::logout();
            Validation::redirectLogin();
            exit;
        }

        $uid = $_SERVER[$uinHeader];
        if (empty($uid)) {
            Validation::logout();
            Validation::redirectLogin();
            exit;
        }

        $emailHeader = Config::getVar('security', 'implicit_auth_header_email');
        if (empty($emailHeader)) {
            die('Implicit Auth enabled in config file - but implicit_auth_header_email not defined.');
        }

        $email = $_SERVER[$emailHeader] ?? '';

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getUserByAuthStr($uid, true);
        
        if ($user) {
            self::implicitAuthAdmin($user->getId(), $user->getAuthStr());
            if (is_array($args)) {
                $args[0] = $user; // Note: Won't pass by reference due to func_get_args()
            }
            return true;
        }

        $user = $userDao->getUserByEmail($email);
        if ($user) {
            if ($user->getAuthStr() !== '') {
                die('Implicit Auth: New email with existing UID');
            }
            $user->setAuthStr($uid);
            $userDao->updateObject($user);
            
            self::implicitAuthAdmin($user->getId(), $user->getAuthStr());
            if (is_array($args)) {
                $args[0] = $user;
            }
            return true;
        }

        $user = $this->registerUserFromShib();
        if ($user) {
            self::implicitAuthAdmin($user->getId(), $user->getAuthStr());
            if (is_array($args)) {
                $args[0] = $user;
            }
        }

        return true;
    }

    /**
     * Register a new user from Shibboleth headers.
     * @return User|false
     */
    public function registerUserFromShib() {
        $uinHeader = Config::getVar('security', 'implicit_auth_header_uin');
        $firstNameHeader = Config::getVar('security', 'implicit_auth_header_first_name');
        $lastNameHeader = Config::getVar('security', 'implicit_auth_header_last_name');
        $emailHeader = Config::getVar('security', 'implicit_auth_header_email');
        $phoneHeader = Config::getVar('security', 'implicit_auth_header_phone');
        $mailingAddressHeader = Config::getVar('security', 'implicit_auth_header_mailing_address');

        $user = new User();
        $user->setAuthStr($_SERVER[$uinHeader] ?? '');
        $user->setUsername($_SERVER[$emailHeader] ?? '');
        $user->setFirstName($_SERVER[$firstNameHeader] ?? '');
        $user->setLastName($_SERVER[$lastNameHeader] ?? '');
        $user->setEmail($_SERVER[$emailHeader] ?? '');
        $user->setPhone($_SERVER[$phoneHeader] ?? '');
        $user->setMailingAddress($_SERVER[$mailingAddressHeader] ?? '');
        $user->setDateRegistered(Core::getCurrentDate());

        $user->setPassword(Validation::encryptCredentials(Validation::generatePassword(40), Validation::generatePassword(40)));

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $userDao->insertUser($user);

        if (!$user->getId()) {
            return false;
        }

        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $session->setSessionVar('username', $user->getUsername());

        return $user;
    }

    /**
     * Synchronize user admin role based on config whitelist.
     * @param int $userId
     * @param string $authStr
     * @return void
     */
    public static function implicitAuthAdmin($userId, $authStr) {
        $adminListStr = Config::getVar('security', 'implicit_auth_admin_list');
        $adminList = $adminListStr ? explode(' ', $adminListStr) : [];
        $isAdmin = in_array($authStr, $adminList, true);

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        
        if ($isAdmin) {
            if (!$roleDao->userHasRole(0, $userId, ROLE_ID_SITE_ADMIN)) {
                $role = new Role();
                $role->setJournalId(0);
                $role->setUserId($userId);
                $role->setRoleId(ROLE_ID_SITE_ADMIN);
                $roleDao->insertRole($role);
            }
        } else {
            $roleDao->deleteRoleByUserId($userId, 0, ROLE_ID_SITE_ADMIN);
        }
    }

}
?>