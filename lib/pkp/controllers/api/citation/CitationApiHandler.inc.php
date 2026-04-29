<?php
declare(strict_types=1);

/**
 * @defgroup controllers_api_citation
 */

/**
 * @file controllers/api/user/CitationApiHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CitationApiHandler
 * @ingroup controllers_api_user
 *
 * @brief Class defining the headless API for backend citation manipulation.
 * [WIZDAM EDITION] Modernized Citation API Handler.
 */

// import the base Handler
import('lib.pkp.classes.handler.PKPHandler');

class CitationApiHandler extends PKPHandler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CitationApiHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }


    //
    // Implement template methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     * [WIZDAM TRANSITION]: Signature kept loose for parent compatibility.
     */
    public function authorize($request, $args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.PKPProcessAccessPolicy');
        
        // Ensure $request is the correct type before use, although PKPProcessAccessPolicy expects it.
        if (!($request instanceof PKPRequest)) {
            // Log an error or return authorization failure if not a PKPRequest
            // For now, we trust the caller passes a compatible object as per design.
        }

        // [WIZDAM FIX] Using direct object creation (no reference needed for objects)
        $this->addPolicy(new PKPProcessAccessPolicy($request, $args, 'checkAllCitations'));
        
        return parent::authorize($request, $args, $roleAssignments);
    }


    //
    // Public handler methods
    //
    /**
     * Check (parse and lookup) all raw citations
     *
     * NB: This handler method is meant to be called by the parallel
     * processing framework (see ProcessDAO::spawnProcesses()).
     * @param array $args
     * @param PKPRequest $request
     * @return string
     * [WIZDAM TRANSITION]: Signature kept loose for parent compatibility.
     */
    public function checkAllCitations($args, $request): string {
        // This is potentially a long running request. So
        // give us unlimited execution time.
        ini_set('max_execution_time', 0);

        // Get the process id.
        $processId = $args['authToken'] ?? null;
        
        if (empty($processId)) {
             return 'Error: Auth token (process ID) missing.';
        }

        // Run until all citations have been checked.
        /** @var ProcessDAO $processDao */
        $processDao = DAORegistry::getDAO('ProcessDAO');
        
        /** @var CitationDAO $citationDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        
        do {
            // Check that the process lease has not expired.
            $continue = $processDao->canContinue($processId);

            if ($continue) {
                // Check the next citation.
                // $citationDao->checkNextRawCitation expects PKPRequest
                $continue = $citationDao->checkNextRawCitation($request, $processId);
            }
        } while ($continue);

        // Free the process slot.
        $processDao->deleteObjectById($processId);

        // This request returns just a (private) status message.
        return 'Done!';
    }
}
?>