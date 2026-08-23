<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep4Form.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class AuthorSubmitStep4Form
 * @ingroup author_form_submit
 *
 * @brief [WIZDAM] Step 4 dari wizard submit yang DIRESTRUKTURISASI --
 * GABUNGAN upload naskah utama (Step 2 lama) DAN file pendukung
 * (Step 4 lama) dalam satu step.
 *
 * uploadSubmissionFile() (naskah utama) dipertahankan PERSIS dari Step
 * 2 lama. Daftar file pendukung (SuppFileDAO) dipertahankan PERSIS
 * dari Step 4 lama -- pengelolaan METADATA per-file pendukung
 * (tambah/edit/hapus) TETAP lewat AuthorSubmitSuppFileForm terpisah
 * (op submitSuppFile/saveSubmitSuppFile/deleteSubmitSuppFile di
 * SubmitHandler, TIDAK berubah) -- form itu sudah hardcode
 * submitStep=4, yang KEBETULAN tetap benar di struktur baru karena
 * file pendukung tetap ada di Step 4.
 */

import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep4Form extends AuthorSubmitForm {

    /**
     * Constructor.
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function __construct($article, $journal, $request) {
        parent::__construct($article, 4, $journal, $request);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function AuthorSubmitStep4Form($article, $journal, $request) {
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
     * Initialize form data from current article.
     */
    public function initData() {
        if (isset($this->article)) {
            $this->_data = [];
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([]);
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->article = $articleDao->getArticle($this->articleId);

        $templateMgr = TemplateManager::getManager($request);

        // --- Bagian upload naskah utama (dari Step 2 lama) ---
        $submissionFileId = $this->article ? (int) $this->article->getSubmissionFileId() : 0;
        if ($submissionFileId > 0) {
            /** @var ArticleFileDAO $articleFileDao */
            $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
            $file = $articleFileDao->getArticleFile($submissionFileId, null, $this->articleId);
            if ($file) {
                $templateMgr->assign('submissionFile', $file);
            }
        }

        // --- Bagian file pendukung (dari Step 4 lama) ---
        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $templateMgr->assign('suppFiles', $suppFileDao->getSuppFilesByArticle($this->articleId));

        parent::display($request, $template);
    }

    /**
     * Upload the submission file. Pola PERSIS Step 2 lama.
     * @param string $fileName (Nama field form, misal 'submissionFile')
     * @return bool
     */
    public function uploadSubmissionFile($fileName) {
        import('classes.file.ArticleFileManager');
        import('classes.notification.NotificationManager');

        $articleFileManager = new ArticleFileManager($this->articleId);
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $notificationManager = new NotificationManager();
        $userId = $this->request->getUser()->getId();

        $submissionFileId = null;
        $errorMsg = null;
        $originalFileName = '';

        if ($articleFileManager->uploadedFileExists($fileName)) {
            $originalFileName = (string) $articleFileManager->getUploadedFileName($fileName);
            $submissionFileId = $articleFileManager->uploadSubmissionFile(
                $fileName,
                $this->article->getSubmissionFileId(),
                true,
                $errorMsg
            );
        } else {
            $errorMsg = __('common.notification.uploadFailed');
        }

        if (!empty($submissionFileId)) {
            $this->article->setSubmissionFileId((int) $submissionFileId);
            $articleDao->updateArticle($this->article);
            $safeFileName = htmlspecialchars($originalFileName, ENT_QUOTES, 'UTF-8');
            $notificationManager->createTrivialNotification(
                $userId,
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('common.notification.uploadedFile', ['filename' => $safeFileName])]
            );
            return true;
        } else {
            $this->addError('submissionFile', $errorMsg ?: __('common.notification.uploadFailed'));
            $this->errorFields['submissionFile'] = 1;
            $notificationManager->createTrivialNotification(
                $userId,
                NOTIFICATION_TYPE_ERROR
            );
            return false;
        }
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        // --- Bagian upload naskah utama (dari Step 2 lama) ---
        if (isset($_FILES['submissionFile']) && !empty($_FILES['submissionFile']['name'])) {
            $uploadSuccess = $this->uploadSubmissionFile('submissionFile');
            if (!$uploadSuccess) {
                return $this->articleId;
            }
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($this->articleId);

        if ($article && $article->getSubmissionProgress() <= $this->step) {
            $article->stampStatusModified();
            $article->setSubmissionProgress($this->step + 1);
            $articleDao->updateArticle($article);
        }

        return $this->articleId;
    }

}
?>