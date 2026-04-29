<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/LanguageSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageSettingsForm
 * @ingroup manager_form
 *
 * @brief Form for modifying journal language settings.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class LanguageSettingsForm extends Form {

    /** @var array the setting names */
    public $settings;

    /** @var array set of locales available for journal use */
    public $availableLocales;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('manager/languageSettings.tpl');

        $this->settings = [
            'supportedLocales' => 'object',
            'supportedSubmissionLocales' => 'object',
            'supportedFormLocales' => 'object'
        ];

        // [WIZDAM] Request Singleton
        $site = Application::get()->getRequest()->getSite();
        $this->availableLocales = $site->getSupportedLocales();

        // Validation checks for this form
        
        // Check if locale is valid via AppLocale
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'primaryLocale', 
            'required', 
            'manager.languages.form.primaryLocaleRequired', 
            ['AppLocale', 'isLocaleValid']
        ));

        // Check if locale is in available locales using Closure (replaces create_function)
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'primaryLocale', 
            'required', 
            'manager.languages.form.primaryLocaleRequired', 
            function($locale) {
                return in_array($locale, $this->availableLocales);
            }
        ));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LanguageSettingsForm() {
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
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $site = Application::get()->getRequest()->getSite();
        
        $templateMgr->assign('availableLocales', $site->getSupportedLocaleNames());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.languages');
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        $journal = Application::get()->getRequest()->getJournal();
        
        foreach ($this->settings as $settingName => $settingType) {
            $this->_data[$settingName] = $journal->getSetting($settingName);
        }

        $this->setData('primaryLocale', $journal->getPrimaryLocale());

        foreach (['supportedFormLocales', 'supportedSubmissionLocales', 'supportedLocales'] as $name) {
            if ($this->getData($name) == null || !is_array($this->getData($name))) {
                $this->setData($name, []);
            }
        }
        
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $vars = array_keys($this->settings);
        $vars[] = 'primaryLocale';
        $this->readUserVars($vars);

        foreach (['supportedFormLocales', 'supportedSubmissionLocales', 'supportedLocales'] as $name) {
            if ($this->getData($name) == null || !is_array($this->getData($name))) {
                $this->setData($name, []);
            }
        }
    }

    /**
     * Save modified settings.
     * @param mixed $object
     */
    public function execute($object = null) {
        $journal = Application::get()->getRequest()->getJournal();
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        // Verify additional locales
        foreach (['supportedLocales', 'supportedSubmissionLocales', 'supportedFormLocales'] as $name) {
            $$name = [];
            $data = $this->getData($name);
            if (is_array($data)) {
                foreach ($data as $locale) {
                    if (AppLocale::isLocaleValid($locale) && in_array($locale, $this->availableLocales)) {
                        array_push($$name, $locale);
                    }
                }
            }
        }

        $primaryLocale = $this->getData('primaryLocale');

        // Make sure at least the primary locale is chosen as available
        if ($primaryLocale != null && !empty($primaryLocale)) {
            foreach (['supportedLocales', 'supportedSubmissionLocales', 'supportedFormLocales'] as $name) {
                if (!in_array($primaryLocale, $$name)) {
                    array_push($$name, $primaryLocale);
                }
            }
        }
        
        // Variable variables populated above ($supportedLocales, etc.)
        $this->setData('supportedLocales', $supportedLocales);
        $this->setData('supportedSubmissionLocales', $supportedSubmissionLocales);
        $this->setData('supportedFormLocales', $supportedFormLocales);
        
        parent::execute();

        foreach ($this->_data as $name => $value) {
            if (!in_array($name, array_keys($this->settings))) continue;
            $settingsDao->updateSetting(
                $journal->getId(),
                $name,
                $value,
                $this->settings[$name]
            );
        }

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal->setPrimaryLocale($this->getData('primaryLocale'));
        $journalDao->updateJournal($journal);
    }
}
?>