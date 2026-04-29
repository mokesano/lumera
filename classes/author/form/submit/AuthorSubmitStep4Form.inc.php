<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep4Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep4Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 4 of author article submission.
 * [WIZDAM EDITION] Refactored for PHP 8.x
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
     */
    public function AuthorSubmitStep4Form($article, $journal, $request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($article, $journal, $request);
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
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        // [WIZDAM] Use assign instead of assign_by_ref
        $templateMgr->assign('suppFiles', $suppFileDao->getSuppFilesByArticle($this->articleId));

        parent::display($request, $template);
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        // Update article
        $article = $this->article;
        if ($article->getSubmissionProgress() <= $this->step) {
            $article->stampStatusModified();
            $article->setSubmissionProgress($this->step + 1);
        }
        $articleDao->updateArticle($article);

        return $this->articleId;
    }
}

?>