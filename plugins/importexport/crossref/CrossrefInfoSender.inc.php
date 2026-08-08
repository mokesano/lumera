<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/CrossrefInfoSender.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossrefInfoSender
 * @ingroup plugins_importexport_crossref
 *
 * @brief Scheduled task to send article information to the ALM server.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class CrossrefInfoSender extends ScheduledTask {

    /** @var CrossRefExportPlugin|null */
    protected ?CrossRefExportPlugin $_plugin = null;

    /**
     * Constructor.
     * @param array $args
     */
    public function __construct($args) {
        PluginRegistry::loadCategory('importexport');
        /** @var CrossRefExportPlugin|null $plugin */
        $plugin = PluginRegistry::getPlugin('importexport', 'CrossRefExportPlugin');
        $this->_plugin = $plugin;

        if ($plugin instanceof CrossRefExportPlugin) {
            $plugin->addLocaleData();
        }

        parent::__construct($args);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param array $args
     */
    public function CrossrefInfoSender($args) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the name of this scheduled task.
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName(): string {
        return __('plugins.importexport.crossref.senderTask.name');
    }

    /**
     * Execute the actions of this scheduled task.
     * @see ScheduledTask::executeActions()
     * @return bool
     */
    public function executeActions(): bool {
        if (!$this->_plugin) {
            return false;
        }

        $plugin = $this->_plugin;
        $journals = $this->_getJournals();
        $request = Application::get()->getRequest();
        $errors = [];

        foreach ($journals as $journal) {
            $unregisteredArticles = $plugin->_getUnregisteredArticles($journal);
            $unregisteredArticlesIds = [];
            
            foreach ($unregisteredArticles as $articleData) {
                $article = $articleData['article'] ?? null;
                if ($article instanceof PublishedArticle && $plugin->canBeExported($article, $errors)) {
                    $unregisteredArticlesIds[(int) $article->getId()] = $article;
                }
            }

            $toBeDepositedIds = [];
            $notify = false;
            foreach ($unregisteredArticlesIds as $id => $article) {
                $currentStatus = $article->getData($plugin->getDepositStatusSettingName());
                $plugin->updateDepositStatus($request, $journal, $article);
                $newStatus = $article->getData($plugin->getDepositStatusSettingName());
                if (!$newStatus) {
                    $toBeDepositedIds[] = $id;
                }

                if (!$notify && $newStatus === CROSSREF_STATUS_FAILED && $currentStatus !== CROSSREF_STATUS_FAILED) {
                    $notify = true;
                }
            }

            if ($notify) {
                /** @var RoleDAO $roleDao */
                $roleDao = DAORegistry::getDAO('RoleDAO');
                $journalManagers = $roleDao->getUsersByRoleId(ROLE_ID_JOURNAL_MANAGER, (int) $journal->getId());
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                
                while ($journalManager = $journalManagers->next()) {
                    $notificationManager->createTrivialNotification(
                        (int) $journalManager->getId(), 
                        NOTIFICATION_TYPE_ERROR, 
                        ['contents' => __('plugins.importexport.crossref.notification.failed')]
                    );
                }
            }

            $autoRegistrationActive = $plugin->getSetting((int) $journal->getId(), 'automaticRegistration');
            import('lib.wizdam.classes.services.JournalOwnershipService');
            if (!$autoRegistrationActive && JournalOwnershipService::isOwnership($journal)) {
                import('lib.wizdam.classes.services.DoiCredentialService');
                $autoRegistrationActive = DoiCredentialService::resolveForJournal($journal)->isConfigured();
            }

            if (!empty($toBeDepositedIds) && $autoRegistrationActive) {
                $exportSpec = [DOI_EXPORT_ARTICLES => $toBeDepositedIds];
                $result = $plugin->registerObjects($request, $exportSpec, $journal);
                
                if ($result !== true) {
                    if (is_array($result)) {
                        foreach ($result as $error) {
                            if (is_array($error) && !empty($error)) {
                                $this->addExecutionLogEntry(
                                    __($error[0], ['param' => $error[1] ?? null]),
                                    SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                                );
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get all journals that meet the requirements to have
     * their articles DOIs sent to Crossref.
     * @see CrossrefExportPlugin::registerObjects()
     * @return array
     */
    public function _getJournals(): array {
        $plugin = $this->_plugin;
        if (!$plugin) {
            return [];
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalFactory = $journalDao->getJournals(true);

        $journals = [];
        while ($journal = $journalFactory->next()) {
            $journalId = (int) $journal->getId();

            $hasOwnCredentials = $plugin->getSetting($journalId, 'username')
                && $plugin->getSetting($journalId, 'password')
                && $plugin->getSetting($journalId, 'automaticRegistration');

            if (!$hasOwnCredentials) {
                import('lib.wizdam.classes.services.JournalOwnershipService');
                if (JournalOwnershipService::isPartnership($journal)) {
                    continue;
                }
                import('lib.wizdam.classes.services.DoiCredentialService');
                $doiCredentials = DoiCredentialService::resolveForJournal($journal);
                if (!$doiCredentials->isConfigured()) {
                    continue;
                }
            }

            $doiPrefix = null;
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journalId);
            if (is_array($pubIdPlugins) && isset($pubIdPlugins['DOIPubIdPlugin'])) {
                $doiPubIdPlugin = $pubIdPlugins['DOIPubIdPlugin'];
                if (!$doiPubIdPlugin->getSetting($journalId, 'enabled')) {
                    continue;
                }
                $doiPrefix = $doiPubIdPlugin->getSetting($journalId, 'doiPrefix');
            }

            if (!empty($doiPrefix)) {
                $journals[] = $journal;
            } else {
                $this->addExecutionLogEntry(
                    __('plugins.importexport.crossref.senderTask.warning.noDOIprefix', ['path' => $journal->getPath()]),
                    SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                );
            }
        }

        return $journals;
    }
    
}
?>