<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/JournalSiteSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSiteSettingsForm
 * @ingroup admin_form
 *
 * @brief Form for site administrator to edit basic journal settings.
 * [WIZDAM EDITION] Refactored for PHP 8.x (Removed create_function)
 */

import('lib.pkp.classes.db.DBDataXMLParser');
import('lib.pkp.classes.form.Form');

class JournalSiteSettingsForm extends Form {

    /** @var int|null The ID of the journal being edited */
    public ?int $journalId = null;

    /**
     * Constructor.
     * @param int|null $journalId omit for a new journal
     */
    public function __construct($journalId = null) {
        parent::__construct('admin/journalSettings.tpl');

        $this->journalId = isset($journalId) ? (int) $journalId : null;

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'admin.journals.form.titleRequired'));
        $this->addCheck(new FormValidator($this, 'journalPath', 'required', 'admin.journals.form.pathRequired'));
        $this->addCheck(new FormValidatorAlphaNum($this, 'journalPath', 'required', 'admin.journals.form.pathAlphaNumeric'));
        
        // [WIZDAM] REPLACED create_function with Closure
        // Using $this context directly in closure is valid in PHP 5.4+ and 8.x
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'journalPath', 
            'required', 
            'admin.journals.form.pathExists', 
            function($path) {
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $oldPath = $this->getData('oldPath');
                return !$journalDao->journalExistsByPath($path) || ($oldPath !== null && $oldPath == $path);
            }
        ));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSiteSettingsForm($journalId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($journalId);
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('journalId', $this->journalId);
        $templateMgr->assign('helpTopicId', 'site.siteManagement');
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        if (isset($this->journalId)) {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($this->journalId);

            if ($journal != null) {
                $this->_data = [
                    'title' => $journal->getSetting('title', null), // Localized
                    'description' => $journal->getSetting('description', null), // Localized
                    'journalPath' => $journal->getPath(),
                    'enabled' => $journal->getEnabled()
                ];

            } else {
                $this->journalId = null;
            }
        }
        
        if (!isset($this->journalId)) {
            $this->_data = [
                'enabled' => 1
            ];
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['title', 'description', 'journalPath', 'enabled']);
        $this->setData('enabled', (int)$this->getData('enabled'));

        if (isset($this->journalId)) {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($this->journalId);
            if ($journal) {
                $this->setData('oldPath', $journal->getPath());
            }
        }
    }

    /**
     * Get a list of field names for which localized settings are used
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['title', 'description'];
    }

    /**
     * Save journal settings.
     * @param object|null $object
     */
    public function execute($object = null) {
        $site = Request::getSite();
        $journalDao = DAORegistry::getDAO('JournalDAO');

        $journal = null;
        if (isset($this->journalId)) {
            $journal = $journalDao->getById($this->journalId);
        }

        if (!isset($journal) || !$journal) {
            $journal = new Journal();
        }

        $journal->setPath($this->getData('journalPath'));
        $journal->setEnabled($this->getData('enabled'));

        $section = null;
        $isNewJournal = false;

        if ($journal->getId() != null) {
            $isNewJournal = false;
            $journalDao->updateJournal($journal);
        } else {
            $isNewJournal = true;

            // Give it a default primary locale
            $journal->setPrimaryLocale($site->getPrimaryLocale());

            $journalId = $journalDao->insertJournal($journal);
            $journalDao->resequenceJournals();

            // Make the site administrator the journal manager of newly created journals
            $sessionManager = SessionManager::getManager();
            $userSession = $sessionManager->getUserSession();
            if ($userSession->getUserId() != null && $userSession->getUserId() != 0 && !empty($journalId)) {
                $role = new Role();
                $role->setJournalId($journalId);
                $role->setUserId($userSession->getUserId());
                $role->setRoleId(ROLE_ID_JOURNAL_MANAGER);

                $roleDao = DAORegistry::getDAO('RoleDAO');
                $roleDao->insertRole($role);
            }

            // Make the file directories for the journal
            import('lib.pkp.classes.file.FileManager');
            $fileManager = new FileManager();
            $filesDir = Config::getVar('files', 'files_dir');
            $publicFilesDir = Config::getVar('files', 'public_files_dir');
            
            $fileManager->mkdir($filesDir . '/journals/' . $journalId);
            $fileManager->mkdir($filesDir . '/journals/' . $journalId . '/articles');
            $fileManager->mkdir($filesDir . '/journals/' . $journalId . '/issues');
            $fileManager->mkdir($publicFilesDir . '/journals/' . $journalId);

            // Install default journal settings
            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            $titles = $this->getData('title');
            AppLocale::requireComponents(LOCALE_COMPONENT_OJS_DEFAULT, LOCALE_COMPONENT_APPLICATION_COMMON);
            
            $journalSettingsDao->installSettings($journalId, 'registry/journalSettings.xml', [
                'indexUrl' => Request::getIndexUrl(),
                'journalPath' => $this->getData('journalPath'),
                'primaryLocale' => $site->getPrimaryLocale(),
                'journalName' => $titles[$site->getPrimaryLocale()]
            ]);

            // Install the default RT versions.
            import('classes.rt.ojs.JournalRTAdmin');
            $journalRtAdmin = new JournalRTAdmin($journalId);
            $journalRtAdmin->restoreVersions(false);

            // Create a default "Articles" section
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            $section = new Section();
            $section->setJournalId($journal->getId());
            $section->setTitle(__('section.default.title'), $journal->getPrimaryLocale());
            $section->setAbbrev(__('section.default.abbrev'), $journal->getPrimaryLocale());
            $section->setMetaIndexed(true);
            $section->setMetaReviewed(true);
            $section->setPolicy(__('section.default.policy'), $journal->getPrimaryLocale());
            $section->setEditorRestricted(false);
            $section->setHideTitle(false);
            $sectionDao->insertSection($section);
        }
        
        $journal->updateSetting('supportedLocales', $site->getSupportedLocales());
        $journal->updateSetting('title', $this->getData('title'), 'string', true);
        $journal->updateSetting('description', $this->getData('description'), 'string', true);

        // Make sure all plugins are loaded for settings preload
        PluginRegistry::loadAllPlugins();

        // [WIZDAM] HookRegistry dispatch - be careful with reference syntax
        // We pass references explicitly.
        HookRegistry::dispatch('JournalSiteSettingsForm::execute', [&$this, &$journal, &$section, &$isNewJournal]);
    }

}

?>