<?php
declare(strict_types=1);

/**
 * @file classes/tasks/SubscriptionExpiryReminder.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionExpiryReminder
 * @ingroup tasks
 *
 * @brief Class to perform automated reminders for subscription expiry.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

const SECONDS_PER_WEEK = 7 * 24 * 60 * 60;

class SubscriptionExpiryReminder extends ScheduledTask {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubscriptionExpiryReminder() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                'Class ' . get_class($this) . ' uses deprecated constructor parent::' . get_class($this) . '(). Please refactor to parent::__construct().',
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }

    /**
     * Get ScheduleTask name
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName() {
        return __('admin.scheduledTask.subscriptionExpiryReminder');
    }

    /**
     * Send reminder to subscriber.
     * @param object $subscription Subscription
     * @param object $journal Journal
     * @param string $emailKey string
     */
    public function sendReminder($subscription, $journal, $emailKey) {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');

        $journalName = $journal->getLocalizedTitle();
        $user = $userDao->getById($subscription->getUserId());
        if ($user === null) {
            return false;
        }

        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());

        $subscriptionName = (string) $journal->getSetting('subscriptionName');
        $subscriptionEmail = (string) $journal->getSetting('subscriptionEmail');
        $subscriptionPhone = (string) $journal->getSetting('subscriptionPhone');
        $subscriptionFax = (string) $journal->getSetting('subscriptionFax');
        $subscriptionMailingAddress = (string) $journal->getSetting('subscriptionMailingAddress');

        $subscriptionContactSignature = $subscriptionName;

        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_USER, LOCALE_COMPONENT_APPLICATION_COMMON);

        if ($subscriptionMailingAddress !== '') {
            $subscriptionContactSignature .= "\n" . $subscriptionMailingAddress;
        }
        if ($subscriptionPhone !== '') {
            $subscriptionContactSignature .= "\n" . __('user.phone') . ': ' . $subscriptionPhone;
        }
        if ($subscriptionFax !== '') {
            $subscriptionContactSignature .= "\n" . __('user.fax') . ': ' . $subscriptionFax;
        }

        $subscriptionContactSignature .= "\n" . __('user.email') . ': ' . $subscriptionEmail;

        $paramArray = [
            'subscriberName' => $user->getFullName(),
            'journalName' => $journalName,
            'subscriptionType' => $subscriptionType ? $subscriptionType->getSummaryString() : __('common.unknown'),
            'expiryDate' => $subscription->getDateEnd(),
            'username' => $user->getUsername(),
            'subscriptionContactSignature' => $subscriptionContactSignature
        ];

        import('classes.mail.MailTemplate');
        $mail = new MailTemplate($emailKey, $journal->getPrimaryLocale(), false, $journal, false, true);
        $mail->setFrom($subscriptionEmail, $subscriptionName);
        $mail->addRecipient($user->getEmail(), $user->getFullName());
        $mail->assignParams($paramArray);
        $mail->send();
        
        return true;
    }

    /**
     * Send reminders for a specific journal.
     * @param object $journal Journal
     */
    public function sendJournalReminders($journal) {
        if ((int) $journal->getSetting('publishingMode') === PUBLISHING_MODE_SUBSCRIPTION) {
            /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
            $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
            /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
            $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
            $journalId = (int) $journal->getId();

            // Check if expiry notification before weeks is enabled
            if ($journal->getSetting('enableSubscriptionExpiryReminderBeforeWeeks')) {
                $beforeWeeks = (int) $journal->getSetting('numWeeksBeforeSubscriptionExpiryReminder');
                $expiryTime = time() + (SECONDS_PER_WEEK * $beforeWeeks);

                $individualSubscriptions = $individualSubscriptionDao->getSubscriptionsToRemind($expiryTime, $journalId, SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY);
                $institutionalSubscriptions = $institutionalSubscriptionDao->getSubscriptionsToRemind($expiryTime, $journalId, SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY);

                while (!$individualSubscriptions->eof()) {
                    $subscription = $individualSubscriptions->next();
                    $this->sendReminder($subscription, $journal, 'SUBSCRIPTION_BEFORE_EXPIRY');
                    $individualSubscriptionDao->flagReminded($subscription->getId(), SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY);
                }

                while (!$institutionalSubscriptions->eof()) {
                    $subscription = $institutionalSubscriptions->next();
                    $this->sendReminder($subscription, $journal, 'SUBSCRIPTION_BEFORE_EXPIRY');
                    $institutionalSubscriptionDao->flagReminded($subscription->getId(), SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY);
                }
            }

            // Check if expiry notification after weeks is enabled
            if ($journal->getSetting('enableSubscriptionExpiryReminderAfterWeeks')) {
                $afterWeeks = (int) $journal->getSetting('numWeeksAfterSubscriptionExpiryReminder');
                $expiryTime = time() - (SECONDS_PER_WEEK * $afterWeeks);

                // Reusing the DAOs initialized above
                $individualSubscriptions = $individualSubscriptionDao->getSubscriptionsToRemind($expiryTime, $journalId, SUBSCRIPTION_REMINDER_FIELD_AFTER_EXPIRY);
                $institutionalSubscriptions = $institutionalSubscriptionDao->getSubscriptionsToRemind($expiryTime, $journalId, SUBSCRIPTION_REMINDER_FIELD_AFTER_EXPIRY);

                while (!$individualSubscriptions->eof()) {
                    $subscription = $individualSubscriptions->next();
                    $this->sendReminder($subscription, $journal, 'SUBSCRIPTION_AFTER_EXPIRY');
                    $individualSubscriptionDao->flagReminded($subscription->getId(), SUBSCRIPTION_REMINDER_FIELD_AFTER_EXPIRY);
                }

                while (!$institutionalSubscriptions->eof()) {
                    $subscription = $institutionalSubscriptions->next();
                    $this->sendReminder($subscription, $journal, 'SUBSCRIPTION_AFTER_EXPIRY');
                    $institutionalSubscriptionDao->flagReminded($subscription->getId(), SUBSCRIPTION_REMINDER_FIELD_AFTER_EXPIRY);
                }
            }
        }
    }

    /**
     * Execute actions scheduled task.
     * @see ScheduledTask::executeActions()
     * @return boolean
     */
    public function executeActions() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);

        while (!$journals->eof()) {
            $journal = $journals->next();
            $this->sendJournalReminders($journal);
        }

        return true;
    }

}
?>