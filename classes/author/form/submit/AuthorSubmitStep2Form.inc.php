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
     */
    public function display($request = null, $template = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);

        /** @var ArticleFileDAO $articleFileDao  */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        if ($this->article->getSubmissionFileId() != null) {
            $templateMgr->assign('submissionFile', $articleFileDao->getArticleFile($this->article->getSubmissionFileId()));
        }
        parent::display($request, $template);
    }

    /**
     * Upload the submission file.
     * @param string $fileName
     * @return bool
     */
    public function uploadSubmissionFile($fileName) {
        import('classes.file.ArticleFileManager');
        import('classes.notification.NotificationManager');

        $articleFileManager = new ArticleFileManager($this->articleId);
        /** @var ArticleDAO $articleDao  */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $notificationManager = new NotificationManager();
        $userId = $this->request->getUser()->getId();

        $submissionFileId = null;
        $errorMsg = null;

        if ($articleFileManager->uploadedFileExists($fileName)) {
            $submissionFileId = $articleFileManager->uploadSubmissionFile(
                $fileName,
                $this->article->getSubmissionFileId(),
                true,
                $errorMsg
            );
        } else {
            $errorMsg = __('common.uploadFailed');
        }

        if (!empty($submissionFileId)) {
            $this->article->setSubmissionFileId($submissionFileId);
            $updated = (bool) $articleDao->updateArticle($this->article);
            $notificationManager->createTrivialNotification(
                $userId,
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('common.uploadedFile')]
            );
            return $updated;
        } else {
            $this->addError('submissionFile', $errorMsg ?: __('common.uploadFailed'));
            $this->errorFields['submissionFile'] = 1;
            
            $notificationManager->createTrivialNotification(
                $userId,
                NOTIFICATION_TYPE_ERROR,
                ['contents' => $errorMsg ?: __('common.uploadFailed')]
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
        $article = $this->article;

        if ($article->getSubmissionProgress() <= $this->step) {
            $article->stampStatusModified();
            $article->setSubmissionProgress($this->step + 1);
            $articleDao->updateArticle($article);
        }

        return $this->articleId;
    }

}
?>