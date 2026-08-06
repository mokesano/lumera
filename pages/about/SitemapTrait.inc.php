<?php
declare(strict_types=1);

/**
 * @file pages/about/SitemapTrait.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING
 * 
 * @class SitemapTrait
 * @ingroup pages_about
 * @brief Trait for generating sitemap data for publisher/site level pages.
 * 
 */

trait SitemapTrait {
    
    /**
     * Display siteMap page.
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
        
}
?>