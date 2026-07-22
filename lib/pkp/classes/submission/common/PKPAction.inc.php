<?php
declare(strict_types=1);

/**
 * @defgroup submission_common
 */

/**
 * @file classes/submission/common/PKPAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAction
 * @ingroup submission_common
 *
 * @brief Application-independent submission actions.
 */

class PKPAction {
    
    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAction() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                'Class ' . get_class($this) . ' uses deprecated constructor parent::PKPAction(). Please refactor to parent::__construct().', 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Actions.
    //
    /**
     * Edit citations
     * 
     * @param PKPRequest $request
     * @param Submission $submission
     * @return void
     */
    public static function editCitations($request, $submission) {
        $router = $request->getRouter();
        $dispatcher = $request->getDispatcher();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->addStyleSheet($request->getBaseUrl() . '/styles/ojs.css');

        // Add extra java script required for ajax components
        $templateMgr->addJavaScript('lib/pkp/js/functions/modal.js');
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/validate/jquery.validate.min.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/jqueryValidatorI18n.js');
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.splitter.js');

        $citationEditorConfigurationError = null;
        $context = $router->getContext($request);
        if (!$context || !$context->getSetting('metaCitations')) {
            $citationEditorConfigurationError = 'submission.citations.editor.pleaseSetup';
        }

        if (!$citationEditorConfigurationError && $context) {
            /** @var FilterDAO $filterDao */
            $filterDao = DAORegistry::getDAO('FilterDAO');
            $contextId = (int) $context->getId();
            $configuredCitationParsers = $filterDao->getObjectsByGroup(CITATION_PARSER_FILTER_GROUP, $contextId);

            if (empty($configuredCitationParsers)) {
                $citationEditorConfigurationError = 'submission.citations.editor.pleaseAddParserFilter';
            }
        }

        if (!$citationEditorConfigurationError && $context) {
            $outputFilterId = $context->getSetting('metaCitationOutputFilterId');
            if (!is_numeric($outputFilterId) || (int) $outputFilterId <= 0) {
                $citationEditorConfigurationError = 'submission.citations.editor.pleaseConfigureOutputStyle';
            }
        }

        $templateMgr->assign('citationEditorConfigurationError', $citationEditorConfigurationError);

        if ($citationEditorConfigurationError === null) {
            $user = $request->getUser();
            $introductionHide = $user ? (bool) $user->getSetting('citation-editor-hide-intro') : false;
        } else {
            $introductionHide = false;
        }
        $templateMgr->assign('introductionHide', $introductionHide);

        /** @var CitationDAO $citationDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $citations = $citationDao->getObjectsByAssocId(ASSOC_TYPE_ARTICLE, $submission->getId());
        $citationCount = $citations ? $citations->getCount() : 0;
        if ($citationCount > 0) {
            $initialHelpMessage = __('submission.citations.editor.details.pleaseClickOnCitationToStartEditing');
        } else {
            $articleMetadataUrl = $router->url($request, null, null, 'viewMetadata', $submission->getId());
            $initialHelpMessage = __('submission.citations.editor.pleaseImportCitationsFirst', ['articleMetadataUrl' => $articleMetadataUrl]);
        }
        $templateMgr->assign('initialHelpMessage', $initialHelpMessage);

        if ($citationCount > 0) {
            $unprocessedCitations = $citationDao->getObjectsByAssocId(ASSOC_TYPE_ARTICLE, $submission->getId(), 0, CITATION_CHECKED);
            $unprocessedCount = $unprocessedCitations ? $unprocessedCitations->getCount() : 0;
            
            if ($unprocessedCount > 0) {
                $templateMgr->assign('unprocessedCitations', $unprocessedCitations->toArray());
            } else {
                $templateMgr->assign('unprocessedCitations', []);
            }
        } else {
            $templateMgr->assign('unprocessedCitations', []);
        }

        // Add the grid URL.
        $citationGridUrl = $dispatcher->url(
            $request, 
            ROUTE_COMPONENT, 
            null, 
            'grid.citation.CitationGridHandler', 
            'fetchGrid', 
            null, 
            ['assocId' => $submission->getId()]
        );
        $templateMgr->assign('citationGridUrl', $citationGridUrl);

        // Add the export URL.
        $citationExportUrl = $dispatcher->url(
            $request, 
            ROUTE_COMPONENT, 
            null, 
            'grid.citation.CitationGridHandler', 
            'exportCitations', 
            null, 
            ['assocId' => $submission->getId()]
        );
        $templateMgr->assign('citationExportUrl', $citationExportUrl);

        // Add the submission.
        $templateMgr->assign('submission', $submission);
    }

}
?>