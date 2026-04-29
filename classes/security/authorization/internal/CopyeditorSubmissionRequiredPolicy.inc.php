<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/internal/CopyeditorSubmissionRequiredPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorSubmissionRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the request contains a valid
 * copyeditor submission.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.security.authorization.DataObjectRequiredPolicy');

class CopyeditorSubmissionRequiredPolicy extends DataObjectRequiredPolicy {
    
    /**
     * Constructor
     * @param $request PKPRequest
     */
    public function __construct($request, $args, $submissionParameterName = 'articleId') {
        parent::__construct($request, $args, $submissionParameterName, 'user.authorization.invalidCopyditorSubmission');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CopyeditorSubmissionRequiredPolicy($request, $args, $submissionParameterName = 'articleId') {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CopyeditorSubmissionRequiredPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $args, $submissionParameterName);
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * @see AuthorizationPolicy::effect()
     */
    public function effect() {
        // Get the request.
        $request = $this->getRequest();

        // Get the submission id.
        $submissionId = $this->getDataObjectId();
        if ($submissionId === false) return AUTHORIZATION_DENY;

        // Get the user
        $user = $request->getUser();
        // [MODERNISASI] is_a -> instanceof
        if (!($user instanceof PKPUser)) return AUTHORIZATION_DENY;

        // Validate the article id.
        $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
        $copyeditorSubmission = $copyeditorSubmissionDao->getCopyeditorSubmission($submissionId, $user->getId());
        // [MODERNISASI] is_a -> instanceof
        if (!($copyeditorSubmission instanceof CopyeditorSubmission)) return AUTHORIZATION_DENY;

        // Check whether the article is actually part of the journal
        // in the context.
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        
        // [MODERNISASI] is_a -> instanceof
        if (!($journal instanceof Journal)) return AUTHORIZATION_DENY;
        
        if ($copyeditorSubmission->getJournalId() != $journal->getId()) return AUTHORIZATION_DENY;

        // Save the copyeditor submission to the authorization context.
        $this->addAuthorizedContextObject(ASSOC_TYPE_ARTICLE, $copyeditorSubmission);
        return AUTHORIZATION_PERMIT;
    }
}

?>