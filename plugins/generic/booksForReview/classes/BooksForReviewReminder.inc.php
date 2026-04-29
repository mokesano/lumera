<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/classes/BooksForReviewReminder.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooksForReviewReminder
 * @ingroup plugins_generic_booksForReview
 *
 * @brief Class to perform automated reminders for book reviewers.
 * [WIZDAM EDITION] Modernized. Uses PHP DateTime for accurate calculation.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class BooksForReviewReminder extends ScheduledTask {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BooksForReviewReminder() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BooksForReviewReminder(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * @see ScheduledTask::getName()
     */
    public function getName(): string {
        return __('plugins.generic.booksForReview.reminderTask.name');
    }

    /**
     * Send email to a book for review author
     */
    public function sendReminder($book, $journal, $emailKey) {
        // [WIZDAM] Pastikan URL dibangun dengan benar
        // Kita tidak bisa menggunakan Request::url() di dalam ScheduledTask karena
        // ScheduledTask mungkin jalan via CLI (tanpa Request context).
        // Solusinya: Gunakan Config::getVar('general', 'base_url') manual atau biarkan jika via Acron.
        $submissionUrl = Config::getVar('general', 'base_url') . '/index.php/' . $journal->getPath() . '/author/submit';

        $paramArray = array(
            'authorName' => strip_tags($book->getUserFullName()),
            'bookForReviewTitle' => '"' . strip_tags($book->getLocalizedTitle()) . '"',
            'bookForReviewDueDate' => date('l, F j, Y', strtotime($book->getDateDue())),
            'submissionUrl' => $submissionUrl,
            'editorialContactSignature' => strip_tags($book->getEditorContactSignature())
        );

        import('classes.mail.MailTemplate');
        $mail = new MailTemplate($emailKey);

        $mail->setFrom($book->getEditorEmail(), $book->getEditorFullName());
        $mail->addRecipient($book->getUserEmail(), $book->getUserFullName());
        $mail->setSubject($mail->getSubject($journal->getPrimaryLocale()));
        $mail->setBody($mail->getBody($journal->getPrimaryLocale()));
        $mail->assignParams($paramArray);
        $mail->send();
    }

    /**
     * Send email to a journal's book for review authors
     */
    public function sendJournalReminders($journal) {
        // [MODERNISASI] Load plugin via Registry, jangan hardcode
        $bfrPlugin = PluginRegistry::getPlugin('generic', 'booksforreviewplugin');

        if ($bfrPlugin) {
            $bfrPluginName = $bfrPlugin->getName();
            $bfrPlugin->import('classes.BookForReviewDAO');
            $bfrPlugin->import('classes.BookForReviewAuthorDAO');

            // Register DAO (Idempotent: DAORegistry handle duplicates)
            $bfrAuthorDao = new BookForReviewAuthorDAO($bfrPluginName);
            DAORegistry::registerDAO('BookForReviewAuthorDAO', $bfrAuthorDao);

            $bfrDao = new BookForReviewDAO($bfrPluginName);
            DAORegistry::registerDAO('BookForReviewDAO', $bfrDao);

            $journalId = $journal->getId();
            $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
            $booksForReviewEnabled = $pluginSettingsDao->getSetting($journalId, $bfrPluginName, 'enabled');
        } else {
            return false;
        }

        if ($booksForReviewEnabled) {
            $today = new DateTime(); // [WIZDAM] Use PHP DateTime

            // 1. Reminder BEFORE Due Date
            if ($pluginSettingsDao->getSetting($journalId, $bfrPluginName, 'enableDueReminderBefore')) {
                $beforeDays = (int) $pluginSettingsDao->getSetting($journalId, $bfrPluginName, 'numDaysBeforeDueReminder');
                
                // Hitung tanggal target: Hari ini + X hari
                $targetDate = clone $today;
                $targetDate->modify('+' . $beforeDays . ' days');
                $dueDateString = $targetDate->format('Y-m-d');

                $books = $bfrDao->getBooksForReviewByDateDue($journalId, $dueDateString);
                while (!$books->eof()) {
                    $bookForReview = $books->next();
                    $this->sendReminder($bookForReview, $journal, 'BFR_REVIEW_REMINDER');
                }
            }

            // 2. Reminder AFTER Due Date (Late)
            if ($pluginSettingsDao->getSetting($journalId, $bfrPluginName, 'enableDueReminderAfter')) {
                $afterDays = (int) $pluginSettingsDao->getSetting($journalId, $bfrPluginName, 'numDaysAfterDueReminder');

                // Hitung tanggal target: Hari ini - X hari
                // Artinya, due date-nya adalah X hari yang lalu
                $targetDate = clone $today;
                $targetDate->modify('-' . $afterDays . ' days');
                $dueDateString = $targetDate->format('Y-m-d');

                $books = $bfrDao->getBooksForReviewByDateDue($journalId, $dueDateString);
                while (!$books->eof()) {
                    $bookForReview = $books->next();
                    $this->sendReminder($bookForReview, $journal, 'BFR_REVIEW_REMINDER_LATE');
                }
            }
        }
    }

    /**
     * @see ScheduledTask::executeActions()
     */
    public function executeActions() {
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);

        while (!$journals->eof()) {
            $journal = $journals->next();
            // [WIZDAM] Panggil fungsi dengan satu parameter saja (Journal)
            // Logika tanggal sudah dihandle di dalam menggunakan DateTime
            $this->sendJournalReminders($journal);
            unset($journal);
        }

        return true;
    }
}

?>