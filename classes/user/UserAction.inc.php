<?php
declare(strict_types=1);

/**
 * @file classes/user/UserAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserAction
 * @ingroup user
 * @see User
 *
 * @brief UserAction class.
 * 
 */

class UserAction {

    /**
     * Constructor.
     */
    public function __construct() {
        // No implementation construct
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserAction() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class " . get_class($this) . " uses deprecated constructor parent::UserAction(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Actions.
     */

    /**
     * Merge user accounts, including attributed articles etc.
     * @param int $oldUserId
     * @param int $newUserId
     * @return boolean
     */
    public function mergeUsers($oldUserId, $newUserId) {
        // Need both user ids for merge
        if (empty($oldUserId) || empty($newUserId)) {
            return false;
        }

        HookRegistry::dispatch('UserAction::mergeUsers', array(&$oldUserId, &$newUserId));

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        foreach ($articleDao->getArticlesByUserId($oldUserId) as $article) {
            $article->setUserId($newUserId);
            $articleDao->updateArticle($article);
            unset($article);
        }

        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $newUser = $userDao->getUser($newUserId);
        foreach ($commentDao->getByUserId($oldUserId) as $comment) {
            $comment->setUser($newUser);
            $commentDao->updateComment($comment);
            unset($comment);
        }

        /** @var NoteDAO $noteDao */
        $noteDao = DAORegistry::getDAO('NoteDAO');
        $notes = $noteDao->getByUserId($oldUserId);
        while ($note = $notes->next()) {
            $note->setUserId($newUserId);
            $noteDao->updateObject($note);
            unset($note);
        }

        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditAssignmentsByUserId($oldUserId);
        while ($editAssignment = $editAssignments->next()) {
            $editAssignment->setEditorId($newUserId);
            $editAssignmentDao->updateEditAssignment($editAssignment);
            unset($editAssignment);
        }

        /** @var EditorSubmissionDAO $editorSubmissionDao */
        $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
        $editorSubmissionDao->transferEditorDecisions($oldUserId, $newUserId);

        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        foreach ($reviewAssignmentDao->getByUserId($oldUserId) as $reviewAssignment) {
            $reviewAssignment->setReviewerId($newUserId);
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
            unset($reviewAssignment);
        }

        // Transfer signoffs (e.g. copyediting, layout editing)
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $signoffDao->transferSignoffs($oldUserId, $newUserId);

        /** @var ArticleEmailLogDAO $articleEmailLogDao */
        $articleEmailLogDao = DAORegistry::getDAO('ArticleEmailLogDAO');
        $articleEmailLogDao->changeUser($oldUserId, $newUserId);
        /** @var ArticleEventLogDAO $articleEventLogDao */
        $articleEventLogDao = DAORegistry::getDAO('ArticleEventLogDAO');
        $articleEventLogDao->changeUser($oldUserId, $newUserId);

        /** @var ArticleCommentDAO $articleCommentDao */
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        foreach ($articleCommentDao->getArticleCommentsByUserId($oldUserId) as $articleComment) {
            $articleComment->setAuthorId($newUserId);
            $articleCommentDao->updateArticleComment($articleComment);
            unset($articleComment);
        }

        /** @var AccessKeyDAO $accessKeyDao */
        $accessKeyDao = DAORegistry::getDAO('AccessKeyDAO');
        $accessKeyDao->transferAccessKeys($oldUserId, $newUserId);

        // Transfer old user's individual subscriptions for each journal if new user
        // does not have a valid individual subscription for a given journal.
        /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
        $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        $oldUserSubscriptions = $individualSubscriptionDao->getSubscriptionsByUser($oldUserId);

        while ($oldUserSubscription = $oldUserSubscriptions->next()) {
            $subscriptionJournalId = $oldUserSubscription->getJournalId();
            $oldUserValidSubscription = $individualSubscriptionDao->isValidIndividualSubscription($oldUserId, $subscriptionJournalId);
            if ($oldUserValidSubscription) {
                // Check if new user has a valid subscription for current journal
                $newUserSubscriptionId = $individualSubscriptionDao->getSubscriptionIdByUser($newUserId, $subscriptionJournalId);
                if (empty($newUserSubscriptionId)) {
                    // New user does not have this subscription, transfer old user's
                    $oldUserSubscription->setUserId($newUserId);
                    $individualSubscriptionDao->updateSubscription($oldUserSubscription);
                } elseif (!$individualSubscriptionDao->isValidIndividualSubscription($newUserId, $subscriptionJournalId)) {
                    // New user has a subscription but it's invalid. Delete it and
                    // transfer old user's valid one
                    $individualSubscriptionDao->deleteSubscriptionsByUserIdForJournal($newUserId, $subscriptionJournalId);
                    $oldUserSubscription->setUserId($newUserId);
                    $individualSubscriptionDao->updateSubscription($oldUserSubscription);
                }
            }
        }

        // Delete any remaining old user's subscriptions not transferred to new user
        $individualSubscriptionDao->deleteSubscriptionsByUserId($oldUserId);

        // Transfer all old user's institutional subscriptions for each journal to
        // new user. New user now becomes the contact person for these.
        /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
        $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        $oldUserSubscriptions = $institutionalSubscriptionDao->getSubscriptionsByUser($oldUserId);
        while ($oldUserSubscription = $oldUserSubscriptions->next()) {
            $oldUserSubscription->setUserId($newUserId);
            $institutionalSubscriptionDao->updateSubscription($oldUserSubscription);
        }

        // Transfer old user's gifts to new user
        /** @var GiftDAO $giftDao */
        $giftDao = DAORegistry::getDAO('GiftDAO');
        $gifts = $giftDao->getAllGiftsByRecipient(ASSOC_TYPE_JOURNAL, $oldUserId);
        while ($gift = $gifts->next()) {
            $gift->setRecipientUserId($newUserId);
            $giftDao->updateObject($gift);
            unset($gift);
        }

        // Transfer completed payments.
        /** @var OJSCompletedPaymentDAO $paymentDao */
        $paymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
        $paymentFactory = $paymentDao->getByUserId($oldUserId);
        while ($payment = $paymentFactory->next()) {
            $payment->setUserId($newUserId);
            $paymentDao->updateObject($payment);
            unset($payment);
        }

        // Delete the old user and associated info.
        /** @var SessionDAO $sessionDao */
        $sessionDao = DAORegistry::getDAO('SessionDAO');
        $sessionDao->deleteSessionsByUserId($oldUserId);
        /** @var TemporaryFileDAO $temporaryFileDao */
        $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
        $temporaryFileDao->deleteTemporaryFilesByUserId($oldUserId);
        /** @var UserSettingsDAO $userSettingsDao */
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        $userSettingsDao->deleteSettings($oldUserId);
        /** @var GroupMembershipDAO $groupMembershipDao */
        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $groupMembershipDao->deleteMembershipByUserId($oldUserId);
        /** @var SectionEditorsDAO $sectionEditorsDao */
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $sectionEditorsDao->deleteEditorsByUserId($oldUserId);

        // Transfer old user's roles
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDao->getRolesByUserId($oldUserId);
        foreach ($roles as $role) {
            if (!$roleDao->userHasRole($role->getJournalId(), $newUserId, $role->getRoleId())) {
                $role->setUserId($newUserId);
                $roleDao->insertRole($role);
            }
        }
        $roleDao->deleteRoleByUserId($oldUserId);

        $userDao->deleteUserById($oldUserId);

        return true;
    }
    
}
?>