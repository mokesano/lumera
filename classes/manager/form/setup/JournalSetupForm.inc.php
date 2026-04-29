<?php
declare(strict_types=1);

/**
 * @defgroup manager_form_setup
 */

/**
 * @file classes/manager/form/setup/JournalSetupForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSetupForm
 * @ingroup manager_form_setup
 *
 * @brief Base class for journal setup forms.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class JournalSetupForm extends Form {
    /** @var int The step number */
    public $step;

    /** @var array Associative array of settings */
    public $settings;

    /**
     * Constructor.
     * @param int $step the step number
     * @param array $settings an associative array with the setting names as keys and associated types as values
     */
    public function __construct($step, $settings) {
        parent::__construct(sprintf('manager/setup/step%d.tpl', $step));
        $this->addCheck(new FormValidatorPost($this));
        $this->step = (int) $step;
        $this->settings = $settings;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSetupForm($step, $settings) {
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
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('setupStep', $this->step);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.setup');
        $templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
        parent::display($request, $template);
    }

    /**
     * Initialize data from current settings.
     */
    public function initData() {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $this->_data = $journal->getSettings();
    }

    /**
     * Read user input.
     */
    public function readInputData() {
        $this->readUserVars(array_keys($this->settings));
    }

    /**
     * Save modified settings.
     * @param object|null $object
     */
    public function execute($object = null) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        foreach ($this->_data as $name => $value) {
            if (isset($this->settings[$name])) {
                $isLocalized = in_array($name, $this->getLocaleFieldNames());
                $settingsDao->updateSetting(
                    $journal->getId(),
                    $name,
                    $value,
                    $this->settings[$name],
                    $isLocalized
                );
            }
        }
    }
}
?>