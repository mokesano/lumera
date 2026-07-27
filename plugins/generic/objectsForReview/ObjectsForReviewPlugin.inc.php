<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/ObjectsForReviewPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewPlugin
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Object for review plugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

define('OFR_MODE_FULL', 0x01);
define('OFR_MODE_METADATA', 0x02);

define('NOTIFICATION_TYPE_OFR_PLUGIN_BASE', NOTIFICATION_TYPE_PLUGIN_BASE + 0x1000000);
define('NOTIFICATION_TYPE_OFR_OT_INSTALLED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000001);
define('NOTIFICATION_TYPE_OFR_OT_CREATED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000002);
define('NOTIFICATION_TYPE_OFR_OT_UPDATED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000003);
define('NOTIFICATION_TYPE_OFR_OT_ACTIVATED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000004);
define('NOTIFICATION_TYPE_OFR_OT_DEACTIVATED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000005);
define('NOTIFICATION_TYPE_OFR_OT_DELETED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000006);
define('NOTIFICATION_TYPE_OFR_CREATED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000007);
define('NOTIFICATION_TYPE_OFR_UPDATED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000008);
define('NOTIFICATION_TYPE_OFR_DELETED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000009);
define('NOTIFICATION_TYPE_OFR_REQUESTED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x000000A);
define('NOTIFICATION_TYPE_OFR_AUTHOR_ASSIGNED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x000000B);
define('NOTIFICATION_TYPE_OFR_AUTHOR_DENIED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x000000C);
define('NOTIFICATION_TYPE_OFR_AUTHOR_MAILED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x000000D);
define('NOTIFICATION_TYPE_OFR_AUTHOR_REMOVED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x000000E);
define('NOTIFICATION_TYPE_OFR_SUBMISSION_ASSIGNED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x000000F);
define('NOTIFICATION_TYPE_OFR_SETTINGS_SAVED', NOTIFICATION_TYPE_OFR_PLUGIN_BASE + 0x0000010);


class ObjectsForReviewPlugin extends GenericPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Register the plugin.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();

        if ($success && $this->getEnabled()) {
            $this->registerDAOs();

            HookRegistry::register('JournalDAO::deleteJournalById', [$this, 'deleteJournalById']);
            HookRegistry::register('Templates::Editor::Index::AdditionalItems', [$this, 'displayLink']);
            HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);
            HookRegistry::register('TinyMCEPlugin::getEnableFields', [$this, 'enableTinyMCE']);
            HookRegistry::register('NotificationManager::getNotificationContents', [$this, 'callbackNotificationContents']);
            HookRegistry::register('UserAction::mergeUsers', [$this, 'mergeObjectsForReviewAuthors']);

            $request = Application::get()->getRequest();
            $router = $request->getRouter();
            $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
            
            if ($journal) {
                $availableLocales = $journal->getSupportedLocaleNames();
                foreach ($availableLocales as $locale => $localeName) {
                    $localePath = $this->getPluginPath() . '/locale/' . $locale . '/locale.xml';
                    AppLocale::registerLocaleFile($locale, $localePath, true);
                }

                $mode = (int) $this->getSetting((int) $journal->getId(), 'mode');

                if ($mode === OFR_MODE_FULL) {
                    HookRegistry::register('Templates::Common::Header::Navbar::CurrentJournal', [$this, 'displayLink']);
                    HookRegistry::register('Templates::Author::Index::AdditionalItems', [$this, 'displayLink']);
                    HookRegistry::register('Author::SubmitHandler::saveSubmit', [$this, 'saveSubmitHandler']);
                    HookRegistry::register('Templates::Author::Submit::Step5::AdditionalItems', [$this, 'displayAuthorObjectsForReview']);
                }

                if ((bool) $this->getSetting((int) $journal->getId(), 'displayAbstract')) {
                    HookRegistry::register('TemplateManager::display', [$this, 'handleTemplateDisplay']);
                    HookRegistry::register('Templates::Article::MoreInfo', [$this, 'displayAbstract']);
                }
            }
        }
        return $success;
    }

    /**
     * Get the display name for this plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.objectsForReview.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.objectsForReview.description');
    }

    /**
     * Get the name of the schema file to install the plugin's database tables.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/xml/schema.xml';
    }

    /**
     * Get the name of the file to install the plugin's email templates.
     * @see PKPPlugin::getInstallEmailTemplatesFile()
     * @return string|null
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return $this->getPluginPath() . '/xml/emailTemplates.xml';
    }

    /**
     * Get the name of the locale file to install the plugin's email template data.
     * @see PKPPlugin::getInstallEmailTemplateDataFile()
     * @return string|null
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return $this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml';
    }

    /**
     * Get the template path for this plugin.
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Get the handler path for this plugin.
     * @return string
     */
    public function getHandlerPath(): string {
        return $this->getPluginPath() . '/pages/';
    }

    /**
     * Get the stylesheet for this plugin.
     * @return string
     */
    public function getStyleSheet(): string {
        return $this->getPluginPath() . '/styles/objectsForReview.css';
    }

    /**
     * Instantiate and register the DAOs.
     * @return void
     */
    public function registerDAOs() {
        $this->import('classes.ReviewObjectTypeDAO');
        $this->import('classes.ReviewObjectMetadataDAO');
        $this->import('classes.ObjectForReviewPersonDAO');
        $this->import('classes.ObjectForReviewDAO');
        $this->import('classes.ObjectForReviewSettingsDAO');
        $this->import('classes.ObjectForReviewAssignmentDAO');

        DAORegistry::registerDAO('ReviewObjectTypeDAO', new ReviewObjectTypeDAO($this->getName()));
        DAORegistry::registerDAO('ReviewObjectMetadataDAO', new ReviewObjectMetadataDAO($this->getName()));
        DAORegistry::registerDAO('ObjectForReviewPersonDAO', new ObjectForReviewPersonDAO($this->getName()));
        DAORegistry::registerDAO('ObjectForReviewDAO', new ObjectForReviewDAO($this->getName()));
        DAORegistry::registerDAO('ObjectForReviewSettingsDAO', new ObjectForReviewSettingsDAO($this->getName()));
        DAORegistry::registerDAO('ObjectForReviewAssignmentDAO', new ObjectForReviewAssignmentDAO($this->getName()));
    }

    //
    // Application level hook implementations.
    //
    /**
     * Hook registry function to load editor, author and public handlers.
     * @see PKPPageRouter::route()
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function callbackLoadHandler($hookName, $params) {
        $page = $params[0] ?? '';
        $op = $params[1] ?? '';

        if ($page === 'editor') {
            if ($op !== '') {
                $reviewObjectTypesEditorPages = $this->_getReviewObjectTypesEditorPages();
                $objectsForReviewEditorPages = $this->_getObjectsForReviewEditorPages();
                if (in_array($op, $reviewObjectTypesEditorPages, true)) {
                    define('HANDLER_CLASS', 'ReviewObjectTypesEditorHandler');
                    define('OBJECTS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_CORE_USER, LOCALE_COMPONENT_APP_EDITOR);
                    $params[2] = $this->getHandlerPath() . 'ReviewObjectTypesEditorHandler.inc.php';
                } elseif (in_array($op, $objectsForReviewEditorPages, true)) {
                    define('HANDLER_CLASS', 'ObjectsForReviewEditorHandler');
                    define('OBJECTS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_CORE_USER, LOCALE_COMPONENT_APP_EDITOR);
                    $params[2] = $this->getHandlerPath() . 'ObjectsForReviewEditorHandler.inc.php';
                }
            }
        }

        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if ($journal) {
            $mode = (int) $this->getSetting((int) $journal->getId(), 'mode');

            if ($mode === OFR_MODE_FULL) {
                if ($page === 'objectsForReview') {
                    if ($op !== '') {
                        $publicPages = $this->_getObjectsForReviewPublicPages();
                        if (in_array($op, $publicPages, true)) {
                            define('HANDLER_CLASS', 'ObjectsForReviewHandler');
                            define('OBJECTS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                            AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                            $params[2] = $this->getHandlerPath() . 'ObjectsForReviewHandler.inc.php';
                        }
                    }
                } elseif ($page === 'author') {
                    if ($op !== '') {
                        $objectsForReviewAuthorPages = $this->_getObjectsForReviewAuthorPages();
                        if (in_array($op, $objectsForReviewAuthorPages, true)) {
                            define('HANDLER_CLASS', 'ObjectsForReviewAuthorHandler');
                            define('OBJECTS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                            AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_CORE_USER, LOCALE_COMPONENT_APP_AUTHOR);
                            $params[2] = $this->getHandlerPath() . 'ObjectsForReviewAuthorHandler.inc.php';
                        }
                    }
                }
            }
        }
    }

    /**
     * Enable TinyMCE support for object for review text fields.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function enableTinyMCE($hookName, $params) {
        $fields =& $params[1]; // Reference needed for array modification

        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        if (!($router instanceof PKPPageRouter)) {
            return false;
        }

        $page = $router->getRequestedPage($request);
        $op = $router->getRequestedOp($request);

        $reviewObjectTypeId = (int) $request->getUserVar('reviewObjectTypeId');
        
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $textareaReviewObjectMetadataIds = $reviewObjectMetadataDao->getTextareaReviewObjectMetadataIds($reviewObjectTypeId);

        if ($page === 'editor') {
            if ($op === 'createReviewObjectType' || $op === 'editReviewObjectType' || $op === 'updateReviewObjectType') {
                $fields[] = 'description';
            } elseif ($op === 'previewReviewObjectType') {
                $fields[] = 'textarea_metadata_type';
            } elseif ($op === 'createObjectForReview' || $op === 'editObjectForReview' || $op === 'updateObjectForReview') {
                $fields[] = 'notes';
                foreach ($textareaReviewObjectMetadataIds as $metadataId) {
                    $fields[] = "ofrSettings-$metadataId";
                }
            } elseif ($op === 'objectsForReviewSettings') {
                $fields[] = 'additionalInformation';
            }
        }
        return false;
    }

    /**
     * Hook registry function to provide notification messages.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackNotificationContents($hookName, $args) {
        $notification = $args[0];
        $message = $args[1];

        $type = $notification->getType();
        
        switch ($type) {
            case NOTIFICATION_TYPE_OFR_OT_INSTALLED:
                $message = __('plugins.generic.objectsForReview.notification.objectTypeInstalled');
                break;
            case NOTIFICATION_TYPE_OFR_OT_CREATED:
                $message = __('plugins.generic.objectsForReview.notification.objectTypeCreated');
                break;
            case NOTIFICATION_TYPE_OFR_OT_UPDATED:
                $message = __('plugins.generic.objectsForReview.notification.objectTypeUpdated');
                break;
            case NOTIFICATION_TYPE_OFR_OT_ACTIVATED:
                $message = __('plugins.generic.objectsForReview.notification.objectTypeActivated');
                break;
            case NOTIFICATION_TYPE_OFR_OT_DEACTIVATED:
                $message = __('plugins.generic.objectsForReview.notification.objectTypeDeactivated');
                break;
            case NOTIFICATION_TYPE_OFR_OT_DELETED:
                $message = __('plugins.generic.objectsForReview.notification.objectTypeDeleted');
                break;
            case NOTIFICATION_TYPE_OFR_CREATED:
                $message = __('plugins.generic.objectsForReview.notification.ofrCreated');
                break;
            case NOTIFICATION_TYPE_OFR_UPDATED:
                $message = __('plugins.generic.objectsForReview.notification.ofrUpdated');
                break;
            case NOTIFICATION_TYPE_OFR_DELETED:
                $message = __('plugins.generic.objectsForReview.notification.ofrDeleted');
                break;
            case NOTIFICATION_TYPE_OFR_REQUESTED:
                $message = __('plugins.generic.objectsForReview.notification.ofrRequested');
                break;
            case NOTIFICATION_TYPE_OFR_AUTHOR_ASSIGNED:
                $message = __('plugins.generic.objectsForReview.notification.ofrAuthorAssigned');
                break;
            case NOTIFICATION_TYPE_OFR_AUTHOR_DENIED:
                $message = __('plugins.generic.objectsForReview.notification.ofrAuthorDenied');
                break;
            case NOTIFICATION_TYPE_OFR_AUTHOR_MAILED:
                $message = __('plugins.generic.objectsForReview.notification.ofrAuthorMailed');
                break;
            case NOTIFICATION_TYPE_OFR_AUTHOR_REMOVED:
                $message = __('plugins.generic.objectsForReview.notification.ofrAuthorRemoved');
                break;
            case NOTIFICATION_TYPE_OFR_SUBMISSION_ASSIGNED:
                $message = __('plugins.generic.objectsForReview.notification.ofrSubmissionAssigned');
                break;
            case NOTIFICATION_TYPE_OFR_SETTINGS_SAVED:
                $message = __('plugins.generic.objectsForReview.notification.ofrSettingsSaved');
                break;
        }
        return false;
    }

    /**
     * Transfer object for review user assignments when merging users.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function mergeObjectsForReviewAuthors($hookName, $params) {
        $oldUserId = (int) $params[0];
        $newUserId = (int) $params[1];

        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignmentDao->transferAssignments($oldUserId, $newUserId);
        return false;
    }

    /**
     * Delete all plug-in data for a journal when the journal is deleted.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function deleteJournalById($hookName, $params) {
        $journalId = (int) $params[1];
        
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $reviewObjectTypeDao->deleteByContextId($journalId);
        
        /** @var ObjectForReviewDAO $objectForReviewDao */
        $objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReviewDao->deleteByContextId($journalId);
        return false;
    }

    /**
     * Display editor, author and public links.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayLink($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // Reference needed for string modification

            $request = Application::get()->getRequest();
            $router = $request->getRouter();
            $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
            
            if ($hookName === 'Templates::Editor::Index::AdditionalItems') {
                $output .= '<h3>' . __('plugins.generic.objectsForReview.editor.objectsForReview') . '</h3>
                            <ul>
                            <li><a href="' . $request->url(null, 'editor', 'reviewObjectTypes') . '">' . __('plugins.generic.objectsForReview.editor.objectTypes') . '</a></li>
                            <li><a href="' . $request->url(null, 'editor', 'objectsForReview', 'all') . '">' . __('plugins.generic.objectsForReview.editor.objectsForReview') . '</a></li>
                            </ul>';
            } elseif ($hookName === 'Templates::Author::Index::AdditionalItems') {
                $output .= '<br /><div class="separator"></div><h3>' . __('plugins.generic.objectsForReview.author.objectsForReview') . '</h3><ul><li><a href="' . $request->url(null, 'author', 'objectsForReview', 'all') . '">' . __('plugins.generic.objectsForReview.author.myObjectsForReview') . '</a></li></ul><br />';
            } elseif ($hookName === 'Templates::Common::Header::Navbar::CurrentJournal' && $journal && (bool) $this->getSetting((int) $journal->getId(), 'displayListing')) {
                $output .= '<li id="objectsForReview"><a href="' . $request->url(null, 'objectsForReview') . '" target="_parent">' . __('plugins.generic.objectsForReview.public.headerLink') . '</a></li>';
            }
        }
        return false;
    }

    /**
     * Display author's objects for review during submission step 5.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayAuthorObjectsForReview($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2];

            $request = Application::get()->getRequest();
            $router = $request->getRouter();
            $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
            $user = $request->getUser();
            
            if ($journal && $user) {
                /** @var ObjectForReviewDAO $ofrDao */
                $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
                /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
                $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
                
                $objectForReviewAssignments = $ofrAssignmentDao->getAllByUserId((int) $user->getId());
                $authorObjects = [];
                
                foreach ($objectForReviewAssignments as $objectForReviewAssignment) {
                    if ($objectForReviewAssignment->getStatus() === OFR_STATUS_ASSIGNED || $objectForReviewAssignment->getStatus() === BFR_STATUS_MAILED) {
                        $objectForReview = $ofrDao->getById((int) $objectForReviewAssignment->getObjectId(), (int) $journal->getId());
                        if ($objectForReview) {
                            $authorObjects[(int) $objectForReviewAssignment->getObjectId()] = substr((string) $objectForReview->getTitle(), 0, 40);
                        }
                    }
                }
                $smarty->assign('authorObjects', $authorObjects);
                $output .= $smarty->fetch($this->getTemplatePath() . 'author/submissionObjectsForReview.tpl');
            }
        }
        return false;
    }

    /**
     * Allow author to specify objects for review during article submission.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function saveSubmitHandler($hookName, $params) {
        $step = (int) $params[0];
        $article = $params[1];

        if ($step === 5) {
            $request = Application::get()->getRequest();
            $router = $request->getRouter();
            $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
            $user = $request->getUser();
            
            if ($journal && $user) {
                $journalId = (int) $journal->getId();
                $userId = (int) $user->getId();
                $submissionObjectsForReview = $request->getUserVar('submissionObjectsForReview');
                
                if (is_array($submissionObjectsForReview)) {
                    /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
                    $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
                    /** @var ObjectForReviewDAO $ofrDao */
                    $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
                    
                    foreach ($submissionObjectsForReview as $objectForReviewId) {
                        $objectForReviewId = (int) $objectForReviewId;
                        if ($ofrDao->objectForReviewExists($objectForReviewId, $journalId)) {
                            $ofrAssignment = $ofrAssignmentDao->getByObjectAndUserId($objectForReviewId, $userId);
                            if ($ofrAssignment !== null && ($ofrAssignment->getStatus() === OFR_STATUS_ASSIGNED || $ofrAssignment->getStatus() === BFR_STATUS_MAILED)) {
                                $ofrAssignment->setStatus(OFR_STATUS_SUBMITTED);
                                $ofrAssignment->setSubmissionId((int) $article->getId());
                                $ofrAssignmentDao->updateObject($ofrAssignment);
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Add the plug-in stylesheets before displaying the article template.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function handleTemplateDisplay($hookName, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        if ($template === 'article/article.tpl') {
            $request = Application::get()->getRequest();
            $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $this->getStyleSheet());
        }
        return false;
    }

    /**
     * Display object metadata on the article abstract pages.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayAbstract($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2];

        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            return false;
        }
        
        $journalId = (int) $journal->getId();
        $article = $smarty->get_template_vars('article');
        $pubObject = $smarty->get_template_vars('pubObject');
        
        if ($article && $pubObject instanceof Article) {
            $objectsForReview = [];
            /** @var ObjectForReviewDAO $ofrDao */
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $objectForReviewAssignments = $ofrAssignmentDao->getAllBySubmissionId((int) $article->getId());
            
            foreach ($objectForReviewAssignments as $objectForReviewAssignment) {
                $objectForReview = $ofrDao->getById((int) $objectForReviewAssignment->getObjectId(), $journalId);
                if ($objectForReview) {
                    $objectsForReview[] = $objectForReview;
                }
            }

            if (!empty($objectsForReview)) {
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

                $publicFileManager = new PublicFileManager();
                $coverPagePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/';
                $smarty->assign('coverPagePath', $coverPagePath);

                $smarty->assign('objectsForReview', $objectsForReview);
                $smarty->assign('allReviewObjectsMetadata', $allReviewObjectsMetadata);
                
                $reviewObjectMetadata = new ReviewObjectMetadata();
                $smarty->assign('multipleOptionsTypes', $reviewObjectMetadata->getMultipleOptionsTypes());
                
                $smarty->assign('ofrListing', true);
                $smarty->assign('ofrTemplatePath', $this->getTemplatePath());
                $output .= $smarty->fetch($this->getTemplatePath() . 'articleObjectsForReview.tpl');
            }
        }
        return false;
    }

    //
    // Private helper methods
    //
    /**
     * Get editor pages for review object types.
     * @return array
     */
    private function _getReviewObjectTypesEditorPages(): array {
        return [
            'reviewObjectTypes', 'createReviewObjectType', 'editReviewObjectType', 'updateReviewObjectType',
            'previewReviewObjectType', 'deleteReviewObjectType', 'activateReviewObjectType', 'deactivateReviewObjectType',
            'copyReviewObjectType', 'updateOrInstallReviewObjectTypes', 'reviewObjectMetadata', 'createReviewObjectMetadata',
            'editReviewObjectMetadata', 'updateReviewObjectMetadata', 'deleteReviewObjectMetadata', 'moveReviewObjectMetadata',
            'copyOrUpdateReviewObjectMetadata'
        ];
    }

    /**
     * Get editor pages for objects for review.
     * @return array
     */
    private function _getObjectsForReviewEditorPages(): array {
        return [
            'objectsForReview', 'objectsForReviewSettings', 'createObjectForReview', 'editObjectForReview',
            'updateObjectForReview', 'removeObjectForReviewCoverPage', 'deleteObjectForReview', 'selectObjectForReviewAuthor',
            'assignObjectForReviewAuthor', 'acceptObjectForReviewAuthor', 'denyObjectForReviewAuthor', 'notifyObjectForReviewMailed',
            'removeObjectForReviewAssignment', 'selectObjectForReviewSubmission', 'assignObjectForReviewSubmission',
            'editObjectForReviewAssignment', 'updateObjectForReviewAssignment'
        ];
    }

    /**
     * Get public pages for objects for review.
     * @return array
     */
    private function _getObjectsForReviewPublicPages(): array {
        return ['index', 'viewObjectForReview'];
    }

    /**
     * Get author pages for objects for review.
     * @return array
     */
    private function _getObjectsForReviewAuthorPages(): array {
        return ['objectsForReview', 'requestObjectForReview'];
    }

}
?>