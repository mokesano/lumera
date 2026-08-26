<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/classes/tasks/Depositor.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Depositor
 * @ingroup plugins_generic_pln_tasks
 *
 * @brief Class to perform automated deposits of PLN object.
 */

import('classes.file.JournalFileManager');
import('lib.pkp.classes.scheduledTask.ScheduledTask');

class Depositor extends ScheduledTask {

    /** @var object|null */
    protected $_plugin;

    /**
     * Constructor.
     * @param array $args
     */
    public function __construct($args) {
        PluginRegistry::loadCategory('generic');
        $this->_plugin = PluginRegistry::getPlugin('generic', PLN_PLUGIN_NAME);
        parent::__construct($args);
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param array $args
     */
    public function Depositor($args) {
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
     * Get the name of this task.
     * @copydoc ScheduledTask::getName()
     * @return string
     */
    public function getName() {
        return __('plugins.generic.pln.depositorTask.name');
    }

    /**
     * Perform the task.
     * @copydoc ScheduledTask::executeActions()
     * @return bool
     */
    public function executeActions() {
        if (!$this->_plugin) {
            return false;
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        
        // Get all journals
        $journals = $journalDao->getJournals(true);
        
        // For all journals
        while ($journal = $journals->next()) {
            $journalId = (int) $journal->getId();

            DBConnection::ensureConnection();
            
            // If the plugin isn't enabled for this journal, skip it
            if (!$this->_plugin->getSetting($journalId, 'enabled')) {
                continue;
            }

            $this->_plugin->registerDAOs();
            $this->_plugin->import('classes.Deposit');
            $this->_plugin->import('classes.DepositObject');
            $this->_plugin->import('classes.DepositPackage');
            
            $this->addExecutionLogEntry(
                __('plugins.generic.pln.notifications.processing_for', ['title' => $journal->getLocalizedTitle()]), 
                SCHEDULED_TASK_MESSAGE_TYPE_NOTICE
            );
            
            // Check to make sure curl is installed
            if (!$this->_plugin->curlInstalled()) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.curl_missing'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
                $this->_plugin->createJournalManagerNotification($journalId, PLN_PLUGIN_NOTIFICATION_TYPE_CURL_MISSING);
                continue;
            }
            
            // Check to make sure zip is installed
            if (!$this->_plugin->zipInstalled()) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.zip_missing'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
                $this->_plugin->createJournalManagerNotification($journalId, PLN_PLUGIN_NOTIFICATION_TYPE_ZIP_MISSING);
                continue;
            }
            
            if (!$this->_plugin->tarInstalled()) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.tar_missing'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
                $this->_plugin->createJournalManagerNotification($journalId, PLN_PLUGIN_NOTIFICATION_TYPE_TAR_MISSING);
                continue;
            }
                        
            $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.getting_servicedocument'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);
            
            // Get the sword service document
            $sdResult = $this->_plugin->getServiceDocument($journalId);
            
            // If for some reason we didn't get a valid response, skip this journal
            if ($sdResult !== PLN_PLUGIN_HTTP_STATUS_OK) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.http_error'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
                $this->_plugin->createJournalManagerNotification($journalId, PLN_PLUGIN_NOTIFICATION_TYPE_HTTP_ERROR);
                continue;
            }
            
            // If the pln isn't accepting deposits, skip this journal
            if (!$this->_plugin->getSetting($journalId, 'pln_accepting')) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.pln_not_accepting'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);
                continue;
            }
            
            // If the terms haven't been agreed to, skip transfer
            if (!$this->_plugin->termsAgreed($journalId)) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.terms_updated'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
                $this->_plugin->createJournalManagerNotification($journalId, PLN_PLUGIN_NOTIFICATION_TYPE_TERMS_UPDATED);
                continue;
            }
            
            // It's necessary that the journal have an issn set
            if (!$journal->getSetting('onlineIssn') && !$journal->getSetting('printIssn') && !$journal->getSetting('issn')) {
                $this->addExecutionLogEntry(__('plugins.generic.pln.notifications.issn_missing'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
                $this->_plugin->createJournalManagerNotification($journalId, PLN_PLUGIN_NOTIFICATION_TYPE_ISSN_MISSING);    
                continue;
            }
            
            // Update the statuses of existing deposits
            $this->addExecutionLogEntry(__('plugins.generic.pln.depositor.statusupdates'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);
            $this->_processStatusUpdates($journal);
            
            // Flag any deposits that have been updated and need to be rebuilt
            $this->addExecutionLogEntry(__('plugins.generic.pln.depositor.updatedcontent'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);            
            $this->_processHavingUpdatedContent($journal);
            
            // Create new deposits for new deposit objects
            $this->addExecutionLogEntry(__('plugins.generic.pln.depositor.newcontent'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);            
            $this->_processNewDepositObjects($journal);
            
            // Package any deposits that need packaging
            $this->addExecutionLogEntry(__('plugins.generic.pln.depositor.packagingdeposits'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);            
            $this->_processNeedPackaging($journal);

            // Transfer the deposit atom documents
            $this->addExecutionLogEntry(__('plugins.generic.pln.depositor.transferringdeposits'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);
            $this->_processNeedTransferring($journal);
        }
        
        return true;
    }

    /**
     * Go through existing deposits and fetch their status from the PLN.
     * @param Journal $journal
     */
    protected function _processStatusUpdates($journal) {
        /** @var DepositDAO $depositDao */
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $depositQueue = $depositDao->getNeedStagingStatusUpdate((int) $journal->getId());

        while ($deposit = $depositQueue->next()) {
            $depositPackage = new DepositPackage($deposit, $this);
            $depositPackage->updateDepositStatus();
        }
    }

    /**
     * Go through the deposits and mark them as updated if they have been.
     * @param Journal $journal
     */
    protected function _processHavingUpdatedContent($journal) {
        /** @var DepositObjectDAO $depositObjectDao */
        $depositObjectDao = DAORegistry::getDAO('DepositObjectDAO');
        $objectType = $this->_plugin->getSetting((int) $journal->getId(), 'object_type');
        $depositObjectDao->markHavingUpdatedContent((int) $journal->getId(), $objectType);
    }

    /**
     * If a deposit hasn't been transferred, transfer it.
     * @param Journal $journal
     */
    protected function _processNeedTransferring($journal) {
        /** @var DepositDAO $depositDao */
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $depositQueue = $depositDao->getNeedTransferring((int) $journal->getId());
        
        while ($deposit = $depositQueue->next()) {
            $depositPackage = new DepositPackage($deposit, $this);
            $depositPackage->transferDeposit();
        }
    }

    /**
     * Create packages for any deposits that don't have any or have been updated.
     * @param Journal $journal
     */
    protected function _processNeedPackaging($journal) {
        /** @var DepositDAO $depositDao */
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $depositQueue = $depositDao->getNeedPackaging((int) $journal->getId());
        $fileManager = new JournalFileManager($journal);
        $plnDir = $fileManager->filesDir . PLN_PLUGIN_ARCHIVE_FOLDER;
        
        // Make sure the pln work directory exists
        // TODO: use FileManager calls instead of PHP ones where possible
        if (!$fileManager->fileExists($plnDir, 'dir')) { 
            $fileManager->mkdirtree($plnDir); 
        }

        // Loop through all of the deposits that need packaging
        while ($deposit = $depositQueue->next()) {
            DBConnection::ensureConnection();
            $depositPackage = new DepositPackage($deposit, $this);
            $depositPackage->packageDeposit();
        }
    }

    /**
     * Create new deposits for deposit objects.
     * @param Journal $journal
     */
    protected function _processNewDepositObjects($journal) {
        $journalId = (int) $journal->getId();
        $objectType = $this->_plugin->getSetting($journalId, 'object_type');
        
        /** @var DepositDAO $depositDao */
        $depositDao = DAORegistry::getDAO('DepositDAO');
        /** @var DepositObjectDAO $depositObjectDao */
        $depositObjectDao = DAORegistry::getDAO('DepositObjectDAO');
        $depositObjectDao->createNew($journalId, $objectType);
        
        // Retrieve all deposit objects that don't belong to a deposit
        $newObjects = $depositObjectDao->getNew($journalId);

        switch ($objectType) {
            case PLN_PLUGIN_DEPOSIT_OBJECT_ARTICLE:
                // Get the new object threshold per deposit and split the objects into arrays of that size
                $objectThreshold = (int) $this->_plugin->getSetting($journalId, 'object_threshold');
                
                foreach (array_chunk($newObjects->toArray(), $objectThreshold) as $newObjectArray) {
                    // Only create a deposit for the complete threshold, we'll worry about the remainder another day
                    if (count($newObjectArray) === $objectThreshold) {
                        // Create a new deposit
                        $newDeposit = new Deposit($this->_plugin->newUUID());
                        $newDeposit->setJournalId($journalId);
                        $depositDao->insertDeposit($newDeposit);
                        
                        // Add each object to the deposit
                        foreach ($newObjectArray as $newObject) {
                            $newObject->setDepositId($newDeposit->getId());
                            $depositObjectDao->updateDepositObject($newObject);
                        }
                    }
                }
                break;
                
            case PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE:
                // Create a new deposit for each deposit object
                while ($newObject = $newObjects->next()) {
                    $newDeposit = new Deposit($this->_plugin->newUUID());
                    $newDeposit->setJournalId($journalId);
                    $depositDao->insertDeposit($newDeposit);
                    $newObject->setDepositId($newDeposit->getId());
                    $depositObjectDao->updateDepositObject($newObject);
                }
                break;
                
            default:
                break;
        }
    }

}
?>