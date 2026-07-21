<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep2Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep2Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 2 of author article submission.
 */

import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep2Form extends AuthorSubmitForm {

    /**
     * Constructor.
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function __construct($article, $journal, $request) {
        parent::__construct($article, 2, $journal, $request);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function AuthorSubmitStep2Form($article, $journal, $request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $this->__construct($article, $journal, $request);
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
        if (!$request) $request = Application::get()->getRequest();
        
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->article = $articleDao->getArticle($this->articleId);
        
        $templateMgr = TemplateManager::getManager($request);
        $submissionFileId = $this->article ? (int) $this->article->getSubmissionFileId() : 0;
        
        if ($submissionFileId > 0) {
            /** @var ArticleFileDAO $articleFileDao */
            $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
            $file = $articleFileDao->getArticleFile($submissionFileId, null, $this->articleId);
            if ($file) {
                $templateMgr->assign('submissionFile', $file);
            }
        }
        parent::display($request, $template);
    }

    /**
     * Upload the submission file.
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