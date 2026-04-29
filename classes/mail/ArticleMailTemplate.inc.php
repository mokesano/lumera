<?php
declare(strict_types=1);

/**
 * @file classes/mail/ArticleMailTemplate.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleMailTemplate
 * @ingroup mail
 *
 * @brief Subclass of MailTemplate for sending emails related to articles.
 *
 * This allows for article-specific functionality like logging, etc.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.mail.MailTemplate');
import('classes.article.log.ArticleEmailLogEntry'); // Bring in log constants

class ArticleMailTemplate extends MailTemplate {

    /** @var object|null the associated article */
    public $article;

    /** @var object|null the associated journal */
    public $journal;

    /** @var int Event type of this email */
    public $eventType;

    /**
     * Constructor.
     * @param object $article Article
     * @param string|null $emailKey
     * @param string|null $locale
     * @param bool|null $enableAttachments
     * @param object|null $journal
     * @param bool $includeSignature
     * @param bool $ignorePostedData
     */
    public function __construct($article, $emailKey = null, $locale = null, $enableAttachments = null, $journal = null, $includeSignature = true, $ignorePostedData = false) {
        parent::__construct($emailKey, $locale, $enableAttachments, $journal, $includeSignature, $ignorePostedData);
        $this->article = $article;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleMailTemplate($article, $emailKey = null, $locale = null, $enableAttachments = null, $journal = null, $includeSignature = true, $ignorePostedData = false) {
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
     * Assigns values to e-mail parameters.
     * @param array $paramArray
     * @return void
     */
    public function assignParams($paramArray = []) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();

        $article = $this->article;
        $journal = isset($this->journal) ? $this->journal : $request->getJournal();

        $paramArray['articleTitle'] = strip_tags($article->getLocalizedTitle());
        $paramArray['articleId'] = $article->getId();
        $paramArray['journalName'] = strip_tags($journal->getLocalizedTitle());
        $paramArray['sectionName'] = strip_tags($article->getSectionTitle());
        $paramArray['articleAbstract'] = PKPString::html2text($article->getLocalizedAbstract());
        $paramArray['authorString'] = strip_tags($article->getAuthorString());

        return parent::assignParams($paramArray);
    }

    /**
     * Send the email.
     * @param object|null $request
     * @return bool
     */
    public function send($request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // PERBAIKAN: Ganti 'false' menjadi '$request'
        // Jika parent::send menerima false, dia akan crash saat mencoba akses user/context.
        if (parent::send($request)) { 
            
            // Logika Log
            if (!isset($this->skip) || !$this->skip) {
                // Bungkus log agar jika log error, email tetap dianggap sukses
                try {
                    $this->log($request);
                } catch (Exception $e) {
                    error_log("Email Log Error: " . $e->getMessage());
                }
            }

            // Logika Hapus Attachment (Cleanup)
            if ($request) {
                $user = $request->getUser();
                if ($user && $this->attachmentsEnabled) {
                    $this->_clearAttachments((int) $user->getId()); // Tambahkan (int) jaga-jaga
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Send email with params.
     * @param array $paramArray
     * @param object|null $request
     * @return bool
     */
    public function sendWithParams($paramArray, $request = null) {
        // [WIZDAM] Fallback request if not passed
        $request = $request ?? Application::get()->getRequest();

        $savedSubject = $this->getSubject();
        $savedBody = $this->getBody();

        $this->assignParams($paramArray);

        $ret = $this->send($request);

        $this->setSubject($savedSubject);
        $this->setBody($savedBody);

        return $ret;
    }

    /**
     * Set the journal this message is associated with.
     * @param object $journal Journal
     */
    public function setJournal($journal) {
        $this->journal = $journal;
    }

    /**
     * Save the email in the article email log.
     * @param object|null $request
     */
    public function log($request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleEmailLogDao = DAORegistry::getDAO('ArticleEmailLogDAO');
        $entry = $articleEmailLogDao->newDataObject();
        $article = $this->article;

        // Log data
        $entry->setEventType($this->eventType);
        $entry->setSubject($this->getSubject());
        $entry->setBody($this->getBody());
        $entry->setFrom($this->getFromString(false));
        $entry->setRecipients($this->getRecipientString());
        $entry->setCcs($this->getCcString());
        $entry->setBccs($this->getBccString());

        // Add log entry
        import('classes.article.log.ArticleLog');
        $logEntryId = ArticleLog::logEmail((int) $this->article->getId(), $entry, $request);

        // Add attachments
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($article->getId());
        foreach ($this->getAttachmentFiles() as $attachment) {
            $articleFileManager->temporaryFileToArticleFile(
                $attachment,
                ARTICLE_FILE_ATTACHMENT,
                $logEntryId
            );
        }
    }

    /**
     * Get assigned editors.
     * @param int $articleId
     * @return array EditAssignment
     */
    public function toAssignedEditors($articleId) {
        $returner = [];
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            $this->addRecipient($editAssignment->getEditorEmail(), $editAssignment->getEditorFullName());
            $returner[] = $editAssignment;
            unset($editAssignment);
        }
        return $returner;
    }
    
    /**
     * Get assigned reviewing section editors.
     * @param int $articleId
     * @return array EditAssignment
     */
    public function toAssignedReviewingSectionEditors($articleId) {
        $returner = [];
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getReviewingSectionEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            $this->addRecipient($editAssignment->getEditorEmail(), $editAssignment->getEditorFullName());
            $returner[] = $editAssignment;
            unset($editAssignment);
        }
        return $returner;
    }

    /**
     * Get assigned editing section editors.
     * @param int $articleId
     * @return array EditAssignment
     */
    public function toAssignedEditingSectionEditors($articleId) {
        $returner = [];
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditingSectionEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            $this->addRecipient($editAssignment->getEditorEmail(), $editAssignment->getEditorFullName());
            $returner[] = $editAssignment;
            unset($editAssignment);
        }
        return $returner;
    }

    /**
     * CC assigned editors.
     * @param int $articleId
     * @return array EditAssignment
     */
    public function ccAssignedEditors($articleId) {
        $returner = [];
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            $this->addCc($editAssignment->getEditorEmail(), $editAssignment->getEditorFullName());
            $returner[] = $editAssignment;
            unset($editAssignment);
        }
        return $returner;
    }
        
    /**
     * CC assigned reviewing section editors.
     * @param int $articleId
     * @return array EditAssignment
     */
    public function ccAssignedReviewingSectionEditors($articleId) {
        $returner = [];
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getReviewingSectionEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            $this->addCc($editAssignment->getEditorEmail(), $editAssignment->getEditorFullName());
            $returner[] = $editAssignment;
            unset($editAssignment);
        }
        return $returner;
    }

    /**
     * CC assigned editing section editors.
     * @param int $articleId
     * @return array EditAssignment
     */
    public function ccAssignedEditingSectionEditors($articleId) {
        $returner = [];
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditingSectionEditorAssignmentsByArticleId($articleId);
        while ($editAssignment = $editAssignments->next()) {
            $this->addCc($editAssignment->getEditorEmail(), $editAssignment->getEditorFullName());
            $returner[] = $editAssignment;
            unset($editAssignment);
        }
        return $returner;
    }
}
?>