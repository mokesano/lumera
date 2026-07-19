<?php
declare(strict_types=1);

/**
 * @file classes/tasks/ReviewReminder.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewReminder
 * @ingroup tasks
 *
 * @brief Class to perform automated reminders for reviewers.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

define('REVIEW_REMIND_AUTO', 'REVIEW_REMIND_AUTO');
define('REVIEW_REQUEST_REMIND_AUTO', 'REVIEW_REQUEST_REMIND_AUTO');

class ReviewReminder extends ScheduledTask {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewReminder() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::ReviewReminder(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct();
    }

    /**
     * Get schedule task name. 
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName() {
        return __('admin.scheduledTask.reviewReminder');
    }

    /**
     * Send reminder to reviewer
     * @param object $reviewAssignment ReviewAssignment
     * @param object $article Article
     * @param object $journal Journal
     * @param string $reminderType
     * @return bool
     */
    public function sendReminder($reviewAssignment, $article, $journal, $reminderType = REVIEW_REMIND_AUTO) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $reviewId = $reviewAssignment->getId();

        $reviewer = $userDao->getUser($reviewAssignment->getReviewerId());
        if (!$reviewer) {
            return false;
        }

        import('classes.mail.ArticleMailTemplate');

        $reviewerAccessKeysEnabled = (bool) $journal->getSetting('reviewerAccessKeysEnabled');

        $email = new ArticleMailTemplate($article, $reviewerAccessKeysEnabled ? $reminderType . '_ONECLICK' : $reminderType, $journal->getPrimaryLocale(), false, $journal, false, true);
        $email->setJournal($journal);
        $email->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
        $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
        $email->setSubject($email->getSubject());
        $email->setBody($email->getBody());

        $urlParams = [];
        if ($reviewerAccessKeysEnabled) {
            import('lib.pkp.classes.security.AccessKeyManager');
            $accessKeyManager = new AccessKeyManager();

            $keyLifetime = ((int) $journal->getSetting('numWeeksPerReview') + 4) * 7;
            $urlParams['key'] = $accessKeyManager->createKey('ReviewerContext', $reviewer->getId(), $reviewId, $keyLifetime);
        }
        $submissionReviewUrl = Request::url($journal->getPath(), 'reviewer', 'submission', $reviewId, $urlParams);

        // Format the review due date
        $reviewDueDateTimestamp = strtotime((string) $reviewAssignment->getDateDue());
        $dateFormatShort = Config::getVar('general', 'date_format_short');
        if ($reviewDueDateTimestamp === false) {
            $reviewDueDate = '_____';
        } else {
            $reviewDueDate = date($dateFormatShort, $reviewDueDateTimestamp);
        }

        $paramArray = [
            'reviewerName' => $reviewer->getFullName(),
            'reviewerUsername' => $reviewer->getUsername(),
            'journalUrl' => $journal->getUrl(),
            'reviewerPassword' => (string) $reviewer->getPassword(),
            'reviewDueDate' => $reviewDueDate,
            'weekLaterDate' => date($dateFormatShort, strtotime('+1 week')),
            'editorialContactSignature' => $journal->getSetting('contactName') . "\n" . $journal->getLocalizedTitle(),
            'passwordResetUrl' => Request::url(
                $journal->getPath(), 
                'login', 
                'resetPassword', 
                $reviewer->getUsername(), 
                ['confirm' => Validation::generatePasswordResetHash($reviewer->getId())]
            ),
            'submissionReviewUrl' => $submissionReviewUrl,
            'abstractTermIfEnabled' => ($article->getLocalizedAbstract() === '' ? '' : __('article.abstract')),
        ];
        $email->assignParams($paramArray);

        $email->send();

        $reviewAssignment->setDateReminded(Core::getCurrentDate());
        $reviewAssignment->setReminderWasAutomatic(1);
        $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
        
        return true;
    }

    /**
     * Execute actions scheduled task.
     * @see ScheduledTask::executeActions()
     * @return bool
     */
    public function executeActions() {
        $article = null;
        $journal = null;

        $inviteReminderEnabled = 0;
        $submitReminderEnabled = 0;
        $inviteReminderDays = 0;
        $submitReminderDays = 0;

        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');

        $incompleteAssignments = $reviewAssignmentDao->getIncompleteReviewAssignments();
        foreach ($incompleteAssignments as $reviewAssignment) {
            // Fetch the Article and the Journal if necessary.
            if (!$article || $article->getId() !== (int) $reviewAssignment->getSubmissionId()) {
                $article = $articleDao->getArticle((int) $reviewAssignment->getSubmissionId());
                // Avoid review assignments without article in database anymore.
                if (!$article) {
                    continue;
                }

                if (!$journal || $journal->getId() !== (int) $article->getJournalId()) {
                    $journal = $journalDao->getById((int) $article->getJournalId());

                    $inviteReminderEnabled = (int) $journal->getSetting('remindForInvite');
                    $submitReminderEnabled = (int) $journal->getSetting('remindForSubmit');
                    $inviteReminderDays = (int) $journal->getSetting('numDaysBeforeInviteReminder');
                    $submitReminderDays = (int) $journal->getSetting('numDaysBeforeSubmitReminder');
                }
            }

            if ($article->getStatus() !== STATUS_QUEUED) {
                continue;
            }

            $reminderType = false;

            if ($inviteReminderEnabled === 1 && $reviewAssignment->getDateConfirmed() === null) {
                $checkDate = strtotime((string) $reviewAssignment->getDateNotified());
                if ($checkDate !== false && (time() - $checkDate > 60 * 60 * 24 * $inviteReminderDays)) {
                    $reminderType = REVIEW_REQUEST_REMIND_AUTO;
                }
            }
            
            if ($submitReminderEnabled === 1 && $reviewAssignment->getDateDue() !== null) {
                $checkDate = strtotime((string) $reviewAssignment->getDateDue());
                if ($checkDate !== false && (time() - $checkDate > 60 * 60 * 24 * $submitReminderDays)) {
                    $reminderType = REVIEW_REMIND_AUTO;
                }
            }

            if ($reviewAssignment->getDateReminded() !== null) {
                $reminderType = false;
            }

            if ($reminderType) {
                $this->sendReminder($reviewAssignment, $article, $journal, $reminderType);
            }
        }

        return true;
    }
    
}
?>