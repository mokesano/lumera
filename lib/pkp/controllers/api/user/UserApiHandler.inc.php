<?php
declare(strict_types=1);

/**
 * @defgroup controllers_api_user
 */

/**
 * @file controllers/api/user/UserApiHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserApiHandler
 * @ingroup controllers_api_user
 *
 * @brief Class defining the headless AJAX API for backend user manipulation.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

// import the base Handler
import('lib.pkp.classes.handler.PKPHandler');

// import JSON class for API responses
import('lib.pkp.classes.core.JSONMessage');

class UserApiHandler extends PKPHandler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserApiHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }


    //
    // Implement template methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
        $this->addPolicy(new PKPSiteAccessPolicy(
            $request,
            ['updateUserMessageState'],
            SITE_ACCESS_ALL_ROLES
        ));
        return parent::authorize($request, $args, $roleAssignments);
    }


    //
    // Public handler methods
    //
    /**
     * Update the information whether user messages should be
     * displayed or not.
     * @param array $args
     * @param PKPRequest $request
     * @return string a JSON message
     */
    public function updateUserMessageState($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Exit with a fatal error if request parameters are missing.
        if (!isset($args['setting-name']) || !isset($args['setting-value'])) {
            header('HTTP/1.0 400 Bad Request');
            fatalError('Required request parameter "setting-name" or "setting-value" missing!');
        }

        // Retrieve the user from the session.
        $user = $request->getUser();
        if (!($user instanceof User)) {
            header('HTTP/1.0 401 Unauthorized');
            fatalError('User must be logged in to update settings.');
        }

        // Validate the setting.
        $settingName = $args['setting-name'];
        $settingValue = $args['setting-value'];
        $settingType = $this->_settingType($settingName);

        switch($settingType) {
            case 'bool':
                if ($settingValue !== 'false' && $settingValue !== 'true') {
                    // Exit with a fatal error when the setting value is invalid.
                    header('HTTP/1.0 400 Bad Request');
                    fatalError('Invalid setting value! Must be "true" or "false".');
                }
                $settingValue = ($settingValue === 'true' ? true : false);
                break;

            default:
                // Exit with a fatal error when an unknown setting is found.
                header('HTTP/1.0 400 Bad Request');
                fatalError('Unknown or disallowed setting!');
        }

        // Persist the validated setting.
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        $userSettingsDao->updateSetting($user->getId(), $settingName, $settingValue, $settingType);

        // Return a success message.
        $json = new JSONMessage(true);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Checks the requested setting against a whitelist of
     * settings that can be changed remotely.
     * @param string $settingName
     * @return string|null a string representation of the setting type
     * for further validation if the setting is whitelisted, otherwise
     * null.
     */
    protected function _settingType($settingName) {
        // Settings whitelist.
        static $allowedSettings = [
            'citation-editor-hide-intro' => 'bool',
            'citation-editor-hide-raw-editing-warning' => 'bool'
        ];

        // Identify the setting type.
        if (isset($allowedSettings[$settingName])) {
            return $allowedSettings[$settingName];
        } else {
            return null;
        }
    }
}

?>