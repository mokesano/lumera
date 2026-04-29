<?php
declare(strict_types=1);

/**
 * @file pages/rtadmin/RTSetupHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTSetupHandler
 * @ingroup pages_rtadmin
 *
 * @brief Handle Reading Tools administration requests -- setup section.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & UI Amputation
 */

import('pages.rtadmin.RTAdminHandler');

class RTSetupHandler extends RTAdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RTSetupHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RTSetupHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display settings page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function settings($args = [], $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        if ($journal) {
            $this->setupTemplate(true);
            $templateMgr = TemplateManager::getManager();

            $rtDao = DAORegistry::getDAO('RTDAO');
            $rt = $rtDao->getJournalRTByJournal($journal);

            $versionOptions = [];
            $versions = $rtDao->getVersions($journal->getId());
            foreach ($versions->toArray() as $version) {
                $versionOptions[$version->getVersionId()] = $version->getTitle();
            }

            $templateMgr->assign('versionOptions', $versionOptions);
            
            // [WIZDAM CLEANUP] Hanya melempar variabel yang relevan ke Smarty TPL
            $templateMgr->assign('version', $rt->getVersion());
            $templateMgr->assign('enabled', $rt->getEnabled());
            $templateMgr->assign('abstract', $rt->getAbstract());
            $templateMgr->assign('captureCite', $rt->getCaptureCite());
            $templateMgr->assign('viewMetadata', $rt->getViewMetadata());
            $templateMgr->assign('supplementaryFiles', $rt->getSupplementaryFiles());
            
            // Variabel usang (printerFriendly, defineTerms, dll) dan fitur Komentar RT DIHAPUS

            $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.settings');
            $templateMgr->display('rtadmin/settings.tpl');
        } else {
            $request->redirect(null, $request->getRequestedPage());
        }
    }

    /**
     * Save settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSettings($args = [], $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        if ($journal) {
            $rtDao = DAORegistry::getDAO('RTDAO');
            $rt = $rtDao->getJournalRTByJournal($journal);

            // [SECURITY FIX] Amankan 'version' (ID integer) with (int) trim()
            $versionInput = (int) trim((string) $request->getUserVar('version'));
            if ($versionInput == 0) { // Cek jika 0 (karena casting dari '' atau null)
                $rt->setVersion(null);
            } else {
                $rt->setVersion($versionInput);
            }
            
            // [WIZDAM CLEANUP] Hanya menangkap parameter form yang dipertahankan
            $rt->setEnabled((bool) trim((string) $request->getUserVar('enabled')));
            $rt->setAbstract((bool) trim((string) $request->getUserVar('abstract')));
            $rt->setCaptureCite((bool) trim((string) $request->getUserVar('captureCite')));
            $rt->setViewMetadata((bool) trim((string) $request->getUserVar('viewMetadata')));
            $rt->setSupplementaryFiles((bool) trim((string) $request->getUserVar('supplementaryFiles')));
            
            // Fitur usang (printerFriendly, defineTerms, dll) dan fitur Komentar RT DIHAPUS dari penyimpanan

            $rtDao->updateJournalRT($rt);
        }
        $request->redirect(null, $request->getRequestedPage());
    }
}
?>