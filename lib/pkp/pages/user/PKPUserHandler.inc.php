<?php
declare(strict_types=1);

/**
 * @file pages/user/PKPUserHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPUserHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user functions.
 *
 * [WIZDAM EDITION] PHP 8.1+ Compatibility, Strict Types, Security Hardening
 */

import('classes.handler.Handler');

class PKPUserHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility for Legacy Calls
     */
    public function PKPUserHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Deprecated constructor called. Use __construct().", E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Get keywords for reviewer interests autocomplete.
     * @param array $args
     * @param PKPRequest|null $request
     * @return string Serialized JSON object
     */
    public function getInterests($args, $request = null) {
        // [Wizdam] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Get the input text used to filter on
        $filter = (string) $request->getUserVar('term');
        
        // [SECURITY FIX] Sanitasi string (Fix variabel undefined $term -> $filter)
        $filter = trim($filter);

        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();

        $interests = $interestManager->getAllInterests($filter);

        import('lib.pkp.classes.core.JSONMessage');
        $json = new JSONMessage(true, $interests);
        
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Persist the status for a user's preference to see inline help.
     * @param array $args
     * @param PKPRequest $request
     * @return string Serialized JSON object
     */
    public function toggleHelp($args, $request) {
        $user = $request->getUser();

        if ($user) {
            $user->setInlineHelp($user->getInlineHelp() ? 0 : 1);

            $userDao = DAORegistry::getDAO('UserDAO');
            $userDao->updateObject($user);
        }

        import('lib.pkp.classes.core.JSONMessage');
        $json = new JSONMessage(true);
        
        header('Content-Type: application/json');
        return $json->getString();
    }
}
?>