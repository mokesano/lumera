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
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.handler.Handler');

class ObjectsForReviewHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectsForReviewHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectsForReviewHandler(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display objects for review public index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request) {
        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Search
        $searchParameters = [
            'searchField', 'searchMatch', 'search'
        ];
        $searchFieldOptions = [
            OFR_FIELD_TITLE => 'plugins.generic.objectsForReview.search.field.title',
            OFR_FIELD_ABSTRACT => 'plugins.generic.objectsForReview.search.field.abstract'
        ];
        
        $searchField = null;
        $searchMatch = null;
        
        // [SECURITY FIX] Amankan 'search' (string teks pencarian) dengan trim()
        $search = trim($request->getUserVar('search') ?? '');
        
        if (!empty($search)) {
            // [SECURITY FIX] Save 'searchField' (string key/field) with trim()
            $searchField = trim($request->getUserVar('searchField') ?? '');
            
            // [SECURITY FIX] Save 'searchMatch' (string key/match type) with trim()
            $searchMatch = trim($request->getUserVar('searchMatch') ?? '');
        }

        // Filter by review object type
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
        $typeOptions = [0 => __('common.all')];
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $allReviewObjectsMetadata = [];

        foreach ($allTypes as $type) {
            $typeId = $type['typeId'];
            $typeOptions[$typeId] = $type['typeName'];
            $typeMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($typeId);
            $allReviewObjectsMetadata[$typeId] = $typeMetadata;
        }
        // [SECURITY FIX] Amankan 'filterType' (string key) dengan trim()
        $filterType = trim($request->getUserVar('filterType') ?? '');

        // Sort
        $sortingOptions = [
            'title' => __('plugins.generic.objectsForReview.objectsForReview.title'),
            'created' => __('plugins.generic.objectsForReview.objectsForReview.dateCreated')
        ];
        
        // [SECURITY FIX] Amankan 'sort' (string key) dengan trim()
        $sort = trim($request->getUserVar('sort') ?? '');
        
        // Protocol 2: Null Coalescing for Sort
        $sort = !empty($sort) ? $sort : 'title';
        
        $sortDirections = [
            SORT_DIRECTION_ASC => __('plugins.generic.objectsForReview.sort.sortDirectionAsc'),
            SORT_DIRECTION_DESC => __('plugins.generic.objectsForReview.sort.sortDirectionDesc')
        ];
        
        // [SECURITY FIX] Amankan 'sortDirection' (string key) dengan trim()
        $sortDirection = trim($request->getUserVar('sortDirection') ?? '');
        $sortDirection = !empty($sortDirection) ? $sortDirection : SORT_DIRECTION_ASC;

        // Get objects for review
        $rangeInfo = Handler::getRangeInfo('objectsForReview');
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        
        // Protocol 6: Paginasi sudah ditangani oleh rangeInfo, aman dari memory leak
        $objectsForReview = $ofrDao->getAllByContextId($journalId, $searchField, $search, $searchMatch, 1, null, $filterType, $rangeInfo, $sort, $sortDirection);

        // If the user is an author get her/his assignments
        $isAuthor = Validation::isAuthor();
        $authorAssignments = [];
        
        if ($isAuthor) {
            $user = $request->getUser();
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $authorAssignments = $ofrAssignmentDao->getObjectIds($user->getId());
        }

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        
        // [SECURITY FIX] Amankan semua parameter pencarian dari XSS dan input kotor
        foreach ($searchParameters as $param) {
            // 1. Ambil input mentah dan bersihkan spasi/karakter kontrol (trim)
            $rawInput = trim($request->getUserVar($param) ?? '');
            
            // 2. Amankan string dari XSS menggunakan htmlspecialchars() sebelum ditampilkan di template
            $sanitizedValue = htmlspecialchars($rawInput, ENT_QUOTES, 'UTF-8');
            
            // 3. Assign nilai yang sudah aman
            $templateMgr->assign($param, $sanitizedValue);
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
        $coverPagePath = $request->getBaseUrl() . '/';
        $coverPagePath .= $publicFileManager->getJournalFilesPath($journalId) . '/';
        $templateMgr->assign('coverPagePath', $coverPagePath);

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        $templateMgr->assign('multipleOptionsTypes', ReviewObjectMetadata::getMultipleOptionsTypes());
        $templateMgr->assign('additionalInformation', $ofrPlugin->getSetting($journalId, 'additionalInformation'));
        $templateMgr->assign('ofrListing', true);
        $templateMgr->display($ofrPlugin->getTemplatePath() . 'objectsForReview.tpl');
    }

    /**
     * Public view object for review details.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewObjectForReview($args, $request) {
        // Ensure the args (object ID) exists
        $objectId = array_shift($args);
        if (!$objectId) {
            $request->redirect(null, 'objectsForReview');
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Ensure the object exists
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);
        
        // Protocol 2: Strict check
        if (!isset($objectForReview)) {
            $request->redirect(null, 'objectsForReview');
        }
        
        // If object is available
        if ($objectForReview->getAvailable()) {
            // Get all metadata for the objects for review
            $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
            $allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
            $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
            $allReviewObjectsMetadata = [];
            
            foreach ($allTypes as $type) {
                $typeId = $type['typeId'];
                $typeMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($typeId);
                $allReviewObjectsMetadata[$typeId] = $typeMetadata;
            }

            // If the user is an author get her/his assignments
            $isAuthor = Validation::isAuthor();
            $authorAssignments = [];
            
            if ($isAuthor) {
                $user = $request->getUser();
                $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
                $authorAssignments = $ofrAssignmentDao->getObjectIds($user->getId());
            }

            $this->setupTemplate($request, true);
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('objectForReview', $objectForReview);
            $templateMgr->assign('allReviewObjectsMetadata', $allReviewObjectsMetadata);

            $templateMgr->assign('isAuthor', $isAuthor);
            $templateMgr->assign('authorAssignments', $authorAssignments);

            // Cover page path
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $coverPagePath = $request->getBaseUrl() . '/';
            $coverPagePath .= $publicFileManager->getJournalFilesPath($journalId) . '/';
            $templateMgr->assign('coverPagePath', $coverPagePath);

            $ofrPlugin = $this->_getObjectsForReviewPlugin();
            $ofrPlugin->import('classes.ReviewObjectMetadata');
            $templateMgr->assign('multipleOptionsTypes', ReviewObjectMetadata::getMultipleOptionsTypes());
            $templateMgr->assign('locale', AppLocale::getLocale());
            $templateMgr->assign('ofrListing', false);
            $templateMgr->assign('ofrTemplatePath', $ofrPlugin->getTemplatePath());
            $templateMgr->display($ofrPlugin->getTemplatePath() . 'objectForReview.tpl');

        } else {
            $request->redirect(null, 'objectsForReview');
        }
    }

    /**
     * Ensure that we have a selected journal, the plugin is enabled,
     * in full mode and the option 'displayListing' is selected
     * @see PKPHandler::authorize()
     */
    public function authorize($request, $args, $roleAssignments) {
        $journal = $request->getJournal();
        if (!isset($journal)) return false;

        $ofrPlugin = $this->_getObjectsForReviewPlugin();

        if (!isset($ofrPlugin)) return false;

        if (!$ofrPlugin->getEnabled()) return false;

        $mode = $ofrPlugin->getSetting($journal->getId(), 'mode');
        if ($mode != OFR_MODE_FULL) return false;

        if (!$ofrPlugin->getSetting($journal->getId(), 'displayListing')) return false;

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param PKPRequest $request
     * @param boolean $subclass set to true if caller is below this handler in the hierarchy
     */
    public function setupTemplate($request, $subclass = false) {
        $templateMgr = TemplateManager::getManager($request);
        if ($subclass) {
            $templateMgr->append(
                'pageHierarchy',
                [
                    $request->url(null, 'objectsForReview'),
                    AppLocale::Translate('plugins.generic.objectsForReview.displayName'),
                    true
                ]
            );
        }
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $ofrPlugin->getStyleSheet());
    }

    //
    // Private helper methods
    //
    /**
     * Get the objectForReview plugin object
     * @return ObjectsForReviewPlugin
     */
    protected function _getObjectsForReviewPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
        return $plugin;
    }
}

?>