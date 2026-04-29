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
 * [WIZDAM EDITION] Refactored for PHP 8.x
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

        // Validation checks for this form
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorSubmitStep2Form($article, $journal, $request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($article, $journal, $request);
    }

    /**
     * Initialize form data from current article.
     */
    public function initData() {
        if (isset($this->article)) {
            $article = $this->article;
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

        $templateMgr = TemplateManager::getManager($request);

        // Get supplementary files for this article
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        if ($this->article->getSubmissionFileId() != null) {
            // [WIZDAM] Use assign instead of assign_by_ref
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

        $articleFileManager = new ArticleFileManager($this->articleId);
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        $submissionFileId = null;

        if ($articleFileManager->uploadedFileExists($fileName)) {
            // upload new submission file, overwriting previous if necessary
            $submissionFileId = $articleFileManager->uploadSubmissionFile($fileName, $this->article->getSubmissionFileId(), true);
        }

        if (!empty($submissionFileId)) {
            $this->article->setSubmissionFileId($submissionFileId);
            return (bool) $articleDao->updateArticle($this->article);
        } else {
            return false;
        }
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        // Update article
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