<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminJournalHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminJournalHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for journal management in site administration.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.admin.AdminHandler');

class AdminJournalHandler extends AdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AdminJournalHandler() {
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
     * Display a list of the journals hosted on the site.
     */
    public function journals() {
        $this->validate();
        $this->setupTemplate();

        $rangeInfo = $this->getRangeInfo('journals');

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(false, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        $templateMgr->assign('journals', $journals);
        $templateMgr->assign('helpTopicId', 'site.siteManagement');
        $templateMgr->display('admin/journals.tpl');
    }

    /**
     * Display form to create a new journal.
     */
    public function createJournal() {
        $this->editJournal();
    }

    /**
     * Display form to create/edit a journal.
     * @param array $args optional, if set the first parameter is the ID of the journal to edit
     */
    public function editJournal($args = []) {
        $this->validate();
        $this->setupTemplate();

        import('classes.admin.form.JournalSiteSettingsForm');
        $settingsForm = new JournalSiteSettingsForm(!isset($args) || empty($args) ? null : $args[0]);

        if ($settingsForm->isLocaleResubmit()) {
            $settingsForm->readInputData();
        } else {
            $settingsForm->initData();
        }
        $settingsForm->display();
    }

    /**
     * Save changes to a journal's settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateJournal($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.admin.form.JournalSiteSettingsForm');

        $journalId = (int) $request->getUserVar('journalId');
        $settingsForm = new JournalSiteSettingsForm($journalId);

        $settingsForm->readInputData();

        if ($settingsForm->validate()) {
            PluginRegistry::loadCategory('blocks');
            $settingsForm->execute();

            $user = $request->getUser();

            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification($user->getId());
            $request->redirect(null, null, 'journals');

        } else {
            $settingsForm->display();
        }
    }

    /**
     * Delete a journal.
     * @param array $args first parameter is the ID of the journal to delete
     * @param PKPRequest $request
     */
    public function deleteJournal($args, $request) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journalDao = DAORegistry::getDAO('JournalDAO');

        if (isset($args) && !empty($args) && !empty($args[0])) {
            $journalId = (int) $args[0]; // [WIZDAM] Cast to int
            if ($journalDao->deleteJournalById($journalId)) {
                // Delete journal file tree
                // FIXME move this somewhere better.
                import('lib.pkp.classes.file.FileManager');
                $fileManager = new FileManager();

                $journalPath = Config::getVar('files', 'files_dir') . '/journals/' . $journalId;
                $fileManager->rmtree($journalPath);

                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $publicFileManager->rmtree($publicFileManager->getJournalFilesPath($journalId));
            }
        }

        $request->redirect(null, null, 'journals');
    }

    /**
     * Change the sequence of a journal on the site index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveJournal($args, $request) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalId = (int) $request->getUserVar('id');
        $journal = $journalDao->getById($journalId);

        if ($journal != null) {
            $direction = (int) $request->getUserVar('d'); // [WIZDAM] Security: cast to int, but logic expects string/direction char?
            // Actually request->getUserVar('d') usually returns 'u' or 'd'.
            // Re-checking logic: ($direction == 'u' ? -1.5 : 1.5). 
            // If we cast to int, 'u' becomes 0.
            // CORRECT FIX: Do not cast 'd' to int immediately if it holds char.
            $directionRaw = trim((string) $request->getUserVar('d'));

            if (!empty($directionRaw)) {
                // moving with up or down arrow
                $journal->setSequence($journal->getSequence() + ($directionRaw == 'u' ? -1.5 : 1.5));

            } else {
                // Dragging and dropping onto another journal
                $prevId = (int) $request->getUserVar('prevId');
                if ($prevId == 0) { // null or 0 from cast
                    $prevSeq = 0;
                } else {
                    $prevJournal = $journalDao->getById($prevId);
                    $prevSeq = $prevJournal ? $prevJournal->getSequence() : 0;
                }

                $journal->setSequence($prevSeq + .5);
            }

            $journalDao->updateJournal($journal);
            $journalDao->resequenceJournals();

            // Moving up or down with the arrows requires a page reload.
            // In the case of a drag and drop move, the display has been
            // updated on the client side, so no reload is necessary.
            if (!empty($directionRaw)) {
                $request->redirect(null, null, 'journals');
            }
        }
    }

    /**
     * Set up the template.
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate(true);
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER);
    }
}
?>