<?php
declare(strict_types=1);

/**
 * @file classes/plugins/AuthPlugin.inc.php
 *
 * @class AuthPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for authentication plugins.
 */

define('AUTH_PLUGIN_CATEGORY', 'auth');
import('classes.plugins.Plugin');

class AuthPlugin extends Plugin {

    /** @var array settings for this plugin instance */
    public $settings;

    /** @var int|null auth source ID for this plugin instance */
    public $authId;

    /**
     * Modern constructor.
     * @param array $settings
     * @param int|null $authId
     */
    public function __construct($settings = [], $authId = null) {
        parent::__construct();
        $this->settings = $settings;
        $this->authId = $authId;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthPlugin($settings = [], $authId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $this->__construct($settings, $authId);
    }

    /**
     * Return the name of this plugin.
     * Should be overridden by subclass.
     * @return string
     */
    public function getName(): string {
        assert(false);
        return '';
    }

    /**
     * Return the localized name of this plugin.
     * Should be overridden by subclass.
     * @return string
     */
    public function getDisplayName(): string {
        assert(false);
        return '';
    }

    /**
     * Return the localized description of this plugin.
     * Should be overridden by subclass.
     * @return string
     */
    public function getDescription(): string {
        assert(false);
        return '';
    }

    /**
     * Return the path to a template for plugin settings.
     * @return string
     */
    public function getSettingsTemplate(): string {
        return $this->getTemplatePath() . 'settings.tpl';
    }

    /**
     * Update local user profile from the remote source, if enabled.
     * @param object $user User
     * @return bool
     */
    public function doGetUserInfo($user): bool {
        if (isset($this->settings['syncProfiles'])) {
            return $this->getUserInfo($user);
        }
        return false;
    }

    /**
     * Update remote user profile, if enabled.
     * @param object $user User
     * @return bool
     */
    public function doSetUserInfo($user): bool {
        if (isset($this->settings['syncProfiles'])) {
            return $this->setUserInfo($user);
        }
        return false;
    }

    /**
     * Update remote user password.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function doSetUserPassword($username, $password): bool {
        if (isset($this->settings['syncPasswords'])) {
            return $this->setUserPassword($username, $password);
        }
        return false;
    }

    /**
     * Create remote user account.
     * @param object $user User
     * @return bool
     */
    public function doCreateUser($user): bool {
        if (isset($this->settings['createUsers'])) {
            return $this->createUser($user);
        }
        return false;
    }

    /**
     * Returns an instance of the authentication plugin
     * @param array $settings
     * @param int $authId
     * @return AuthPlugin|null
     */
    public function getInstance($settings, $authId) {
        return null;
    }

    /**
     * Authenticate a username and password.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate(string $username, string $password): bool {
        return false;
    }

    /**
     * Check if a username exists.
     * @param string $username
     * @return bool
     */
    public function userExists(string $username): bool {
        return false;
    }

    /**
     * Retrieve user profile information from the remote source.
     * @param object $user User
     * @return bool
     */
    public function getUserInfo($user): bool {
        return false;
    }

    /**
     * Store user profile information on the remote source.
     * @param object $user User
     * @return bool
     */
    public function setUserInfo($user): bool {
        return false;
    }

    /**
     * Change a user's password on the remote source.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function setUserPassword(string $username, string $password): bool {
        return false;
    }

    /**
     * Create a user on the remote source.
     * @param object $user User
     * @return bool
     */
    public function createUser($user): bool {
        return false;
    }

    /**
     * Delete a user from the remote source.
     * @param string $username
     * @return bool
     */
    public function deleteUser(string $username): bool {
        return false;
    }

    /**
     * Return true iff this is a site-wide plugin.
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * Return the management verbs for this plugin.
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return [
            [
                'authSources',
                __('admin.authSources')
            ]
        ];
    }

    /**
     * Management handler.
     * * MODERNIZATION NOTE:
     * Removed pass-by-reference (&) for $message and $messageParams.
     * Use NotificationManager for user feedback instead.
     *
     * @param string $verb
     * @param array $args
     * @param string|null $message DEPRECATED: Do not use. Use NotificationManager.
     * @param array|null $messageParams DEPRECATED: Do not use.
     * @param object|null $plugin
     * @param object|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message = null, $messageParams = null, $plugin = null, $request = null): bool {
        if ($verb === 'authSources') {
            Request::redirect('index', 'admin', 'auth');
            return false;
        }
        return false;
    }
}

?>