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
            trigger_error(
                'Class ' . get_class($this) . ' uses deprecated constructor parent::' . get_class($this) . '(). Please refactor to parent::__construct().',
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }

    /**
     * Get name schecule task open access notification
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName() {
        return __('admin.scheduledTask.openAccessNotification');
    }

    /**
     * Send notification to users
     * @param mixed $users DAOResultFactory
     * @param mixed $journal Journal
     * @param mixed $issue Issue
     */
    public function sendNotification($users, $journal, $issue) {
        if ((int)$users->getCount() > 0) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('OPEN_ACCESS_NOTIFY', $journal->getPrimaryLocale(), false, $journal, false, true);

            $email->setSubject($email->getSubject());
            $email->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
            $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));

            $paramArray = [
                'journalName' => $journal->getLocalizedTitle(),
                'journalUrl' => $journal->getUrl(),
                'editorialContactSignature' => $journal->getSetting('contactName') . "\n" . $journal->getLocalizedTitle()
            ];
            $email->assignParams($paramArray);

            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $publishedArticles = $publishedArticleDao->getPublishedArticlesInSections($issue->getId());
            $mimeBoundary = '==boundary_' . md5((string)microtime());

            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('body', $email->getBody());
            $templateMgr->assign('templateSignature', $journal->getSetting('emailSignature'));
            $templateMgr->assign('mimeBoundary', $mimeBoundary);
            $templateMgr->assign('issue', $issue);
            $templateMgr->assign('publishedArticles', $publishedArticles);

            $email->addHeader('MIME-Version', '1.0');
            $email->setContentType('multipart/alternative; boundary="' . $mimeBoundary . '"');
            $email->setBody($templateMgr->fetch('subscription/openAccessNotifyEmail.tpl'));

            while (!$users->eof()) {
                $user = $users->next();
                $email->addBcc($user->getEmail(), $user->getFullName());
            }

            $email->send();
        }
    }

    /**
     * Send notifications for a specific journal based on date
     * @param mixed $journal Journal
     * @param mixed $curDate array
     */
    public function sendNotifications($journal, $curDate) {
        if ($journal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION && $journal->getSetting('enableOpenAccessNotification')) {
            $curYear = (int)$curDate['year'];
            $curMonth = (int)$curDate['month'];
            $curDay = (int)$curDate['day'];

            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issues = $issueDao->getPublishedIssues($journal->getId());

            while (!$issues->eof()) {
                $issue = $issues->next();

                $accessStatus = $issue->getAccessStatus();
                $openAccessDate = $issue->getOpenAccessDate();

                if ($accessStatus == ISSUE_ACCESS_SUBSCRIPTION && !empty($openAccessDate) && strtotime($openAccessDate) === mktime(0, 0, 0, $curMonth, $curDay, $curYear)) {
                    /** @var UserSettingsDAO $userSettingsDao */
                    $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
                    $users = $userSettingsDao->getUsersBySetting('openAccessNotification', true, 'bool', null, $journal->getId());
                    $this->sendNotification($users, $journal, $issue);
                }
            }
        }
    }

    /**
     * Execute action open access notification
     * @see ScheduledTask::executeActions()
     * @return boolean
     */
    public function executeActions() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);

        $todayDate = [
            'year' => (int)date('Y'),
            'month' => (int)date('n'),
            'day' => (int)date('j')
        ];

        while (!$journals->eof()) {
            $journal = $journals->next();
            $this->sendNotifications($journal, $todayDate);
            unset($journal);
        }

        $shortMonths = [2, 4, 6, 8, 10, 12];

        if ($todayDate['day'] === 1 && in_array($todayDate['month'] - 1, $shortMonths, true)) {
            $curDate = [
                'day' => 31,
                'month' => $todayDate['month'] - 1,
                'year' => ($todayDate['month'] - 1 === 12) ? $todayDate['year'] - 1 : $todayDate['year']
            ];

            $journals = $journalDao->getJournals(true);
            while (!$journals->eof()) {
                $journal = $journals->next();
                $this->sendNotifications($journal, $curDate);
                unset($journal);
            }
        }

        if ($todayDate['day'] === 1 && $todayDate['month'] === 3) {
            $curDate = [
                'day' => 30,
                'month' => 2,
                'year' => $todayDate['year']
            ];

            $journals = $journalDao->getJournals(true);
            while (!$journals->eof()) {
                $journal = $journals->next();
                $this->sendNotifications($journal, $curDate);
                unset($journal);
            }

            if (!checkdate(2, 29, $todayDate['year'])) {
                $curDate['day'] = 29;
                $journals = $journalDao->getJournals(true);

                while (!$journals->eof()) {
                    $journal = $journals->next();
                    $this->sendNotifications($journal, $curDate);
                    unset($journal);
                }
            }
        }

        return true;
    }

}
?>