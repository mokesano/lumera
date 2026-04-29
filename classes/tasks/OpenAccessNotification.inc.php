<?php
declare(strict_types=1);

/**
 * @defgroup tasks
 */

/**
 * @file classes/tasks/OpenAccessNotification.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OpenAccessNotification
 * @ingroup tasks
 *
 * @brief Class to perform automated email notifications when an issue becomes open access.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class OpenAccessNotification extends ScheduledTask {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OpenAccessNotification() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::OpenAccessNotification(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName() {
        return __('admin.scheduledTask.openAccessNotification');
    }

    /**
     * Send notification to users
     * @param $users DAOResultFactory
     * @param $journal Journal
     * @param $issue Issue
     */
    public function sendNotification ($users, $journal, $issue) {
        if ($users->getCount() != 0) {

            import('classes.mail.MailTemplate');
            $email = new MailTemplate('OPEN_ACCESS_NOTIFY', $journal->getPrimaryLocale(), false, $journal, false, true);

            $email->setSubject($email->getSubject($journal->getPrimaryLocale()));
            $email->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
            $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));

            $paramArray = array(
                'journalName' => $journal->getLocalizedTitle(),
                'journalUrl' => $journal->getUrl(),
                'editorialContactSignature' => $journal->getSetting('contactName') . "\n" . $journal->getLocalizedTitle()
            );
            $email->assignParams($paramArray);

            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $publishedArticles = $publishedArticleDao->getPublishedArticlesInSections($issue->getId());
            $mimeBoundary = '==boundary_' . md5(microtime());

            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('body', $email->getBody($journal->getPrimaryLocale()));
            $templateMgr->assign('templateSignature', $journal->getSetting('emailSignature'));
            $templateMgr->assign('mimeBoundary', $mimeBoundary);
            
            // Modernisasi: assign_by_ref diganti assign
            $templateMgr->assign('issue', $issue);
            $templateMgr->assign('publishedArticles', $publishedArticles);

            $email->addHeader('MIME-Version', '1.0');
            $email->setContentType('multipart/alternative; boundary="'.$mimeBoundary.'"');
            $email->setBody($templateMgr->fetch('subscription/openAccessNotifyEmail.tpl'));

            while (!$users->eof()) {
                // Hapus '&'
                $user = $users->next();
                $email->addBcc($user->getEmail(), $user->getFullName());
            }

            $email->send();
        }
    }

    /**
     * Send notifications for a specific journal based on date
     * @param $journal Journal
     * @param $curDate array
     */
    public function sendNotifications ($journal, $curDate) {

        // Only send notifications if subscriptions and open access notifications are enabled
        if ($journal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION && $journal->getSetting('enableOpenAccessNotification')) {

            $curYear = $curDate['year'];
            $curMonth = $curDate['month'];
            $curDay = $curDate['day'];

            // Check if the current date corresponds to the open access date of any issues
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issues = $issueDao->getPublishedIssues($journal->getId());

            while (!$issues->eof()) {
                // Hapus '&'
                $issue = $issues->next();

                $accessStatus = $issue->getAccessStatus();
                $openAccessDate = $issue->getOpenAccessDate();

                if ($accessStatus == ISSUE_ACCESS_SUBSCRIPTION && !empty($openAccessDate) && strtotime($openAccessDate) == mktime(0, 0, 0, (int)$curMonth, (int)$curDay, (int)$curYear)) {
                    // Notify all users who have open access notification set for this journal
                    // UserSettingsDAO sekarang mengambil 'openAccessNotification' dari user_settings (bukan notification_subscription_settings)
                    // Pastikan DAO yang benar dipanggil jika struktur DB berubah. Di sini diasumsikan UserSettingsDAO benar.
                    $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
                    $users = $userSettingsDao->getUsersBySetting('openAccessNotification', true, 'bool', null, $journal->getId()); // Perbaikan parameter assocType/assocId
                    $this->sendNotification($users, $journal, $issue);
                }
            }
        }
    }

    /**
     * @see ScheduledTask::executeActions()
     * @return boolean
     */
    public function executeActions() {
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);

        $todayDate = array(
            'year' => date('Y'),
            'month' => date('n'),
            'day' => date('j')
        );

        while (!$journals->eof()) {
            // Hapus '&'
            $journal = $journals->next();

            // Send notifications based on current date
            $this->sendNotifications($journal, $todayDate);
            unset($journal);
        }

        // If it is the first day of a month but previous month had only
        // 30 days then simulate 31st day for open access dates that end on
        // that day.
        $shortMonths = array(2,4,6,8,10,12);

        if (($todayDate['day'] == 1) && in_array(($todayDate['month'] - 1), $shortMonths)) {

            $curDate['day'] = 31;
            $curDate['month'] = $todayDate['month'] - 1;

            if ($curDate['month'] == 12) {
                $curDate['year'] = $todayDate['year'] - 1;
            } else {
                $curDate['year'] = $todayDate['year'];
            }

            $journals = $journalDao->getJournals(true);

            while (!$journals->eof()) {
                // Hapus '&'
                $journal = $journals->next();

                // Send reminders for simulated 31st day of short month
                $this->sendNotifications($journal, $curDate);
                unset($journal);
            }
        }

        // If it is the first day of March, simulate 29th and 30th days for February
        // or just the 30th day in a leap year.
        if (($todayDate['day'] == 1) && ($todayDate['month'] == 3)) {

            $curDate['day'] = 30;
            $curDate['month'] = 2;
            $curDate['year'] = $todayDate['year'];

            $journals = $journalDao->getJournals(true);

            while (!$journals->eof()) {
                // Hapus '&'
                $journal = $journals->next();

                // Send reminders for simulated 30th day of February
                $this->sendNotifications($journal, $curDate);
                unset($journal);
            }

            // Check if it's a leap year
            if (date("L", mktime(0, 0, 0, 0, 0, (int)$curDate['year'])) != '1') {

                $curDate['day'] = 29;

                $journals = $journalDao->getJournals(true);

                while (!$journals->eof()) {
                    // Hapus '&'
                    $journal = $journals->next();

                    // Send reminders for simulated 29th day of February
                    $this->sendNotifications($journal, $curDate);
                    unset($journal);
                }
            }
        }

        return true;
    }
}

?>