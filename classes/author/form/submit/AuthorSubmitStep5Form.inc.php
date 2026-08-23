<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep5Form.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class AuthorSubmitStep5Form
 * @ingroup author_form_submit
 *
 * @brief [WIZDAM] Step 5 dari wizard submit yang DIRESTRUKTURISASI --
 * Overview (ringkasan seluruh input Step 1-4) + tombol Submit + "Save
 * to Draft".
 *
 * PENTING: "Save to Draft" TIDAK memerlukan logika/status baru apa pun
 * (dikonfirmasi eksplisit) -- artikel SUDAH otomatis tersimpan
 * bertahap di setiap step (submissionProgress), bisa ditinggal dan
 * dilanjutkan kapan saja. "Save to Draft" di sini murni PELABELAN
 * ULANG tautan "kembali ke daftar submission saya" TANPA memanggil
 * execute() -- ditangani di TEMPLATE (link biasa, bukan form submit),
 * TIDAK ADA perubahan method PHP untuk itu.
 *
 * SATU-SATUNYA perubahan nyata di kelas ini dibanding Step 5 lama:
 * display() sekarang MENGUMPULKAN data ringkasan (title, authors,
 * funders, files, deklarasi) dari step-step sebelumnya untuk
 * ditampilkan sebagai overview. SELURUH logika finalisasi
 * (execute()/validate() -- signoff, penugasan editor, email notifikasi,
 * pencatatan log) DIPERTAHANKAN PERSIS TANPA PERUBAHAN dari Step 5
 * lama -- ini bagian PALING BERISIKO untuk diubah, jadi SENGAJA tidak
 * disentuh sama sekali.
 */

import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep5Form extends AuthorSubmitForm {
    
    /**
     * Constructor.
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function __construct($article, $journal, $request) {
        parent::__construct($article, 5, $journal, $request);

        // [WIZDAM] Array syntax for callable, removed reference on $this
        $this->addCheck(new FormValidatorCustom($this, 'qualifyForWaiver', 'optional', 'author.submit.mustEnterWaiverReason', [$this, 'checkWaiverReason']));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function AuthorSubmitStep5Form($article, $journal, $request) {
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
     * Check that if the user choses a Waiver that they enter text in the comments to Editor
     * @return bool
     */
    public function checkWaiverReason() {
        if ($this->request->getUserVar('qualifyForWaiver') == false ) return true;
        else return ($this->request->getUserVar('commentsToEditor') != '');
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        if (!$this->request) $this->request = $request;

        $journal = $this->request->getJournal();
        $user = $this->request->getUser();
        $templateMgr = TemplateManager::getManager($request);

        // Get article file for this article
        /** @var ArticleFileDAO $articleFileDao  */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $articleFiles = $articleFileDao->getArticleFilesByArticle($this->articleId);

        $templateMgr->assign('files', $articleFiles);
        $templateMgr->assign('journal', $journal);

        // [WIZDAM] Overview -- ringkasan seluruh input Step 1-4, murni
        // BACA data yang SUDAH tersimpan (tidak menghitung/memvalidasi
        // apa pun baru), ditampilkan sebelum tombol Submit/Save Draft.
        $this->_assignOverviewData($templateMgr);

        // Set up required Payment Related Information
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->request);
        if ( $paymentManager->submissionEnabled() || $paymentManager->fastTrackEnabled() || $paymentManager->publicationEnabled()) {
            $templateMgr->assign('authorFees', true);
            import('lib.wizdam.classes.invoice.Invoice');
            /** @var InvoiceDAO $invoiceDao */
            $invoiceDao = DAORegistry::getDAO('InvoiceDAO');
            $articleId = $this->articleId;

            if ($paymentManager->submissionEnabled()) {
                $templateMgr->assign('submissionPayment', $invoiceDao->getPaidInvoiceForArticleFee(
                    $journal->getId(), $articleId, Invoice::FEE_TYPE_SUBMISSION, PAYMENT_TYPE_SUBMISSION
                ));
                $templateMgr->assign('manualPayment', $journal->getSetting('paymentMethodPluginName') == 'ManualPayment');
            }

            if ($paymentManager->fastTrackEnabled()) {
                $templateMgr->assign('fastTrackPayment', $invoiceDao->getPaidInvoiceForArticleFee(
                    $journal->getId(), $articleId, Invoice::FEE_TYPE_FAST_TRACK, PAYMENT_TYPE_FASTTRACK
                ));
            }
        }

        parent::display($request, $template);
    }

    /**
     * [WIZDAM] Kumpulkan data overview dari SELURUH step sebelumnya --
     * murni pembacaan data yang SUDAH tersimpan di database (Step 1-4
     * masing-masing sudah menyimpan datanya sendiri saat dilewati),
     * TIDAK ADA perhitungan/logika bisnis baru di sini. Kalau salah
     * satu step belum pernah diisi (mis. user melompat langsung ke
     * Step 5 lewat URL manual), field terkait cukup kosong -- template
     * yang menentukan cara menampilkannya (mis. "belum diisi").
     * @param PKPTemplateManager $templateMgr
     */
    private function _assignOverviewData($templateMgr) {
        $article = $this->article;
        if (!$article) {
            return;
        }

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $templateMgr->assign('overviewSection', $sectionDao->getSection($article->getSectionId()));

        // --- Ringkasan Step 1: Metadata ---
        $templateMgr->assign('overviewTitle', $article->getLocalizedTitle());
        $templateMgr->assign('overviewAbstract', $article->getLocalizedAbstract());

        // --- Ringkasan Step 2: Authors + Funders ---
        $templateMgr->assign('overviewAuthors', $article->getAuthors());
        /** @var ArticleFunderDAO $funderDao */
        $funderDao = DAORegistry::getDAO('ArticleFunderDAO');
        $templateMgr->assign('overviewFunders', $funderDao->getByArticleId($article->getId())->toArray());

        // --- Ringkasan Step 3: Deklarasi ---
        $templateMgr->assign('overviewCompetingInterest', $article->getLocalizedCompetingInterest());
        $templateMgr->assign('overviewEthicalApproval', $article->getLocalizedEthicalApproval());
        $templateMgr->assign('overviewGenerativeAiDeclaration', $article->getLocalizedGenerativeAiDeclaration());

        // --- Ringkasan Step 4: File (submission file sudah diambil di
        // $templateMgr->assign('files', ...) sebelum method ini dipanggil
        // -- tidak perlu diulang, dipakai lewat variabel $files yang sama). ---
        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $templateMgr->assign('overviewSuppFiles', $suppFileDao->getSuppFilesByArticle($article->getId()));
    }

    /**
     * Initialize form data from current article.
     */
    public function initData() {
        if (isset($this->article)) {
            $this->_data = [
                'commentsToEditor' => $this->article->getCommentsToEditor()
            ];
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['paymentSent', 'qualifyForWaiver', 'commentsToEditor']);
    }

    /**
     * Validate the form
     * @param bool $callHooks
     * @return bool
     */
    public function validate($callHooks = true) {
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->request);
        if ( $paymentManager->submissionEnabled() ) {
            if (!parent::validate()) return false;

            $journal = $this->request->getJournal();
            $journalId = $journal->getId();
            $articleId = $this->articleId;
            $user = $this->request->getUser();

            /** @var OJSCompletedPaymentDAO $completedPaymentDao  */
            $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
            if ($completedPaymentDao->hasPaidSubmission($journalId, $articleId)) {
                return parent::validate();
            } elseif ($this->request->getUserVar('qualifyForWaiver') && $this->request->getUserVar('commentsToEditor') != '') {
                return parent::validate();
            } elseif ($this->request->getUserVar('paymentSent')) {
                return parent::validate();
            } else {
                $queuedPayment = $paymentManager->createQueuedPayment($journalId, PAYMENT_TYPE_SUBMISSION, $user->getId(), $articleId, $journal->getSetting('submissionFee'));
                $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

                $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
                exit;
            }
        } else {
            return parent::validate();
        }
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        /** @var ArticleDAO $articleDao  */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var SignoffDAO $signoffDao  */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        $journal = $this->request->getJournal();
        $user = $this->request->getUser();

        // Update article
        $article = $this->article;

        if ($this->getData('commentsToEditor') != '') {
            $article->setCommentsToEditor($this->getData('commentsToEditor'));
        }

        $article->setDateSubmitted(Core::getCurrentDate());
        $article->setSubmissionProgress(0);
        $article->stampStatusModified();
        $articleDao->updateArticle($article);

        // Setup default copyright/license metadata at finalization of submission.
        $article->initializePermissions();
        $articleDao->updateLocaleFields($article);

        // Designate this as the review version by default.
        /** @var AuthorSubmissionDAO $authorSubmissionDao  */
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
        $authorSubmission = $authorSubmissionDao->getAuthorSubmission($article->getId());
        AuthorAction::designateReviewVersion($authorSubmission, true);
        unset($authorSubmission);

        $copyeditInitialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $article->getId());
        $copyeditAuthorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $article->getId());
        $copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $article->getId());
        $copyeditInitialSignoff->setUserId(0);
        $copyeditAuthorSignoff->setUserId($user->getId());
        $copyeditFinalSignoff->setUserId(0);
        $signoffDao->updateObject($copyeditInitialSignoff);
        $signoffDao->updateObject($copyeditAuthorSignoff);
        $signoffDao->updateObject($copyeditFinalSignoff);

        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $article->getId());
        $layoutSignoff->setUserId(0);
        $signoffDao->updateObject($layoutSignoff);

        $proofAuthorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_ARTICLE, $article->getId());
        $proofProofreaderSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $article->getId());
        $proofLayoutEditorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $article->getId());
        $proofAuthorSignoff->setUserId($user->getId());
        $proofProofreaderSignoff->setUserId(0);
        $proofLayoutEditorSignoff->setUserId(0);
        $signoffDao->updateObject($proofAuthorSignoff);
        $signoffDao->updateObject($proofProofreaderSignoff);
        $signoffDao->updateObject($proofLayoutEditorSignoff);

        $sectionEditors = $this->assignEditors($article);

        // Send author notification email
        import('classes.mail.ArticleMailTemplate');
        $mail = new ArticleMailTemplate($article, 'SUBMISSION_ACK', null, null, null, false);
        $mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
        if ($mail->isEnabled()) {
            $mail->addRecipient($user->getEmail(), $user->getFullName());
            // If necessary, BCC the acknowledgement to someone.
            if($journal->getSetting('copySubmissionAckPrimaryContact')) {
                $mail->addBcc(
                    $journal->getSetting('contactEmail'),
                    $journal->getSetting('contactName')
                );
            }
            if($journal->getSetting('copySubmissionAckSpecified')) {
                $copyAddress = $journal->getSetting('copySubmissionAckAddress');
                if (!empty($copyAddress)) $mail->addBcc($copyAddress);
            }

            // Also BCC automatically assigned section editors
            foreach ($sectionEditors as $sectionEditorEntry) {
                $sectionEditor = $sectionEditorEntry['user'];
                $mail->addBcc($sectionEditor->getEmail(), $sectionEditor->getFullName());
                unset($sectionEditor);
            }

            $mail->assignParams([
                'authorName'                => $user->getFullName(),
                'authorUsername'            => $user->getUsername(),
                'editorialContactSignature' => $journal->getSetting('contactName') . "\n" . $journal->getLocalizedTitle(),
                'submissionUrl'             => $this->request->url(null, 'author', 'submission', $article->getId()),
                
                // --- TAMBAHAN VARIABEL ---
                'articleTitle'              => $article->getLocalizedTitle(),
                'articleId'                 => $article->getId(),
                'sectionName'               => $article->getSectionTitle(),
                'submitDate'                => date('d F Y', strtotime(Core::getCurrentDate())),
                
                // Variabel waktu dinamis
                'reviewTime'                => $journal->getSetting('numWeeksPerReview') . ' ' . __('common.weeks')
            ]);
            $mail->send($this->request);
        }

        import('classes.article.log.ArticleLog');
        ArticleLog::logEvent($this->request, $article, ARTICLE_LOG_ARTICLE_SUBMIT, 'log.author.submitted', ['authorName' => $user->getFullName(), 'submissionId' => $article->getId()]);

        return $this->articleId;
    }
    
}
?>