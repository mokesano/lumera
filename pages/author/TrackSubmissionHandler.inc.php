<?php
declare(strict_types=1);

/**
 * @file pages/author/TrackSubmissionHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TrackSubmissionHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for submission tracking.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.author.AuthorHandler');

class TrackSubmissionHandler extends AuthorHandler {
    /** @var AuthorSubmission|null submission associated with the request */
    public $submission;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TrackSubmissionHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::TrackSubmissionHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Delete a submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteSubmission($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $authorSubmission = $this->submission;
        $this->setupTemplate($request, true);

        // If the submission is incomplete, allow the author to delete it.
        if ($authorSubmission->getSubmissionProgress() != 0) {
            import('classes.file.ArticleFileManager');
            $articleFileManager = new ArticleFileManager($articleId);
            $articleFileManager->deleteArticleTree();

            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $articleDao->deleteArticleById($articleId);

            import('classes.search.ArticleSearchIndex');
            $articleSearchIndex = new ArticleSearchIndex();
            $articleSearchIndex->articleDeleted($articleId);
            $articleSearchIndex->articleChangesFinished();
        }

        $request->redirect(null, null, 'index');
    }

    /**
     * Delete an author version file.
     * @param array $args ($articleId, $fileId, $revisionId)
     * @param PKPRequest $request
     */
    public function deleteArticleFile($args, $request) {
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revisionId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $authorSubmission = $this->submission;

        if ($authorSubmission->getStatus() != STATUS_PUBLISHED && $authorSubmission->getStatus() != STATUS_ARCHIVED) {
            AuthorAction::deleteArticleFile($authorSubmission, $fileId, $revisionId);
        }

        $request->redirect(null, null, 'submissionReview', $articleId);
    }

    /**
     * Display a summary of the status of an author's submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submission($args, $request) {
        $journal = $request->getJournal();
        $user = $request->getUser();
        $articleId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true, $articleId);

        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());

        // Setting the round.
        $round = (int) array_shift($args);
        if (!$round) $round = $submission->getCurrentRound();

        $templateMgr = TemplateManager::getManager();

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($submission->getId());
        
        // 1. Inisialisasi awal dengan null
        $issue = null;

        if ($publishedArticle) {
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($publishedArticle->getIssueId());
        }

        // --- BULLETPROOF NULL OBJECT PATTERN ---
        // 2. Menangkap semua anomali: 
        // Entah datanya hilang di tabel 'issues' atau hilang di 'published_articles', jika $issue masih null, kita paksa buat objek kosong.
        if (!$issue) {
            import('classes.issue.Issue'); // Mencegah fatal error 'Class Issue not found'
            $issue = new Issue();
        }

        // 3. Pastikan assign ke template dilakukan di LUAR blok if ($publishedArticle), sehingga template pasti selalu menerima objek (meskipun isinya kosong).
        $templateMgr->assign('issue', $issue);
        // ---------------------------------------

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($submission->getSectionId());
        $templateMgr->assign('section', $section);

        $templateMgr->assign('journalSettings', $journalSettings);
        $templateMgr->assign('submission', $submission);
        $templateMgr->assign('publishedArticle', $publishedArticle);
        $templateMgr->assign('reviewAssignments', $submission->getReviewAssignments($round));
        $templateMgr->assign('round', $round);
        $templateMgr->assign('submissionFile', $submission->getSubmissionFile());
        $templateMgr->assign('revisedFile', $submission->getRevisedFile());
        $templateMgr->assign('suppFiles', $submission->getSuppFiles());

        import('classes.submission.sectionEditor.SectionEditorSubmission');
        $templateMgr->assign('editorDecisionOptions', SectionEditorSubmission::getEditorDecisionOptions());

        // Set up required Payment Related Information
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        if ( $paymentManager->submissionEnabled() || $paymentManager->fastTrackEnabled() || $paymentManager->publicationEnabled()) {
            $templateMgr->assign('authorFees', true);
            $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');

            if ($paymentManager->submissionEnabled()) {
                $templateMgr->assign('submissionPayment', $completedPaymentDao->getSubmissionCompletedPayment ($journal->getId(), $articleId));
            }

            if ($paymentManager->fastTrackEnabled()) {
                $templateMgr->assign('fastTrackPayment', $completedPaymentDao->getFastTrackCompletedPayment ($journal->getId(), $articleId));
            }

            if ($paymentManager->publicationEnabled()) {
                $templateMgr->assign('publicationPayment', $completedPaymentDao->getPublicationCompletedPayment ($journal->getId(), $articleId));
            }
        }

        $templateMgr->assign('helpTopicId','editorial.authorsRole');

        $initialCopyeditSignoff = $submission->getSignoff('SIGNOFF_COPYEDITING_INITIAL');
        $templateMgr->assign('canEditMetadata', !$initialCopyeditSignoff->getDateCompleted() && $submission->getStatus() != STATUS_PUBLISHED);

        $templateMgr->display('author/submission.tpl');
    }

    /**
     * Display specific details of an author's submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submissionReview($args, $request) {
        $user = $request->getUser();
        $articleId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $authorSubmission = $this->submission;
        $this->setupTemplate($request, true, $articleId);
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR); // editor.article.decision etc. FIXME?

        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewModifiedByRound = $reviewAssignmentDao->getLastModifiedByRound($articleId);
        $reviewEarliestNotificationByRound = $reviewAssignmentDao->getEarliestNotificationByRound($articleId);
        $reviewFilesByRound = $reviewAssignmentDao->getReviewFilesByRound($articleId);
        $authorViewableFilesByRound = $reviewAssignmentDao->getAuthorViewableFilesByRound($articleId);

        $editorDecisions = $authorSubmission->getDecisions($authorSubmission->getCurrentRound());
        $lastDecision = count($editorDecisions) >= 1 ? $editorDecisions[count($editorDecisions) - 1] : null;

        $templateMgr = TemplateManager::getManager();

        $reviewAssignments = $authorSubmission->getReviewAssignments();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('reviewAssignments', $reviewAssignments);
        $templateMgr->assign('submission', $authorSubmission);
        $templateMgr->assign('reviewFilesByRound', $reviewFilesByRound);
        $templateMgr->assign('authorViewableFilesByRound', $authorViewableFilesByRound);
        $templateMgr->assign('reviewModifiedByRound', $reviewModifiedByRound);

        $reviewIndexesByRound = [];
        for ($round = 1; $round <= $authorSubmission->getCurrentRound(); $round++) {
            $reviewIndexesByRound[$round] = $reviewAssignmentDao->getReviewIndexesForRound($articleId, $round);
        }
        $templateMgr->assign('reviewIndexesByRound', $reviewIndexesByRound);

        $templateMgr->assign('reviewEarliestNotificationByRound', $reviewEarliestNotificationByRound);
        $templateMgr->assign('submissionFile', $authorSubmission->getSubmissionFile());
        $templateMgr->assign('revisedFile', $authorSubmission->getRevisedFile());
        $templateMgr->assign('suppFiles', $authorSubmission->getSuppFiles());
        $templateMgr->assign('lastEditorDecision', $lastDecision);
        import('classes.submission.sectionEditor.SectionEditorSubmission');
        $templateMgr->assign('editorDecisionOptions', SectionEditorSubmission::getEditorDecisionOptions());
        $templateMgr->assign('helpTopicId', 'editorial.authorsRole.review');
        $templateMgr->display('author/submissionReview.tpl');
    }

    /**
     * Add a supplementary file.
     * @param array $args ($articleId)
     * @param PKPRequest $request
     */
    public function addSuppFile($args, $request) {
        $articleId = (int) array_shift($args);
        $journal = $request->getJournal();

        $this->validate(null, $request, $articleId);
        $authorSubmission = $this->submission;

        if ($authorSubmission->getStatus() != STATUS_PUBLISHED && $authorSubmission->getStatus() != STATUS_ARCHIVED) {
            $this->setupTemplate($request, true, $articleId, 'summary');

            import('classes.submission.form.SuppFileForm');

            $submitForm = new SuppFileForm($authorSubmission, $journal);

            if ($submitForm->isLocaleResubmit()) {
                $submitForm->readInputData();
            } else {
                $submitForm->initData();
            }
            $submitForm->display();
        } else {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Edit a supplementary file.
     * @param array $args ($articleId, $suppFileId)
     * @param PKPRequest $request
     */
    public function editSuppFile($args, $request) {
        $articleId = (int) array_shift($args);
        $suppFileId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $authorSubmission = $this->submission;

        if ($authorSubmission->getStatus() != STATUS_PUBLISHED && $authorSubmission->getStatus() != STATUS_ARCHIVED) {
            $this->setupTemplate($request, true, $articleId, 'summary');

            import('classes.submission.form.SuppFileForm');

            $journal = $request->getJournal();
            $submitForm = new SuppFileForm($authorSubmission, $journal, $suppFileId);

            if ($submitForm->isLocaleResubmit()) {
                $submitForm->readInputData();
            } else {
                $submitForm->initData();
            }
            $submitForm->display();
        } else {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Set reviewer visibility for a supplementary file.
     * @param array $args ($suppFileId)
     * @param PKPRequest $request
     */
    public function setSuppFileVisibility($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $authorSubmission = $this->submission;

        if ($authorSubmission->getStatus() != STATUS_PUBLISHED && $authorSubmission->getStatus() != STATUS_ARCHIVED) {
            $suppFileId = (int) $request->getUserVar('fileId');
            $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
            $suppFile = $suppFileDao->getSuppFile($suppFileId, $articleId);

            if (isset($suppFile) && $suppFile != null) {
                $suppFile->setShowReviewers((int) $request->getUserVar('hide')==1?0:1);
                $suppFileDao->updateSuppFile($suppFile);
            }
        }
        $request->redirect(null, null, 'submissionReview', $articleId);
    }

    /**
     * Save a supplementary file.
     * @param array $args ($suppFileId)
     * @param PKPRequest $request
     */
    public function saveSuppFile($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $suppFileId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);

        $authorSubmission = $this->submission;
        $this->setupTemplate($request, true, $articleId, 'summary');

        if ($authorSubmission->getStatus() != STATUS_PUBLISHED && $authorSubmission->getStatus() != STATUS_ARCHIVED) {
            import('classes.submission.form.SuppFileForm');

            $journal = $request->getJournal();
            $submitForm = new SuppFileForm($authorSubmission, $journal, $suppFileId);
            $submitForm->readInputData();

            if ($submitForm->validate()) {
                $submitForm->execute();
                $request->redirect(null, null, 'submission', $articleId);
            } else {
                $submitForm->display();
            }
        } else {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Display the status and other details of an author's submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submissionEditing($args, $request) {
        $journal = $request->getJournal();
        $user = $request->getUser();
        $articleId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true, $articleId);

        AuthorAction::copyeditUnderway($submission);
        import('classes.submission.proofreader.ProofreaderAction');
        ProofreaderAction::proofreadingUnderway($submission, 'SIGNOFF_PROOFREADING_AUTHOR');

        $templateMgr = TemplateManager::getManager();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('submission', $submission);
        $templateMgr->assign('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
        $templateMgr->assign('submissionFile', $submission->getSubmissionFile());
        $templateMgr->assign('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
        $templateMgr->assign('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
        $templateMgr->assign('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
        $templateMgr->assign('suppFiles', $submission->getSuppFiles());
        $templateMgr->assign('useCopyeditors', $journal->getSetting('useCopyeditors'));
        $templateMgr->assign('useLayoutEditors', $journal->getSetting('useLayoutEditors'));
        $templateMgr->assign('useProofreaders', $journal->getSetting('useProofreaders'));
        $templateMgr->assign('helpTopicId', 'editorial.authorsRole.editing');
        $templateMgr->display('author/submissionEditing.tpl');
    }

    /**
     * Upload the author's revised version of an article.
     * @param array $args
     * @param PKPRequest $request
     */
    public function uploadRevisedVersion($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true);

        AuthorAction::uploadRevisedVersion($submission, $request);

        $request->redirect(null, null, 'submissionReview', $articleId);
    }

    /**
     * View the submission metadata.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewMetadata($args, $request) {
        $articleId = (int) array_shift($args);
        $journal = $request->getJournal();
        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true, $articleId, 'summary');

        AuthorAction::viewMetadata($submission, $journal);
    }

    /**
     * Save the modified metadata.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveMetadata($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true, $articleId);

        // If the copy editor has completed copyediting, disallow
        // the author from changing the metadata.
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $submission->getId());
        if ($initialSignoff->getDateCompleted() != null || AuthorAction::saveMetadata($submission, $request)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Remove cover page from article
     * @param array $args
     * @param PKPRequest $request
     */
    public function removeArticleCoverPage($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);

        $formLocale = array_shift($args);
        if (!AppLocale::isLocaleValid($formLocale)) {
            $request->redirect(null, null, 'viewMetadata', $articleId);
        }

        $submission = $this->submission;
        $journal = $request->getJournal();

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $publicFileManager->removeJournalFile($journal->getId(),$submission->getFileName($formLocale));
        $submission->setFileName('', $formLocale);
        $submission->setOriginalFileName('', $formLocale);
        $submission->setWidth('', $formLocale);
        $submission->setHeight('', $formLocale);

        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleDao->updateArticle($submission);

        $request->redirect(null, null, 'viewMetadata', $articleId);
    }

    /**
     * Uploaded a copyedited version of the submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function uploadCopyeditVersion($args, $request) {
        $copyeditStage = (int) $request->getUserVar('copyeditStage');
        $articleId = (int) $request->getUserVar('articleId');

        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true, $articleId);

        AuthorAction::uploadCopyeditVersion($submission, $copyeditStage);

        $request->redirect(null, null, 'submissionEditing', $articleId);
    }

    /**
     * Flag the author copyediting process as complete.
     * @param array $args
     * @param PKPRequest $request
     */
    public function completeAuthorCopyedit($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate($request, true);

        if (AuthorAction::completeAuthorCopyedit($submission, (int) $request->getUserVar('send'), $request)) {
            $request->redirect(null, null, 'submissionEditing', $articleId);
        }
    }

    //
    // Misc
    //

    /**
     * Download a file.
     * @param array $args ($articleId, $fileId, [$revision])
     * @param PKPRequest $request
     */
    public function downloadFile($args, $request) {
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = (int) array_shift($args);
        if (!$revision) $revision = null;

        $this->validate(null, $request, $articleId);
        $submission = $this->submission;
        if (!AuthorAction::downloadAuthorFile($submission, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Download a file.
     * @param array $args ($articleId, $fileId, [$revision])
     * @param PKPRequest $request
     */
    public function download($args, $request) {
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = (int) array_shift($args);
        if (!$revision) $revision = null;

        $this->validate(null, $request, $articleId);
        Action::downloadFile($articleId, $fileId, $revision);
    }

    //
    // Proofreading
    //

    /**
     * Set the author proofreading date completion
     * @param array $args
     * @param PKPRequest $request
     */
    public function authorProofreadingComplete($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);

        $send = (int) $request->getUserVar('send');

        import('classes.submission.proofreader.ProofreaderAction');

        if (ProofreaderAction::proofreadEmail($articleId, 'PROOFREAD_AUTHOR_COMPLETE', $request, $send?'':$request->url(null, 'author', 'authorProofreadingComplete'))) {
            $request->redirect(null, null, 'submissionEditing', $articleId);
        }
    }

    /**
     * Proof / "preview" a galley.
     * @param array $args ($articleId, $galleyId)
     * @param PKPRequest $request
     */
    public function proofGalley($args, $request) {
        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->display('submission/layout/proofGalley.tpl');
    }

    /**
     * Proof galley (shows frame header).
     * @param array $args ($articleId, $galleyId)
     * @param PKPRequest $request
     */
    public function proofGalleyTop($args, $request) {
        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('backHandler', 'submissionEditing');
        $templateMgr->display('submission/layout/proofGalleyTop.tpl');
    }

    /**
     * Proof galley (outputs file contents).
     * @param array $args ($articleId, $galleyId)
     * @param PKPRequest $request
     */
    public function proofGalleyFile($args, $request) {
        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);

        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = $galleyDao->getGalley($galleyId, $articleId);

        import('classes.file.ArticleFileManager'); // FIXME

        if (isset($galley)) {
            if ($galley->isHTMLGalley()) {
                $templateMgr = TemplateManager::getManager();
                // [WIZDAM] Removed assign_by_ref
                $templateMgr->assign('galley', $galley);
                if ($galley->isHTMLGalley() && $styleFile = $galley->getStyleFile()) {
                    $templateMgr->addStyleSheet($request->url(null, 'article', 'viewFile', [
                        $articleId, $galleyId, $styleFile->getFileId()
                    ]));
                }
                $templateMgr->display('submission/layout/proofGalleyHTML.tpl');

            } else {
                // View non-HTML file inline
                $this->viewFile([$articleId, $galley->getFileId()], $request);
            }
        }
    }

    /**
     * View a file (inlines file).
     * @param array $args ($articleId, $fileId, [$revision])
     * @param PKPRequest $request
     */
    public function viewFile($args, $request) {
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = (int) array_shift($args);
        if (!$revision) $revision = null;

        $this->validate(null, $request, $articleId);
        if (!AuthorAction::viewFile($articleId, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    //
    // Payment Actions
    //

    /**
     * Display a form to pay for the submission an article
     * @param array $args ($articleId)
     * @param PKPRequest $request
     */
    public function paySubmissionFee($args, $request) {
        $articleId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true, $articleId);

        $journal = $request->getJournal();

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $user = $request->getUser();

        $queuedPayment = $paymentManager->createQueuedPayment($journal->getId(), PAYMENT_TYPE_SUBMISSION, $user->getId(), $articleId, $journal->getSetting('submissionFee'));
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

    /**
     * Display a form to pay for Fast Tracking an article
     * @param array $args ($articleId)
     * @param PKPRequest $request
     */
    public function payFastTrackFee($args, $request) {
        $articleId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true, $articleId);

        $journal = $request->getJournal();

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $user = $request->getUser();

        $queuedPayment = $paymentManager->createQueuedPayment($journal->getId(), PAYMENT_TYPE_FASTTRACK, $user->getId(), $articleId, $journal->getSetting('fastTrackFee'));
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

    /**
     * Display a form to pay for Publishing an article
     * @param array $args ($articleId)
     * @param PKPRequest $request
     */
    public function payPublicationFee($args, $request) {
        $articleId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true, $articleId);

        $journal = $request->getJournal();

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $user = $request->getUser();

        $queuedPayment = $paymentManager->createQueuedPayment($journal->getId(), PAYMENT_TYPE_PUBLICATION, $user->getId(), $articleId, $journal->getSetting('publicationFee'));
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }
}
?>