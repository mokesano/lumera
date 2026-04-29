<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/datacite/DataciteInfoSender.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataciteInfoSender
 * @ingroup plugins_importexport_datacite
 *
 * @brief Scheduled task to register DOIs to the DataCite server.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');


class DataciteInfoSender extends ScheduledTask {
    /** @var DataciteExportPlugin */
    public $_plugin;

    /**
     * Constructor.
     * @param $args array task arguments
     */
    public function __construct($args) {
        PluginRegistry::loadCategory('importexport');
        $plugin = PluginRegistry::getPlugin('importexport', 'DataciteExportPlugin'); /* @var $plugin DataciteExportPlugin */
        $this->_plugin = $plugin;

        if ($plugin instanceof DataciteExportPlugin) {
            $plugin->addLocaleData();
        }

        parent::__construct($args);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataciteInfoSender($args) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the name of this task.
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName() {
        return __('plugins.importexport.datacite.senderTask.name');
    }

    /**
     * Execute the task.
     * @see ScheduledTask::executeActions()
     * @return boolean True if the task executed successfully, false if not.
     */
    public function executeActions() {
        if (!$this->_plugin) return false;

        $plugin = $this->_plugin;

        $journals = $this->_getJournals();
        $request = Application::getRequest();

        foreach ($journals as $journal) {
            $unregisteredIssues = $plugin->_getUnregisteredIssues($journal);
            $unregisteredArticles = $plugin->_getUnregisteredArticles($journal);
            $unregisteredGalleys = $plugin->_getUnregisteredGalleys($journal);
            $unregisteredSuppFiles = $plugin->_getUnregisteredSuppFiles($journal);
            $errors = [];

            $unregisteredIssueIds = [];
            foreach ($unregisteredIssues as $issue) {
                if ($plugin->canBeExported($issue, $errors)) {
                    $unregisteredIssueIds[] = $issue->getId();
                }
            }
            $unregisteredArticlesIds = [];
            foreach ($unregisteredArticles as $articleData) {
                $article = $articleData['article'];
                if ($article instanceof PublishedArticle && $plugin->canBeExported($article, $errors)) {
                    $unregisteredArticlesIds[] = $article->getId();
                }
            }
            $unregisteredGalleyIds = [];
            foreach ($unregisteredGalleys as $galleyData) {
                $galley = $galleyData['galley'];
                if ($plugin->canBeExported($galley, $errors)) {
                    $unregisteredGalleyIds[] = $galley->getId();
                }
            }
            $unregisteredSuppFileIds = [];
            foreach ($unregisteredSuppFiles as $suppFileData) {
                $suppFile = $suppFileData['suppFile'];
                if ($plugin->canBeExported($suppFile, $errors)) {
                    $unregisteredSuppFileIds[$suppFile->getId()] = $suppFile->getId();
                }
            }

            // If there are unregistered DOIs and we want automatic deposits
            $exportSpec = [];
            $register = false;
            if (count($unregisteredIssueIds)) {
                $exportSpec[DOI_EXPORT_ISSUES] = $unregisteredIssueIds;
                $register = true;
            }
            if (count($unregisteredArticlesIds)) {
                $exportSpec[DOI_EXPORT_ARTICLES] = $unregisteredArticlesIds;
                $register = true;
            }
            if (count($unregisteredGalleyIds)) {
                $exportSpec[DOI_EXPORT_GALLEYS] = $unregisteredGalleyIds;
                $register = true;
            }
            if (count($unregisteredSuppFileIds)) {
                $exportSpec[DOI_EXPORT_SUPPFILES] = $unregisteredSuppFileIds;
                $register = true;
            }
            if ($register) {
                $result = $plugin->registerObjects($request, $exportSpec, $journal);
                if ($result !== true) {
                    if (is_array($result)) {
                        foreach($result as $error) {
                            assert(is_array($error) && count($error) >= 1);
                            $this->addExecutionLogEntry(
                                __($error[0], ['param' => (isset($error[1]) ? $error[1] : null)]),
                                SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                            );
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get all journals that meet the requirements to have
     * their articles DOIs sent to DataCite.
     * @return array
     */
    public function _getJournals() {
        $plugin = $this->_plugin;
        $journalDao = DAORegistry::getDAO('JournalDAO'); /* @var $journalDao JournalDAO */
        $journalFactory = $journalDao->getJournals(true);

        $journals = [];
        while($journal = $journalFactory->next()) {
            $journalId = $journal->getId();
            if (!$plugin->getSetting($journalId, 'username') || !$plugin->getSetting($journalId, 'password') || !$plugin->getSetting($journalId, 'automaticRegistration')) continue;

            $doiPrefix = null;
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journalId);
            if (isset($pubIdPlugins['DOIPubIdPlugin'])) {
                $doiPubIdPlugin = $pubIdPlugins['DOIPubIdPlugin'];
                $doiPrefix = $doiPubIdPlugin->getSetting($journalId, 'doiPrefix');
            }

            if ($doiPrefix) {
                $journals[] = $journal;
            } else {
                $this->addExecutionLogEntry(
                    __('plugins.importexport.common.senderTask.warning.noDOIprefix', ['path' => $journal->getPath()]),
                    SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                );
            }
            unset($journal);
        }

        return $journals;
    }
}
?>