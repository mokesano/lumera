<?php
declare(strict_types=1);

/**
 * @file classes/notification/NotificationManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationManager
 * @ingroup notification
 * @see NotificationDAO
 * @see Notification
 * @brief Class for Notification Manager.
 */

import('lib.pkp.classes.notification.PKPNotificationManager');

class NotificationManager extends PKPNotificationManager {
    
    /** @var array Cache each user's most privileged role for each article */
    protected $_privilegedRoles = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function NotificationManager() {
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
     * Construct a URL for the notification based on its type and associated object.
     * @param mixed $request
     * @param Notification $notification
     * @return string
     */
    public function getNotificationUrl($request, $notification) {
        $router = $request->getRouter();
        $dispatcher = $router->getDispatcher();
        $type = $notification->getType();

        switch ($type) {
            case NOTIFICATION_TYPE_ARTICLE_SUBMITTED:
                $role = $this->_getCachedRole($notification);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submission', $notification->getAssocId());
            case NOTIFICATION_TYPE_SUPP_FILE_MODIFIED:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionEditing', $notification->getAssocId());
            case NOTIFICATION_TYPE_METADATA_MODIFIED:
                $role = $this->_getCachedRole($notification);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submission', $notification->getAssocId(), null, 'metadata');
            case NOTIFICATION_TYPE_GALLEY_MODIFIED:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionEditing', $notification->getAssocId(), null, 'layout');
            case NOTIFICATION_TYPE_SUBMISSION_COMMENT:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionReview', $notification->getAssocId(), null, 'editorDecision');
            case NOTIFICATION_TYPE_LAYOUT_COMMENT:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionEditing', $notification->getAssocId(), null, 'layout');
            case NOTIFICATION_TYPE_COPYEDIT_COMMENT:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                // [WIZDAM FIX] Corrected typo from 'coypedit' to 'copyedit'
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionEditing', $notification->getAssocId(), null, 'copyedit');
            case NOTIFICATION_TYPE_PROOFREAD_COMMENT:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionEditing', $notification->getAssocId(), null, 'proofread');
            case NOTIFICATION_TYPE_REVIEWER_COMMENT:
            case NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionReview', $notification->getAssocId(), null, 'peerReview');
            case NOTIFICATION_TYPE_EDITOR_DECISION_COMMENT:
                $role = $this->_getCachedRole($notification, [ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_AUTHOR]);
                return $dispatcher->url($request, ROUTE_PAGE, null, $role, 'submissionReview', $notification->getAssocId(), null, 'editorDecision');
            case NOTIFICATION_TYPE_USER_COMMENT:
                return $dispatcher->url($request, ROUTE_PAGE, null, 'comment', 'view', $notification->getAssocId());
            case NOTIFICATION_TYPE_PUBLISHED_ISSUE:
                return $dispatcher->url($request, ROUTE_PAGE, null, 'issue', 'current');
            case NOTIFICATION_TYPE_NEW_ANNOUNCEMENT:
                return $dispatcher->url($request, ROUTE_PAGE, null, 'announcement', 'view', [$notification->getAssocId()]);
            default:
                return parent::getNotificationUrl($request, $notification);
        }
    }

    /**
     * Helper to get cached role.
     * @param Notification $notification
     * @param array|null $validRoles
     * @return string|null Role path
     */
    public function _getCachedRole($notification, $validRoles = null) {
        $articleId = (int) $notification->getAssocId();
        $userId = (int) $notification->getUserId();

        if (!isset($this->_privilegedRoles[$userId][$articleId])) {
            $this->_privilegedRoles[$userId][$articleId] = $this->_getHighestPrivilegedRolesForArticle($userId, $articleId);
        }

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');

        if (is_array($validRoles)) {
            foreach ($this->_privilegedRoles[$userId][$articleId] as $roleId) {
                if (in_array($roleId, $validRoles, true)) {
                    return $roleDao->getRolePath($roleId);
                }
            }
        } else {
            $roleId = $this->_privilegedRoles[$userId][$articleId][0] ?? null;
            return $roleDao->getRolePath($roleId);
        }
        return null;
    }

    /**
     * Get a list of the most 'privileged' roles a user has associated with an article.
     * @param int $userId User ID
     * @param int $articleId Article ID
     * @return array
     */
    public function _getHighestPrivilegedRolesForArticle($userId, $articleId) {
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        $roles = [];

        $article = $articleDao->getArticle($articleId);
        if ($article && Validation::isEditor((int) $article->getJournalId())) {
            $roles[] = ROLE_ID_EDITOR;
        }

        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditingSectionEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            if ((int) $userId === (int) $editAssignment->getEditorId()) {
                $roles[] = ROLE_ID_SECTION_EDITOR;
            }
        }

        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $copyedSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
        if ((int) $userId === (int) $copyedSignoff->getUserId()) {
            $roles[] = ROLE_ID_COPYEDITOR;
        }

        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
        if ((int) $userId === (int) $layoutSignoff->getUserId()) {
            $roles[] = ROLE_ID_LAYOUT_EDITOR;
        }

        $proofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
        if ((int) $userId === (int) $proofSignoff->getUserId()) {
            $roles[] = ROLE_ID_PROOFREADER;
        }

        if ($article && (int) $userId === (int) $article->getUserId()) {
            $roles[] = ROLE_ID_AUTHOR;
        }

        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignments = $reviewAssignmentDao->getBySubmissionId($articleId);
        foreach ($reviewAssignments as $reviewAssignment) {
            if ((int) $userId === (int) $reviewAssignment->getReviewerId()) {
                $roles[] = ROLE_ID_REVIEWER;
            }
        }

        return $roles;
    }

    /**
     * Construct the contents for the notification based on its type and associated object.
     * @param mixed $request
     * @param Notification $notification
     * @return string
     */
    public function getNotificationContents($request, $notification) {
        $type = $notification->getType();

        $message = null;
        HookRegistry::dispatch('NotificationManager::getNotificationContents', [$notification, &$message]);
        
        if ($message) {
            return $message;
        }

        switch ($type) {
            case NOTIFICATION_TYPE_ARTICLE_SUBMITTED:
                return __('notification.type.articleSubmitted', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_SUPP_FILE_MODIFIED:
                return __('notification.type.suppFileModified', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_METADATA_MODIFIED:
                return __('notification.type.metadataModified', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_GALLEY_MODIFIED:
                return __('notification.type.galleyModified', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_SUBMISSION_COMMENT:
                return __('notification.type.submissionComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_LAYOUT_COMMENT:
                return __('notification.type.layoutComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_COPYEDIT_COMMENT:
                return __('notification.type.copyeditComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_PROOFREAD_COMMENT:
                return __('notification.type.proofreadComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_REVIEWER_COMMENT:
            case NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT:
                return __('notification.type.reviewerComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_EDITOR_DECISION_COMMENT:
                return __('notification.type.editorDecisionComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_USER_COMMENT:
                return __('notification.type.userComment', ['title' => $this->_getArticleTitle($notification)]);
            case NOTIFICATION_TYPE_PUBLISHED_ISSUE:
                return __('notification.type.issuePublished');
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_SUCCESS:
                return __('gifts.giftRedeemed');
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_NO_GIFT_TO_REDEEM:
                return __('gifts.noGiftToRedeem');
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_ALREADY_REDEEMED:
                return __('gifts.giftAlreadyRedeemed');
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_INVALID:
                return __('gifts.giftNotValid');
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_TYPE_INVALID:
                return __('gifts.subscriptionTypeNotValid');
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_NON_EXPIRING:
                return __('gifts.subscriptionNonExpiring');
            case NOTIFICATION_TYPE_BOOK_REQUESTED:
                return __('plugins.generic.booksForReview.notification.bookRequested');
            case NOTIFICATION_TYPE_BOOK_CREATED:
                return __('plugins.generic.booksForReview.notification.bookCreated');
            case NOTIFICATION_TYPE_BOOK_UPDATED:
                return __('plugins.generic.booksForReview.notification.bookUpdated');
            case NOTIFICATION_TYPE_BOOK_DELETED:
                return __('plugins.generic.booksForReview.notification.bookDeleted');
            case NOTIFICATION_TYPE_BOOK_MAILED:
                return __('plugins.generic.booksForReview.notification.bookMailed');
            case NOTIFICATION_TYPE_BOOK_SETTINGS_SAVED:
                return __('plugins.generic.booksForReview.notification.settingsSaved');
            case NOTIFICATION_TYPE_BOOK_SUBMISSION_ASSIGNED:
                return __('plugins.generic.booksForReview.notification.submissionAssigned');
            case NOTIFICATION_TYPE_BOOK_AUTHOR_ASSIGNED:
                return __('plugins.generic.booksForReview.notification.authorAssigned');
            case NOTIFICATION_TYPE_BOOK_AUTHOR_DENIED:
                return __('plugins.generic.booksForReview.notification.authorDenied');
            case NOTIFICATION_TYPE_BOOK_AUTHOR_REMOVED:
                return __('plugins.generic.booksForReview.notification.authorRemoved');
            default:
                return parent::getNotificationContents($request, $notification);
        }
    }

    /**
     * Helper function to get an article title from a notification's associated object.
     * @param Notification $notification
     * @return string|null
     */
    public function _getArticleTitle($notification) {
        $articleId = (int) $notification->getAssocId();
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);
        if (!$article) {
            return null;
        }
        return (string) $article->getLocalizedTitle();
    }

    /**
     * Get notification style class.
     * @param Notification $notification
     * @return string
     */
    public function getStyleClass($notification) {
        switch ($notification->getType()) {
            case NOTIFICATION_TYPE_BOOK_REQUESTED:
            case NOTIFICATION_TYPE_BOOK_CREATED:
            case NOTIFICATION_TYPE_BOOK_UPDATED:
            case NOTIFICATION_TYPE_BOOK_DELETED:
            case NOTIFICATION_TYPE_BOOK_MAILED:
            case NOTIFICATION_TYPE_BOOK_SETTINGS_SAVED:
            case NOTIFICATION_TYPE_BOOK_SUBMISSION_ASSIGNED:
            case NOTIFICATION_TYPE_BOOK_AUTHOR_ASSIGNED:
            case NOTIFICATION_TYPE_BOOK_AUTHOR_DENIED:
            case NOTIFICATION_TYPE_BOOK_AUTHOR_REMOVED:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_SUCCESS:
                return 'notifySuccess';
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_NO_GIFT_TO_REDEEM:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_ALREADY_REDEEMED:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_INVALID:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_TYPE_INVALID:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_NON_EXPIRING:
                return 'notifyError';
            default: 
                return parent::getStyleClass($notification);
        }
    }

    /**
     * Return a CSS class containing the icon of this notification type.
     * @param Notification $notification
     * @return string
     */
    public function getIconClass($notification) {
        switch ($notification->getType()) {
            case NOTIFICATION_TYPE_ARTICLE_SUBMITTED:
                return 'notifyIconNewPage';
            case NOTIFICATION_TYPE_SUPP_FILE_MODIFIED:
                return 'notifyIconPageAttachment';
            case NOTIFICATION_TYPE_METADATA_MODIFIED:
            case NOTIFICATION_TYPE_GALLEY_MODIFIED:
                return 'notifyIconEdit';
            case NOTIFICATION_TYPE_SUBMISSION_COMMENT:
            case NOTIFICATION_TYPE_LAYOUT_COMMENT:
            case NOTIFICATION_TYPE_COPYEDIT_COMMENT:
            case NOTIFICATION_TYPE_PROOFREAD_COMMENT:
            case NOTIFICATION_TYPE_REVIEWER_COMMENT:
            case NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT:
            case NOTIFICATION_TYPE_EDITOR_DECISION_COMMENT:
            case NOTIFICATION_TYPE_USER_COMMENT:
                return 'notifyIconNewComment';
            case NOTIFICATION_TYPE_PUBLISHED_ISSUE:
                return 'notifyIconPublished';
            case NOTIFICATION_TYPE_NEW_ANNOUNCEMENT:
                return 'notifyIconNewAnnouncement';
            case NOTIFICATION_TYPE_BOOK_REQUESTED:
            case NOTIFICATION_TYPE_BOOK_CREATED:
            case NOTIFICATION_TYPE_BOOK_UPDATED:
            case NOTIFICATION_TYPE_BOOK_DELETED:
            case NOTIFICATION_TYPE_BOOK_MAILED:
            case NOTIFICATION_TYPE_BOOK_SETTINGS_SAVED:
            case NOTIFICATION_TYPE_BOOK_SUBMISSION_ASSIGNED:
            case NOTIFICATION_TYPE_BOOK_AUTHOR_ASSIGNED:
            case NOTIFICATION_TYPE_BOOK_AUTHOR_DENIED:
            case NOTIFICATION_TYPE_BOOK_AUTHOR_REMOVED:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_SUCCESS:
                return 'notifyIconSuccess';
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_NO_GIFT_TO_REDEEM:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_ALREADY_REDEEMED:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_INVALID:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_TYPE_INVALID:
            case NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_NON_EXPIRING:
                return 'notifyIconError';
            default: 
                return parent::getIconClass($notification);
        }
    }

    /**
     * Returns an array of information on the journal's subscription settings.
     * @param mixed $request
     * @return array
     */
    public function getSubscriptionSettings($request) {
        // [SCHOLARWIZDAM LUMERA STANDARD] Context resolution via Router
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            return [];
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);

        return [
            'subscriptionsEnabled' => $paymentManager->acceptSubscriptionPayments(),
            'allowRegReviewer' => (bool) $journal->getSetting('allowRegReviewer'),
            'allowRegAuthor' => (bool) $journal->getSetting('allowRegAuthor')
        ];
    }
    
}
?>