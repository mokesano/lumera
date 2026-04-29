<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/internal/JournalPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures availability of an OJS journal in
 * the request context
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.security.authorization.PolicySet');

class JournalPolicy extends PolicySet {
    
    /**
     * Constructor
     * @param $request PKPRequest
     */
    public function __construct($request) {
        parent::__construct();

        // Ensure that we have a journal in the context.
        import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
        $this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noJournal'));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalPolicy($request) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::JournalPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request);
    }
}

?>