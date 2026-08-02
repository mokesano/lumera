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
 */

import('pages.user.UserHandler');

class UserIndexHandler extends UserHandler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display user index page.
     * @param array $args
     * @param object|null $request PKPRequest
     * @return void
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager();

        $journal = $request->getJournal();
        $templateMgr->assign('helpTopicId', 'user.userHome');

        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');

        $setupIncomplete = [];
        $userJournals = [];

        // [WIZDAM HOTFIX] Explicitly initialize multi-dimensional structure.
        // Prevents PHP 8.4 Warning: "Trying to access array offset on null"
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

        if ($journal === null) { // Currently at site level
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournals();

            // Fetch the user's roles for each journal
            while ($currentJournal = $journals->next()) {
                $journalId = (int) $currentJournal->getId();

                // Determine if journal setup is incomplete, to provide a message for JM
                $setupIncomplete[$journalId] = $this->_checkIncompleteSetup($currentJournal);

                $roles = $roleDao->getRolesByUserId($userId, $journalId);
                if (!empty($roles)) {
                    $userJournals[] = $currentJournal;
                    $this->_getRoleDataForJournal($userId, $journalId, $submissionsCount, $isValid);
                }
            }

            $templateMgr->assign('userJournals', $userJournals);
            $templateMgr->assign('showAllJournals', 1);

            $allJournals = $journalDao->getJournals();
            $templateMgr->assign('allJournals', $allJournals->toArray());

        } else { // Currently within a journal's context.
            $journalId = (int) $journal->getId();

            // Determine if journal setup is incomplete, to provide a message for JM
            $setupIncomplete[$journalId] = $this->_checkIncompleteSetup($journal);

            $userJournals = [$journal];

            $this->_getRoleDataForJournal($userId, $journalId, $submissionsCount, $isValid);

            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $publishingMode = (int) $journal->getSetting('publishingMode');
            $subscriptionsEnabled = $publishingMode === PUBLISHING_MODE_SUBSCRIPTION
                && ($subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, false)
                    || $subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, true));
            $templateMgr->assign('subscriptionsEnabled', $subscriptionsEnabled);

            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            
            $acceptGiftPayments = (bool) $paymentManager->acceptGiftPayments();
            $templateMgr->assign('acceptGiftPayments', $acceptGiftPayments);
            
            $membershipEnabled = (bool) $paymentManager->membershipEnabled();
            $templateMgr->assign('membershipEnabled', $membershipEnabled);

            if ($membershipEnabled) {
                $dateEndMembership = $user->getSetting('dateEndMembership');
                $templateMgr->assign('dateEndMembership', $dateEndMembership !== null ? (int) $dateEndMembership : 0);
            }

            $templateMgr->assign('allowRegAuthor', (bool) $journal->getSetting('allowRegAuthor'));
            $templateMgr->assign('allowRegReviewer', (bool) $journal->getSetting('allowRegReviewer'));

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