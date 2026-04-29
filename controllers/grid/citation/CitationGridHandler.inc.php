<?php
declare(strict_types=1);

/**
 * @file controllers/grid/citation/CitationGridHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CitationGridHandler
 * @ingroup controllers_grid_citation
 *
 * @brief Handle OJS specific parts of citation grid requests.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.citation.PKPCitationGridHandler');

// import validation classes
import('classes.handler.validation.HandlerValidatorJournal');
import('lib.pkp.classes.handler.validation.HandlerValidatorRoles');

class CitationGridHandler extends PKPCitationGridHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addRoleAssignment(
            [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR],
            [
                'fetchGrid', 'addCitation', 'editCitation', 'updateRawCitation',
                'checkCitation', 'updateCitation', 'deleteCitation', 'exportCitations',
                'fetchCitationFormErrorsAndComparison', 'sendAuthorQuery'
            ]
        );
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CitationGridHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
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
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments) {
        // Make sure the user can edit the submission in the request.
        import('classes.security.authorization.OjsSubmissionAccessPolicy');
        $this->addPolicy(new OjsSubmissionAccessPolicy($request, $args, $roleAssignments, 'assocId'));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * @see PKPHandler::initialize()
     * @param PKPRequest $request
     * @param array|null $args
     */
    public function initialize($request, $args = null) {
        // Associate the citation editor with the authorized article.
        $this->setAssocType(ASSOC_TYPE_ARTICLE);
        $article = $this->getAuthorizedContextObject(ASSOC_TYPE_ARTICLE);
        
        // [WIZDAM] Use instanceof for cleaner type checking
        if (!$article instanceof Article) {
            fatalError('Authorized context object is not an instance of Article!');
        }
        
        $this->setAssocObject($article);

        parent::initialize($request, $args);
    }

    //
    // Override methods from PKPCitationGridHandler
    //

    /**
     * @see PKPCitationGridHandler::exportCitations()
     * @param array $args
     * @param PKPRequest $request
     */
    public function exportCitations($args, $request) {
        $dispatcher = $this->getDispatcher();
        $articleMetadataUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'editor', 'viewMetadata', $this->getAssocId());
        
        $noCitationsFoundMessage = __("submission.citations.editor.pleaseImportCitationsFirst", ['articleMetadataUrl' => $articleMetadataUrl]);
        
        return parent::exportCitations($args, $request, $noCitationsFoundMessage);
    }
}

?>