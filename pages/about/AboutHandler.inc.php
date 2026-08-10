<?php
declare(strict_types=1);

/**
 * @file pages/about/AboutHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutHandler
 * @ingroup pages_about
 *
 * @brief Handle ops shared BETWEEN journal and publisher/site context
 * (index, sitemap), AND serve as the shared base class (constructor,
 * setupTemplate()) for AboutJournalHandler dan AboutPublisherHandler.
 */

import('classes.handler.Handler');

class AboutHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward compatibility.
     * Use __construct() instead.
     * @deprecated
     */
    public function AboutHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display about index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager();
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalPath = $request->getRequestedJournalPath();

        if ($journalPath != 'index' && $journalDao->journalExistsByPath($journalPath)) {
            $journal = $request->getJournal();
            /** @var JournalSettingsDAO $journalSettingsDao */
            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            $templateMgr->assign('journalSettings', $journalSettingsDao->getJournalSettings($journal->getId()));

            $customAboutItems = $journalSettingsDao->getSetting($journal->getId(), 'customAboutItems');
            if (isset($customAboutItems[AppLocale::getLocale()])) {
                $templateMgr->assign('customAboutItems', $customAboutItems[AppLocale::getLocale()]);
            } elseif (isset($customAboutItems[AppLocale::getPrimaryLocale()])) {
                $templateMgr->assign('customAboutItems', $customAboutItems[AppLocale::getPrimaryLocale()]);
            }

            foreach ($this->_getPublicStatisticsNames() as $name) {
                if ($journal->getSetting($name)) {
                    $templateMgr->assign('publicStatisticsEnabled', true);
                    break;
                } 
            }
            
            // Hide membership if the payment method is not configured
            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            $templateMgr->assign('paymentConfigured', $paymentManager->isConfigured());

            if ($journal->getSetting('boardEnabled')) {
                /** @var GroupDAO $groupDao */
                $groupDao = DAORegistry::getDAO('GroupDAO');
                $groups = $groupDao->getGroups(ASSOC_TYPE_JOURNAL, $journal->getId(), GROUP_CONTEXT_PEOPLE);
                $templateMgr->assign('peopleGroups', $groups);
            }

            $templateMgr->assign('helpTopicId', 'user.about');
            $templateMgr->display('about/index.tpl');
        } else {
            $site = $request->getSite();
            $about = $site->getLocalizedAbout();

            $templateMgr->assign('about', $about);
            $journals = $journalDao->getJournals(true);
            $templateMgr->assign('journals', $journals);

            $templateMgr->display('about/site.tpl');
        }
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_CORE_MANAGER);

        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }
        
        if ($subclass) {
            $templateMgr->assign('pageHierarchy', [[$request->url(null, 'about'), 'about.aboutTheJournal']]);
        }

        if ($journal) {
            $journalId = (int) $journal->getId();
            /** @var GroupDAO $groupDao */
            $groupDao = DAORegistry::getDAO('GroupDAO');
            
            $templateMgr->assign([
                'hasDisplayMembership' => $groupDao->hasDisplayMembershipGroups($journalId),
                'displayMembershipGroups' => $groupDao->getDisplayMembershipGroupsData($journalId)
            ]);
        }
    }

    /**
     * About sitemap.
     */
    public function sitemap() {
        $this->validate();
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager();
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $user = Application::get()->getRequest()->getUser();
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');

        if ($user) {
            $rolesByJournal = [];
            $journals = $journalDao->getJournals(true);
            // Fetch the user's roles for each journal
            foreach ($journals->toArray() as $journal) {
                $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
                if (!empty($roles)) {
                    $rolesByJournal[$journal->getId()] = $roles;
                }
            }
        }

        $journals = $journalDao->getJournals(true);
        $templateMgr->assign('journals', $journals->toArray());
        if (isset($rolesByJournal)) {
            $templateMgr->assign('rolesByJournal', $rolesByJournal);
        }
        if ($user) {
            $templateMgr->assign('isSiteAdmin', $roleDao->getRole(0, $user->getId(), ROLE_ID_SITE_ADMIN));
        }

        $templateMgr->display('about/sitemap.tpl');
    }

    /**
     * HELPER: Get public statistics names.
     */
    public function _getPublicStatisticsNames() {
        import ('pages.manager.ManagerHandler');
        import ('pages.manager.StatisticsHandler');

        return [
            'statNumPublishedIssues',
            'statItemsPublished',
            'statNumSubmissions',
            'statPeerReviewed',
            'statCountAccept',
            'statCountDecline',
            'statCountRevise',
            'statDaysPerReview',
            'statDaysToPublication',
            'statRegisteredUsers',
            'statRegisteredReaders',
            'statSubscriptions',
        ];
    }

}
?>