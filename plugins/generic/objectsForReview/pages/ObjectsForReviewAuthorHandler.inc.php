<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/pages/ObjectsForReviewAuthorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewAuthorHandler
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Handle requests for author object for review functions.
 */

import('classes.handler.Handler');

class ObjectsForReviewAuthorHandler extends Handler {

    /**
     * Display objects for review author listing page.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function objectsForReview($args, $request) {
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $user = $request->getUser();
        if (!$user) {
            $request->redirect(null, 'index');
        }
        $userId = (int) $user->getId();

        // Sort
        $sort = (string) $request->getUserVar('sort');
        $sort = $sort !== '' ? $sort : 'title';
        
        $sortDirection = (string) $request->getUserVar('sortDirection');
        $sortDirection = $sortDirection !== '' ? $sortDirection : SORT_DIRECTION_ASC;

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $mode = $ofrPlugin ? (int) $ofrPlugin->getSetting($journalId, 'mode') : 0;

        if ($ofrPlugin) {
            $ofrPlugin->import('classes.ObjectForReviewAssignment');
        }
        
        $path = isset($args[0]) ? (string) $args[0] : 'all';
        
        switch ($path) {
            case 'requested':
                $status = OFR_STATUS_REQUESTED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleRequested';
                break;
            case 'assigned':
                $status = OFR_STATUS_ASSIGNED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleAssigned';
                break;
            case 'mailed':
                $status = OFR_STATUS_MAILED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleMailed';
                break;
            case 'submitted':
                $status = OFR_STATUS_SUBMITTED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleSubmitted';
                break;
            case 'all':
            default:
                $path = 'all';
                $status = null;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleAll';
        }

        $rangeInfo = Handler::getRangeInfo('objectForReview');
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $objectForReviewAssignments = $ofrAssignmentDao->getAllByContextId(
            $journalId, null, null, null, $status, $userId, null, null, $rangeInfo, $sort, $sortDirection
        );

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);
        $templateMgr->assign('mode', $mode);
        $templateMgr->assign('returnPage', $path);
        $templateMgr->assign('pageTitle', $pageTitle);
        $templateMgr->assign('objectForReviewAssignments', $objectForReviewAssignments);
        $templateMgr->assign('counts', $ofrAssignmentDao->getStatusCounts($journalId, $userId));
        
        if ($ofrPlugin) {
            $templateMgr->display($ofrPlugin->getTemplatePath() . 'author/objectsForReviewAssignments.tpl');
        }
    }

    /**
     * Author requests an object for review.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function requestObjectForReview($args, $request) {
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'objectsForReview');
        }
        
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);

        $redirect = true;
        if ($objectForReview && (int) $objectForReview->getAvailable() === 1) {
            $user = $request->getUser();
            if ($user) {
                $userId = (int) $user->getId();
                
                /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
                $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
                if ($ofrAssignmentDao->assignmentExists($objectId, $userId)) {
                    $request->redirect(null, 'objectsForReview');
                }

                import('classes.mail.MailTemplate');
                $email = new MailTemplate('OFR_OBJECT_REQUESTED');
                
                $send = (bool) $request->getUserVar('send');
                
                // Author has filled out mail form or decided to skip email
                if ($send && !$email->hasErrors()) {
                    $ofrAssignment = $ofrAssignmentDao->newDataObject();
                    $ofrAssignment->setObjectId($objectId);
                    $ofrAssignment->setUserId($userId);
                    $ofrAssignment->setStatus(OFR_STATUS_REQUESTED);
                    $ofrAssignment->setDateRequested(Core::getCurrentDate());
                    $ofrAssignmentDao->insertObject($ofrAssignment);
                    $email->send();
                    $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_REQUESTED, $request);
                } else {
                    $returnUrl = $request->url(null, 'author', 'requestObjectForReview', [(string) $objectId]);
                    $this->_displayEmailForm($email, $objectForReview, $user, $returnUrl, 'OFR_OBJECT_REQUESTED', $request);
                    $redirect = false;
                }
            }
        }
        if ($redirect) {
            $request->redirect(null, 'objectsForReview');
        }
    }

    /**
     * Ensure that we have a journal, plugin is enabled, and user is author.
     * @see PKPHandler::authorize()
     * @param mixed $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            return false;
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin || !$ofrPlugin->getEnabled()) {
            return false;
        }

        if (!Validation::isAuthor((int) $journal->getId())) {
            Validation::redirectLogin();
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
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'author'),
                'user.role.author'
            ]
        ];
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
        
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
    public function _getObjectsForReviewPlugin() {
        return PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
    }

    /**
     * Ensure object for review exists.
     * @param int|null $objectId
     * @param int $journalId
     * @return bool
     */
    public function _ensureObjectExists($objectId, $journalId) {
        if ($objectId === null) {
            return false;
        }
        
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        return $ofrDao->objectForReviewExists($objectId, $journalId);
    }

    /**
     * Display email form for the author.
     * @param MailTemplate $email
     * @param ObjectForReview $objectForReview
     * @param User $user
     * @param string $returnUrl
     * @param string $action
     * @param mixed $request
     * @return void
     */
    public function _displayEmailForm($email, $objectForReview, $user, $returnUrl, $action, $request) {
        $continuedFlag = (bool) $request->getUserVar('continued');
        $paramArray = [];

        if (!$continuedFlag) {
            $editor = $objectForReview->getEditor();
            if ($editor) {
                $editorFullName = (string) $editor->getFullName();
                $editorEmail = (string) $editor->getEmail();

                if ($action === 'OFR_OBJECT_REQUESTED') {
                    $paramArray = [
                        'editorName' => strip_tags($editorFullName),
                        'objectForReviewTitle' => '"' . strip_tags((string) $objectForReview->getTitle()) . '"',
                        'authorContactSignature' => PKPString::html2text((string) $user->getContactSignature())
                    ];
                }
                $email->addRecipient($editorEmail, $editorFullName);
                $email->assignParams($paramArray);
            }
        }
        $email->displayEditForm($returnUrl);
    }

    /**
     * Create trivial notification.
     * @param int $notificationType
     * @param mixed $request
     * @return void
     */
    public function _createTrivialNotification($notificationType, $request) {
        $user = $request->getUser();
        if ($user) {
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification((int) $user->getId(), (int) $notificationType);
        }
    }

}
?>