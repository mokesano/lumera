<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/tasks/ObjectsForReviewReminder.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewReminder
 * @ingroup plugins_generic_objectsForReview_tasks
 *
 * @brief Class to perform automated reminders for object reviewers.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class ObjectsForReviewReminder extends ScheduledTask {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ObjectsForReviewReminder() {
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
    public function getName() {
        return __('plugins.generic.objectsForReview.reminderTask.name');
    }

    /**
     * Send email to object for review author.
     * @param ObjectForReviewAssignment $ofrAssignment
     * @param Journal $journal
     * @param string $emailKey
     * @return void
     */
    public function sendReminder($ofrAssignment, $journal, $emailKey) {
        $journalId = (int) $journal->getId();

        $author = $ofrAssignment->getUser();
        $objectForReview = $ofrAssignment->getObjectForReview();
        $editor = $objectForReview->getEditor();

        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();

        $paramArray = [
            'authorName' => strip_tags((string) $author->getFullName()),
            'objectForReviewTitle' => '"' . strip_tags((string) $objectForReview->getTitle()) . '"',
            'objectForReviewDueDate' => date('l, F j, Y', strtotime((string) $ofrAssignment->getDateDue())),
            'submissionUrl' => $request->url($journal->getPath(), 'submission', 'submit'),
            'editorialContactSignature' => strip_tags((string) $editor->getContactSignature())
        ];

        import('classes.mail.MailTemplate');
        $mail = new MailTemplate($emailKey);
        $mail->setFrom((string) $editor->getEmail(), (string) $editor->getFullName());
        $mail->addRecipient((string) $author->getEmail(), (string) $author->getFullName());
        
        // MailTemplate handles locale internally based on the journal context.
        $mail->setSubject($mail->getSubject());
        $mail->setBody($mail->getBody());
        
        $mail->assignParams($paramArray);
        $mail->send();

        $currentDate = Core::getCurrentDate();
        
        // [WIZDAM FIX] Use specific date setters based on the email key, 
        // as generic 'setDateReminded' does not exist in ObjectForReviewAssignment.
        if ($emailKey === 'OFR_REVIEW_REMINDER') {
            $ofrAssignment->setDateRemindedBefore($currentDate);
        } elseif ($emailKey === 'OFR_REVIEW_REMINDER_LATE') {
            $ofrAssignment->setDateRemindedAfter($currentDate);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignmentDao->updateObject($ofrAssignment);
    }

    /**
     * Cron callback to send reminders for object for review assignments.
     * @see ScheduledTask::executeActions()
     * @return bool
     */
    public function executeActions() {
        // [WIZDAM RESOURCE] Prevent timeout on heavy cron jobs
        set_time_limit(0);

        // [WIZDAM FIX] Explicit type annotation to resolve "Undefined method 'registerDAOs'"
        /** @var ObjectsForReviewPlugin $ofrPlugin */
        $ofrPlugin = PluginRegistry::getPlugin('generic', 'objectsforreviewplugin');
        
        if ($ofrPlugin) {
            $ofrPluginName = $ofrPlugin->getName();
            
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournals(true);
            
            $ofrPlugin->registerDAOs();
            
            /** @var PluginSettingsDAO $pluginSettingsDao */
            $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            
            while ($journal = $journals->next()) {
                $journalId = (int) $journal->getId();
                
                $pluginEnabled = $pluginSettingsDao->getSetting($journalId, $ofrPluginName, 'enabled');
                if ($pluginEnabled) {
                    $enableDueReminderBefore = $pluginSettingsDao->getSetting($journalId, $ofrPluginName, 'enableDueReminderBefore');
                    $enableDueReminderAfter = $pluginSettingsDao->getSetting($journalId, $ofrPluginName, 'enableDueReminderAfter');
                    $beforeDays = (int) $pluginSettingsDao->getSetting($journalId, $ofrPluginName, 'numDaysBeforeDueReminder');
                    $afterDays = (int) $pluginSettingsDao->getSetting($journalId, $ofrPluginName, 'numDaysAfterDueReminder');
                    
                    if (($enableDueReminderBefore && $beforeDays > 0) || ($enableDueReminderAfter && $afterDays > 0)) {
                        $incompleteAssignments = $ofrAssignmentDao->getIncompleteAssignmentsByContextId($journalId);
                        
                        foreach ($incompleteAssignments as $incompleteAssignment) {
                            $dateDue = $incompleteAssignment->getDateDue();
                            if ($dateDue !== null) {
                                $dueDate = strtotime((string) $dateDue);
                                
                                $dateRemindedBefore = $incompleteAssignment->getDateRemindedBefore();
                                $dateRemindedAfter = $incompleteAssignment->getDateRemindedAfter();
                                
                                // Remind before:
                                // If there hasn't been any such reminder, this option is set and due date is in the future
                                if ($dateRemindedBefore === null && $enableDueReminderBefore == 1 && time() < $dueDate) {
                                    $nowToDueDate = $dueDate - time();
                                    if ($nowToDueDate < (60 * 60 * 24 * $beforeDays)) {
                                        $this->sendReminder($incompleteAssignment, $journal, 'OFR_REVIEW_REMINDER');
                                    }
                                }
                                
                                // Remind after:
                                // If there hasn't been any such reminder, this option is set and due date is in the past
                                if ($dateRemindedAfter === null && $enableDueReminderAfter == 1 && time() > $dueDate) {
                                    $dueDateToNow = time() - $dueDate;
                                    if ($dueDateToNow > (60 * 60 * 24 * $afterDays)) {
                                        $this->sendReminder($incompleteAssignment, $journal, 'OFR_REVIEW_REMINDER_LATE');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return true;
        }
        
        return false;
    }
    
}
?>