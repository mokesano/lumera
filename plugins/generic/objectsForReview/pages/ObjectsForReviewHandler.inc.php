<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/pages/ObjectsForReviewHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewHandler
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Handle requests for public object for review functions.
 */

import('classes.handler.Handler');

class ObjectsForReviewHandler extends Handler {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ObjectsForReviewHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display objects for review public index page.
     * @param array|null $args
     * @param mixed $request
     * @return void
     */
    public function index($args = null, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $searchParameters = ['searchField', 'searchMatch', 'search'];
        $searchFieldOptions = [
            OFR_FIELD_TITLE => 'plugins.generic.objectsForReview.search.field.title',
            OFR_FIELD_ABSTRACT => 'plugins.generic.objectsForReview.search.field.abstract'
        ];
        
        $search = (string) $request->getUserVar('search');
        $searchField = null;
        $searchMatch = null;
        
        if ($search !== '') {
            $searchFieldInput = (string) $request->getUserVar('searchField');
            if (in_array($searchFieldInput, [OFR_FIELD_TITLE, OFR_FIELD_ABSTRACT], true)) {
                $searchField = $searchFieldInput;
            }
            
            $searchMatchInput = (string) $request->getUserVar('searchMatch');
            if (in_array($searchMatchInput, ['is', 'contains', 'startsWith'], true)) {
                $searchMatch = $searchMatchInput;
            } else {
                $searchMatch = 'contains';
            }
        }

        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
        $typeOptions = [0 => __('common.all')];
        
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $allReviewObjectsMetadata = [];

        foreach ($allTypes as $type) {
            $typeId = (int) $type['typeId'];
            $typeOptions[$typeId] = (string) $type['typeName'];
            $typeMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($typeId);
            $allReviewObjectsMetadata[$typeId] = $typeMetadata;
        }
        
        $filterTypeInput = $request->getUserVar('filterType');
        $filterType = $filterTypeInput !== null && $filterTypeInput !== '' ? (int) $filterTypeInput : null;

        $sortingOptions = [
            'title' => __('plugins.generic.objectsForReview.objectsForReview.title'),
            'created' => __('plugins.generic.objectsForReview.objectsForReview.dateCreated')
        ];
        
        $sortInput = (string) $request->getUserVar('sort');
        $sort = in_array($sortInput, ['title', 'created'], true) ? $sortInput : 'title';
        
        $sortDirections = [
            SORT_DIRECTION_ASC => __('plugins.generic.objectsForReview.sort.sortDirectionAsc'),
            SORT_DIRECTION_DESC => __('plugins.generic.objectsForReview.sort.sortDirectionDesc')
        ];
        
        $sortDirectionInput = (string) $request->getUserVar('sortDirection');
        $sortDirection = ($sortDirectionInput === SORT_DIRECTION_DESC) ? SORT_DIRECTION_DESC : SORT_DIRECTION_ASC;

        $rangeInfo = Handler::getRangeInfo('objectsForReview');
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        
        $objectsForReview = $ofrDao->getAllByContextId($journalId, $searchField, $search, $searchMatch, 1, null, $filterType, $rangeInfo, $sort, $sortDirection);

        $isAuthor = Validation::isAuthor();
        $authorAssignments = [];
        
        if ($isAuthor) {
            $user = $request->getUser();
            if ($user) {
                /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
                $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
                $authorAssignments = $ofrAssignmentDao->getObjectIds((int) $user->getId());
            }
        }

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        
        foreach ($searchParameters as $param) {
            $rawInput = (string) $request->getUserVar($param);
            $templateMgr->assign($param, htmlspecialchars($rawInput, ENT_QUOTES, 'UTF-8'));
        }
        
        $templateMgr->assign('searchFieldOptions', $searchFieldOptions);
        $templateMgr->assign('typeOptions', $typeOptions);
        $templateMgr->assign('filterType', $filterType);
        $templateMgr->assign('sortingOptions', $sortingOptions);
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirections', $sortDirections);
        $templateMgr->assign('sortDirection', $sortDirection);
        $templateMgr->assign('objectsForReview', $objectsForReview);
        $templateMgr->assign('allReviewObjectsMetadata', $allReviewObjectsMetadata);
        $templateMgr->assign('isAuthor', $isAuthor);
        $templateMgr->assign('authorAssignments', $authorAssignments);

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/';
        $templateMgr->assign('coverPagePath', $coverPagePath);

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.ReviewObjectMetadata');
            
            // [WIZDAM FIX] Instantiate to call non-static methods safely, resolving linter error
            $reviewObjectMetadata = new ReviewObjectMetadata();
            $templateMgr->assign('multipleOptionsTypes', $reviewObjectMetadata->getMultipleOptionsTypes());
            $templateMgr->assign('additionalInformation', $ofrPlugin->getSetting($journalId, 'additionalInformation'));
            $templateMgr->assign('ofrListing', true);
            $templateMgr->display($ofrPlugin->getTemplatePath() . 'objectsForReview.tpl');
        }
    }

    /**
     * Public view object for review details.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function viewObjectForReview($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        if ($objectId === null) {
            $request->redirect(null, 'objectsForReview');
        }

        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);
        
        if ($objectForReview === null || (int) $objectForReview->getAvailable() !== 1) {
            $request->redirect(null, 'objectsForReview');
        }
        
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
        
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $allReviewObjectsMetadata = [];
        
        foreach ($allTypes as $type) {
            $typeId = (int) $type['typeId'];
            $typeMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($typeId);
            $allReviewObjectsMetadata[$typeId] = $typeMetadata;
        }

        $isAuthor = Validation::isAuthor();
        $authorAssignments = [];
        
        if ($isAuthor) {
            $user = $request->getUser();
            if ($user) {
                /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
                $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
                $authorAssignments = $ofrAssignmentDao->getObjectIds((int) $user->getId());
            }
        }

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('objectForReview', $objectForReview);
        $templateMgr->assign('allReviewObjectsMetadata', $allReviewObjectsMetadata);
        $templateMgr->assign('isAuthor', $isAuthor);
        $templateMgr->assign('authorAssignments', $authorAssignments);

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/';
        $templateMgr->assign('coverPagePath', $coverPagePath);

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.ReviewObjectMetadata');
            
            // [WIZDAM FIX] Instantiate to call non-static methods safely, resolving linter error
            $reviewObjectMetadata = new ReviewObjectMetadata();
            $templateMgr->assign('multipleOptionsTypes', $reviewObjectMetadata->getMultipleOptionsTypes());
            $templateMgr->assign('locale', AppLocale::getLocale());
            $templateMgr->assign('ofrListing', false);
            $templateMgr->assign('ofrTemplatePath', $ofrPlugin->getTemplatePath());
            $templateMgr->display($ofrPlugin->getTemplatePath() . 'objectForReview.tpl');
        }
    }

    /**
     * Ensure that we have a selected journal, the plugin is enabled,
     * in full mode and the option 'displayListing' is selected.
     * @see PKPHandler::authorize()
     * @param mixed $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            return false;
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin || !$ofrPlugin->getEnabled()) {
            return false;
        }

        $mode = (int) $ofrPlugin->getSetting((int) $journal->getId(), 'mode');
        if ($mode !== OFR_MODE_FULL) {
            return false;
        }

        if (!$ofrPlugin->getSetting((int) $journal->getId(), 'displayListing')) {
            return false;
        }

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param mixed $request
     * @param bool $subclass
     * @return void
     */
    public function setupTemplate($request = null, $subclass = false) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $templateMgr = TemplateManager::getManager($request);
        if ($subclass) {
            $templateMgr->append(
                'pageHierarchy',
                [
                    $request->url(null, 'objectsForReview'),
                    __('plugins.generic.objectsForReview.displayName'),
                    true
                ]
            );
        }
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $ofrPlugin->getStyleSheet());
        }
    }

    //
    // Private helper methods
    //

    /**
     * Get the objectForReview plugin object.
     * @return ObjectsForReviewPlugin|null
     */
    protected function _getObjectsForReviewPlugin() {
        return PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
    }

}
?>