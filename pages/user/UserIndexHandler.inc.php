<?php
declare(strict_types=1);

/**
 * @file pages/user/UserIndexHandler.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class UserIndexHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user dashboard (User Home).
 * [WIZDAM EDITION] Refactored from UserHandler for better separation of concerns.
 */

import('pages.user.UserHandler');

class UserIndexHandler extends UserHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display user index page.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();

        $roleDao = DAORegistry::getDAO('RoleDAO');

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager();

        $journal = $request->getJournal();
        $templateMgr->assign('helpTopicId', 'user.userHome');

        $user = $request->getUser();
        if (!$user) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = $user->getId();

        $setupIncomplete = [];
        $userJournals = [];

        // [WIZDAM HOTFIX] Inisialisasi struktur multi-dimensi secara eksplisit.
        // Mencegah PHP 8.4 Warning: "Trying to access array offset on null"
        $isValid = [
            'JournalManager' => [],
            'SubscriptionManager' => [],
            'Author' => [],
            'Copyeditor' => [],
            'LayoutEditor' => [],
            'Editor' => [],
            'SectionEditor' => [],
            'Proofreader' => [],
            'Reviewer' => []
        ];

        $submissionsCount = [
            'Author' => [],
            'Copyeditor' => [],
            'LayoutEditor' => [],
            'Editor' => [],
            'SectionEditor' => [],
            'Proofreader' => [],
            'Reviewer' => []
        ];

        if ($journal == null) { // Currently at site level
            // Show roles for all journals
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournals();

            // Fetch the user's roles for each journal
            while ($journal = $journals->next()) {
                $journalId = $journal->getId();

                // Determine if journal setup is incomplete, to provide a message for JM
                $setupIncomplete[$journalId] = $this->_checkIncompleteSetup($journal);

                $roles = $roleDao->getRolesByUserId($userId, $journalId);
                if (!empty($roles)) {
                    $userJournals[] = $journal;
                    $this->_getRoleDataForJournal($userId, $journalId, $submissionsCount, $isValid);
                }
            }

            $templateMgr->assign('userJournals', $userJournals);
            $templateMgr->assign('showAllJournals', 1);

            $allJournals = $journalDao->getJournals();
            $templateMgr->assign('allJournals', $allJournals->toArray());

        } else { // Currently within a journal's context.
            $journalId = $journal->getId();

            // Determine if journal setup is incomplete, to provide a message for JM
            $setupIncomplete[$journalId] = $this->_checkIncompleteSetup($journal);

            $userJournals = [$journal];

            $this->_getRoleDataForJournal($userId, $journalId, $submissionsCount, $isValid);

            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $subscriptionsEnabled = $journal->getSetting('publishingMode') ==  PUBLISHING_MODE_SUBSCRIPTION
                && ($subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, false)
                    || $subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, true)) ? true : false;
            $templateMgr->assign('subscriptionsEnabled', $subscriptionsEnabled);

            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            $acceptGiftPayments = $paymentManager->acceptGiftPayments();
            $templateMgr->assign('acceptGiftPayments', $acceptGiftPayments);
            $membershipEnabled = $paymentManager->membershipEnabled();
            $templateMgr->assign('membershipEnabled', $membershipEnabled);

            if ( $membershipEnabled ) {
                $templateMgr->assign('dateEndMembership', $user->getSetting('dateEndMembership', 0));
            }

            $templateMgr->assign('allowRegAuthor', $journal->getSetting('allowRegAuthor'));
            $templateMgr->assign('allowRegReviewer', $journal->getSetting('allowRegReviewer'));

            $templateMgr->assign('userJournals', $userJournals);
        }

        $templateMgr->assign('isValid', $isValid);
        $templateMgr->assign('submissionsCount', $submissionsCount);
        $templateMgr->assign('setupIncomplete', $setupIncomplete);
        $templateMgr->assign('isSiteAdmin', $roleDao->getRole(0, $userId, ROLE_ID_SITE_ADMIN));
        $templateMgr->display('user/index.tpl');
    }
}
?>