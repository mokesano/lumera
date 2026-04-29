<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/OjsJournalAccessPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OjsJournalAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OJS' journal setup components
 * * MODERNIZED FOR WIZDAM FORK
 */

import('classes.security.authorization.internal.JournalPolicy');

class OjsJournalAccessPolicy extends JournalPolicy {
    
    /**
     * Constructor
     * @param $request PKPRequest
     * @param $roleAssignments array
     */
    public function __construct($request, $roleAssignments) {
        parent::__construct($request);

        // On journal level we don't have role-specific conditions
        // so we can simply add all role assignments. It's ok if
        // any of these role conditions permits access.
        $journalRolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
        import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
        foreach($roleAssignments as $role => $operations) {
            $journalRolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($journalRolePolicy);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OjsJournalAccessPolicy($request, $roleAssignments) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::OjsJournalAccessPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $roleAssignments);
    }
}

?>