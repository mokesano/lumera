<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep1Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep1Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 1 of author article submission.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */


import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep1Form extends AuthorSubmitForm {

    /**
     * Constructor.
     * @param Article|null $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function __construct($article, $journal, $request) {
        // [WIZDAM] Removed reference & on params
        parent::__construct($article, 1, $journal, $request);

        // Validation checks for this form
        $this->addCheck(new FormValidator($this, 'sectionId', 'required', 'author.submit.form.sectionRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'sectionId', 'required', 'author.submit.form.sectionRequired', [DAORegistry::getDAO('SectionDAO'), 'sectionExists'], [$journal->getId()]));

        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (!is_array($supportedSubmissionLocales) || count($supportedSubmissionLocales) < 1) $supportedSubmissionLocales = [$journal->getPrimaryLocale()];
        $this->addCheck(new FormValidatorInSet($this, 'locale', 'required', 'author.submit.form.localeRequired', $supportedSubmissionLocales));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorSubmitStep1Form($article, $journal, $request) {
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
        // Ensure internal request property matches
        if (!$this->request) $this->request = $request;

        $journal = $this->request->getJournal();
        $user = $this->request->getUser();

        $templateMgr = TemplateManager::getManager($request);

        // Get sections for this journal
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        // If this user is a section editor or an editor, they are
        // allowed to submit to sections flagged as "editor-only" for
        // submissions. Otherwise, display only sections they are
        // allowed to submit to.
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $isEditor = $roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_EDITOR) || $roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_SECTION_EDITOR);
        $templateMgr->assign('sectionOptions', ['0' => __('author.submit.selectSection')] + $sectionDao->getSectionTitles($journal->getId(), !$isEditor));

        // Set up required Payment Related Information
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->request);
        if ($paymentManager->submissionEnabled() || $paymentManager->fastTrackEnabled() || $paymentManager->publicationEnabled()) {
            $templateMgr->assign('authorFees', true);
            $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
            $articleId = $this->articleId;

            if ($paymentManager->submissionEnabled()) {
                // [WIZDAM] Use assign instead of assign_by_ref
                $templateMgr->assign('submissionPayment', $completedPaymentDao->getSubmissionCompletedPayment ($journal->getId(), $articleId));
            }

            if ($paymentManager->fastTrackEnabled()) {
                // [WIZDAM] Use assign instead of assign_by_ref
                $templateMgr->assign('fastTrackPayment', $completedPaymentDao->getFastTrackCompletedPayment ($journal->getId(), $articleId));
            }
        }

        // Provide available submission languages. (Convert the array
        // of locale symbolic names xx_XX into an associative array
        // of symbolic names => readable names.)
        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (empty($supportedSubmissionLocales)) $supportedSubmissionLocales = [$journal->getPrimaryLocale()];
        $templateMgr->assign(
            'supportedSubmissionLocaleNames',
            array_flip(array_intersect(
                array_flip(AppLocale::getAllLocales()),
                $supportedSubmissionLocales
            ))
        );

        parent::display($request, $template);
    }

    /**
     * Initialize form data from current article.
     */
    public function initData() {
        if (isset($this->article)) {
            $this->_data = [
                'sectionId' => $this->article->getSectionId(),
                'locale' => $this->article->getLocale(),
                'commentsToEditor' => $this->article->getCommentsToEditor()
            ];
        } else {
            // [WIZDAM] Singleton Fallback
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();
            $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
            // Try these locales in order until we find one that's
            // supported to use as a default.
            $fallbackLocales = array_keys($supportedSubmissionLocales);
            $tryLocales = [
                $this->getFormLocale(), // Current form locale
                AppLocale::getLocale(), // Current UI locale
                $journal->getPrimaryLocale(), // Journal locale
                $supportedSubmissionLocales[array_shift($fallbackLocales)] // Fallback: first one on the list
            ];
            $this->_data = [];
            foreach ($tryLocales as $locale) {
                if (in_array($locale, $supportedSubmissionLocales)) {
                    // Found a default to use
                    $this->_data['locale'] = $locale;
                    break;
                }
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['locale', 'submissionChecklist', 'copyrightNoticeAgree', 'sectionId', 'commentsToEditor']);
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        if (isset($this->article)) {
            // Update existing article
            $this->article->setSectionId($this->getData('sectionId'));
            $this->article->setLocale($this->getData('locale'));
            $this->article->setCommentsToEditor($this->getData('commentsToEditor'));
            if ($this->article->getSubmissionProgress() <= $this->step) {
                $this->article->stampStatusModified();
                $this->article->setSubmissionProgress($this->step + 1);
            }
            $articleDao->updateArticle($this->article);

        } else {
            // Insert new article
            // [WIZDAM] Singleton Fallback
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();
            $user = $request->getUser();

            $this->article = new Article();
            $this->article->setLocale($this->getData('locale'));
            $this->article->setUserId($user->getId());
            $this->article->setJournalId($journal->getId());
            $this->article->setSectionId($this->getData('sectionId'));
            $this->article->stampStatusModified();
            $this->article->setSubmissionProgress($this->step + 1);
            $this->article->setLanguage(PKPString::substr($this->article->getLocale(), 0, 2));
            $this->article->setCommentsToEditor($this->getData('commentsToEditor'));
            $articleDao->insertArticle($this->article);
            $this->articleId = $this->article->getId();

            // Set user to initial author
            $authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */
            $user = $request->getUser();
            $author = new Author();
            $author->setSubmissionId($this->articleId);
            $author->setFirstName($user->getFirstName());
            $author->setMiddleName($user->getMiddleName());
            $author->setLastName($user->getLastName());
            $author->setAffiliation($user->getAffiliation(null), null);
            $author->setCountry($user->getCountry());
            $author->setEmail($user->getEmail());
            $author->setData('orcid', $user->getData('orcid'));
            $author->setUrl($user->getUrl());
            $author->setBiography($user->getBiography(null), null);
            $author->setPrimaryContact(1);
            $authorDao->insertAuthor($author);
        }

        return $this->articleId;
    }

}

?>