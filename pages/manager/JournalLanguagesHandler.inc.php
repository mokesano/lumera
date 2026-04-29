<?php
declare(strict_types=1);

/**
 * @file pages/manager/JournalLanguagesHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalLanguagesHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for changing journal language settings.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class JournalLanguagesHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalLanguagesHandler() {
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
     * Display form to edit language settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function languages($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.manager.form.LanguageSettingsForm');

        $settingsForm = new LanguageSettingsForm();
        $settingsForm->initData();
        $settingsForm->display();
    }

    /**
     * Save changes to language settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveLanguageSettings($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.manager.form.LanguageSettingsForm');

        $settingsForm = new LanguageSettingsForm();
        $settingsForm->readInputData();

        if ($settingsForm->validate()) {
            $settingsForm->execute();
            $user = $request->getUser();
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification($user->getId());
            $request->redirect(null, null, 'index');
        } else {
            $settingsForm->display();
        }
    }

    /**
     * Reload the default localized settings for the journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function reloadLocalizedDefaultSettings($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // make sure the locale is valid
        $localeToLoad = trim((string) $request->getUserVar('localeToLoad'));
        
        // [WIZDAM FIX] Fixed undefined variable $locale -> $localeToLoad
        if (!AppLocale::isLocaleValid($localeToLoad)) {
            $request->redirect(null, null, 'languages');
        }

        $this->validate();
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettingsDao->reloadLocalizedDefaultSettings(
            $journal->getId(), 
            'registry/journalSettings.xml',
            [
                'indexUrl' => $request->getIndexUrl(),
                'journalPath' => $journal->getData('path'),
                'primaryLocale' => $journal->getPrimaryLocale(),
                'journalName' => $journal->getTitle($journal->getPrimaryLocale())
            ],
            $localeToLoad
        );

        $user = $request->getUser();

        // Display a notification
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $notificationManager->createTrivialNotification($user->getId());
        $request->redirect(null, null, 'languages');
    }
}
?>