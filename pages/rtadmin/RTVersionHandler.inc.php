<?php
declare(strict_types=1);

/**
 * @file pages/rtadmin/RTVersionHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTVersionHandler
 * @ingroup pages_rtadmin
 *
 * @brief Handle Reading Tools administration requests -- setup section.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.rtadmin.RTAdminHandler');
import('classes.rt.ojs.JournalRTAdmin'); // [WIZDAM] Explicit import

class RTVersionHandler extends RTAdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RTVersionHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RTVersionHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
    
    /**
     * Create version.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createVersion($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journal = $request->getJournal();

        import('classes.rt.ojs.form.VersionForm');
        $versionForm = new VersionForm(null, $journal->getId());

        if (isset($args[0]) && $args[0]=='save') {
            $versionForm->readInputData();
            $versionForm->execute();
            $request->redirect(null, null, 'versions');
        } else {
            $this->setupTemplate(true);
            $versionForm->display();
        }
    }

    /**
     * Export version.
     * @param array $args
     * @param PKPRequest $request
     */
    public function exportVersion($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());

        if ($version) {
            $templateMgr = TemplateManager::getManager();
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('version', $version);

            $templateMgr->display('rtadmin/exportXml.tpl', 'application/xml');
        } else {
            $request->redirect(null, null, 'versions');
        }
    }

    /**
     * Import version.
     * @param array $args
     * @param PKPRequest $request
     */
    public function importVersion($args = [], $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();

        $fileField = 'versionFile';
        if (isset($_FILES[$fileField]['tmp_name']) && is_uploaded_file($_FILES[$fileField]['tmp_name'])) {
            $rtAdmin = new JournalRTAdmin($journal->getId());
            $rtAdmin->importVersion($_FILES[$fileField]['tmp_name']);
        }
        $request->redirect(null, null, 'versions');
    }

    /**
     * Restore versions.
     * @param array $args
     * @param PKPRequest $request
     */
    public function restoreVersions($args = [], $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $rtAdmin = new JournalRTAdmin($journal->getId());
        $rtAdmin->restoreVersions();

        // If the journal RT was configured, change its state to
        // "disabled" because the RT version it was configured for
        // has now been deleted.
        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);
        if ($journalRt) {
            $journalRt->setVersion(null);
            $rtDao->updateJournalRT($journalRt);
        }

        $request->redirect(null, null, 'versions');
    }

    /**
     * Versions list.
     * @param array $args
     * @param PKPRequest $request
     */
    public function versions($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        $rtDao = DAORegistry::getDAO('RTDAO');
        $rangeInfo = $this->getRangeInfo('versions');

        $templateMgr = TemplateManager::getManager();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('versions', $rtDao->getVersions($journal->getId(), $rangeInfo));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.versions');
        $templateMgr->display('rtadmin/versions.tpl');
    }

    /**
     * Edit version.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editVersion($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());

        if (isset($version)) {
            import('classes.rt.ojs.form.VersionForm');
            $this->setupTemplate(true, $version);
            $versionForm = new VersionForm($versionId, $journal->getId());
            $versionForm->initData();
            $versionForm->display();
        } else {
            $request->redirect(null, null, 'versions');
        }
    }

    /**
     * Delete version.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteVersion($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;

        $rtDao->deleteVersion($versionId, $journal->getId());

        $request->redirect(null, null, 'versions');
    }

    /**
     * Save version.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveVersion($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());

        if (isset($version)) {
            import('classes.rt.ojs.form.VersionForm');
            $versionForm = new VersionForm($versionId, $journal->getId());
            $versionForm->readInputData();
            $versionForm->execute();
        }

        $request->redirect(null, null, 'versions');
    }
}
?>