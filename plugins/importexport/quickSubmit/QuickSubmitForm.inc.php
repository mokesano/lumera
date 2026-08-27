<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/quickSubmit/QuickSubmitForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class QuickSubmitForm
 * @ingroup plugins_importexport_quickSubmit
 *
 * @brief Form for QuickSubmit one-page submission plugin
 */

import('lib.pkp.classes.form.Form');
import('classes.article.Author');

class QuickSubmitForm extends Form {
    
    /** @var Request */
    public $request;

    /**
     * Constructor
     * @param object $plugin
     * @param Request $request
     */
    public function __construct($plugin, $request) {
        parent::__construct($plugin->getTemplatePath() . 'index.tpl');

        $this->request = $request;
        $journal = $request->getJournal();

        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidator($this, 'sectionId', 'required', 'author.submit.form.sectionRequired'));
        
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'tempFileId', 
            'required', 
            'plugins.importexport.quickSubmit.submissionRequired', 
            function($tempFileId) { 
                return (int) $tempFileId > 0; 
            }
        ));

        $this->addCheck(new FormValidatorCustom(
            $this, 
            'sectionId', 
            'required', 
            'author.submit.form.sectionRequired', 
            [DAORegistry::getDAO('SectionDAO'), 'sectionExists'], 
            [(int) $journal->getId()]
        ));

        $this->addCheck(new FormValidatorCustom(
            $this, 
            'authors', 
            'required', 
            'author.submit.form.authorRequired', 
            function($authors) { 
                return count((array) $authors) > 0; 
            }
        ));

        $this->addCheck(new FormValidatorCustom(
            $this, 
            'destination', 
            'required', 
            'plugins.importexport.quickSubmit.issueRequired', 
            function($destination, $form) { 
                return $destination === 'queue' ? true : ((int) $form->getData('issueId') > 0); 
            }, 
            [$this]
        ));

        $this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', ['firstName', 'lastName']));

        $this->addCheck(new FormValidatorArrayCustom(
            $this, 
            'authors', 
            'required', 
            'user.profile.form.emailRequired', 
            function($email, $regExp) { 
                return PKPString::regexp_match($regExp, $email); 
            }, 
            [ValidatorEmail::getRegexp()], 
            false, 
            ['email']
        ));

        $this->addCheck(new FormValidatorArrayCustom(
            $this, 
            'authors', 
            'required', 
            'user.profile.form.urlInvalid', 
            function($url, $regExp) { 
                return empty($url) ? true : PKPString::regexp_match($regExp, $url); 
            }, 
            [ValidatorUrl::getRegexp()], 
            false, 
            ['url']
        ));

        // Add ORCiD validation
        import('lib.pkp.classes.validation.ValidatorORCID');
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 
            'authors', 
            'required', 
            'user.profile.form.orcidInvalid', 
            function($orcid) { 
                $validator = new ValidatorORCID(); 
                return empty($orcid) ? true : $validator->isValid($orcid); 
            }, 
            [], 
            false, 
            ['orcid']
        ));

        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (!is_array($supportedSubmissionLocales) || count($supportedSubmissionLocales) < 1) {
            $supportedSubmissionLocales = [$journal->getPrimaryLocale()];
        }
        $this->addCheck(new FormValidatorInSet($this, 'locale', 'required', 'author.submit.form.localeRequired', $supportedSubmissionLocales));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function QuickSubmitForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the names of fields for which data should be localized.
     * @return array
     */
    public function getLocaleFieldNames(): array {
        return ['title', 'abstract', 'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor', 'competingInterest', 'ethicalApproval', 'generativeAiDeclaration'];
    }

    /**
     * Display the form.
     * @param Request|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null): void {
        $templateMgr = TemplateManager::getManager($this->request);
        $request = $this->request;
        $user = $request->getUser();
        $journal = $request->getJournal();
        $formLocale = $this->getFormLocale();

        $templateMgr->assign('journal', $journal);

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections = $sectionDao->getJournalSections((int) $journal->getId());
        $sectionTitles = [];
        $sectionAbstractsRequired = [];
        
        while ($section = $sections->next()) {
            $sectionTitles[$section->getId()] = $section->getTitle($journal->getPrimaryLocale());
            $sectionAbstractsRequired[(int) $section->getId()] = (int) (!$section->getAbstractsNotRequired());
        }

        $templateMgr->assign('sectionOptions', ['0' => __('author.submit.selectSection')] + $sectionTitles);
        $templateMgr->assign('sectionAbstractsRequired', $sectionAbstractsRequired);

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
        $templateMgr->assign('countries', $countries);
        
        $templateMgr->assign('allCreditRoles', Author::getAllCreditRoles());

        import('classes.issue.IssueAction');
        $templateMgr->assign('issueOptions', IssueAction::getIssueOptions());

        import('classes.file.TemporaryFileManager');
        $temporaryFileManager = new TemporaryFileManager();
        $tempFileId = $this->getData('tempFileId');
        
        if (is_array($tempFileId) && isset($tempFileId[$formLocale]) && (int) $tempFileId[$formLocale] > 0) {
            $submissionFile = $temporaryFileManager->getFile((int) $tempFileId[$formLocale], (int) $user->getId());
            $templateMgr->assign('submissionFile', $submissionFile);
        }

        if ($request->getUserVar('addAuthor') || $request->getUserVar('delAuthor') || $request->getUserVar('moveAuthor')) {
            $templateMgr->assign('scrollToAuthor', true);
        }

        if ($request->getUserVar('destination') === 'queue') {
            $templateMgr->assign('publishToIssue', false);
        } else {
            $templateMgr->assign('issueNumber', $request->getUserVar('issueId'));
            $templateMgr->assign('publishToIssue', true);
        }

        $templateMgr->assign('enablePageNumber', $journal->getSetting('enablePageNumber'));

        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (empty($supportedSubmissionLocales)) {
            $supportedSubmissionLocales = [$journal->getPrimaryLocale()];
        }
        
        $templateMgr->assign(
            'supportedSubmissionLocaleNames',
            array_flip(array_intersect(
                array_flip(AppLocale::getAllLocales()),
                (array) $supportedSubmissionLocales
            ))
        );
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data for a new form.
     * @return void
     */
    public function initData(): void {
        $request = $this->request;
        $journal = $request->getJournal();

        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        $supportedSubmissionLocales = is_array($supportedSubmissionLocales) ? $supportedSubmissionLocales : [];
        $fallbackLocales = array_keys($supportedSubmissionLocales);
        
        $tryLocales = [
            $this->getFormLocale(),
            AppLocale::getLocale(),
            $journal->getPrimaryLocale(),
            !empty($fallbackLocales) ? $supportedSubmissionLocales[array_shift($fallbackLocales)] : null
        ];
        
        $this->_data = [];
        foreach ($tryLocales as $locale) {
            if ($locale !== null && in_array($locale, $supportedSubmissionLocales, true)) {
                $this->_data['locale'] = $locale;
                break;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData(): void {
        $this->readUserVars([
            'tempFileId', 'destination', 'issueId', 'pages', 'sectionId', 'authors',
            'primaryContact', 'title', 'abstract', 'discipline', 'subjectClass', 'subject',
            'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'language', 'sponsor',
            'citations', 'locale',
            // [WIZDAM] Funders + Deklarasi
            'funders', 'deletedFunders', 'competingInterest', 'ethicalApproval', 'generativeAiDeclaration'
        ]);

        // [WIZDAM] Normalisasi creditRoles per-penulis dan funders --
        // pola sama persis AuthorSubmitStep2Form/MetadataForm.
        if (is_array($this->_data['authors'])) {
            foreach ($this->_data['authors'] as $i => $author) {
                if (!isset($this->_data['authors'][$i]['creditRoles']) || !is_array($this->_data['authors'][$i]['creditRoles'])) {
                    $this->_data['authors'][$i]['creditRoles'] = [];
                }
            }
        }
        if (!is_array($this->_data['funders'])) {
            $this->_data['funders'] = [];
        }

        $this->readUserDateVars(['datePublished']);

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection((int) $this->getData('sectionId'));
        
        if ($section && !$section->getAbstractsNotRequired()) {
            $this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired', $this->getData('locale')));
        }
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired', $this->getData('locale')));
    }

    /**
     * Upload the submission file.
     * @param string $fileName
     * @return int|false
     */
    public function uploadSubmissionFile($fileName) {
        import('classes.file.TemporaryFileManager');
        $temporaryFileManager = new TemporaryFileManager();
        $request = $this->request;
        $user = $request->getUser();

        $temporaryFile = $temporaryFileManager->handleUpload($fileName, (int) $user->getId());

        if ($temporaryFile) {
            return (int) $temporaryFile->getId();
        }
        return false;
    }

    /**
     * Save settings.
     * @param object|null $object
     * @return void
     */
    public function execute($object = null) {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        $request = $this->request;
        $user = $request->getUser();
        $router = $request->getRouter();
        $journal = $router->getContext($request);

        $article = new Article();
        $article->setLocale($this->getData('locale'));
        $article->setUserId((int) $user->getId());
        $article->setJournalId((int) $journal->getId());
        $article->setSectionId((int) $this->getData('sectionId'));
        $article->setLanguage($this->getData('language'));
        $article->setTitle($this->getData('title'), null);
        $article->setAbstract($this->getData('abstract'), null);
        $article->setDiscipline($this->getData('discipline'), null);
        $article->setSubjectClass($this->getData('subjectClass'), null);
        $article->setSubject($this->getData('subject'), null);
        $article->setCoverageGeo($this->getData('coverageGeo'), null);
        $article->setCoverageChron($this->getData('coverageChron'), null);
        $article->setCoverageSample($this->getData('coverageSample'), null);
        $article->setType($this->getData('type'), null);
        $article->setSponsor($this->getData('sponsor'), null);
        $article->setCitations($this->getData('citations'));
        $article->setPages($this->getData('pages'));

        // [WIZDAM] Deklarasi level artikel -- QuickSubmit dipakai editor
        // untuk menambah artikel langsung (mis. digitalisasi terbitan
        // lama), jadi field ini juga perlu tersedia di sini, sama seperti
        // wizard submit dan halaman metadata editorial.
        $article->setCompetingInterest($this->getData('competingInterest'), null);
        $article->setEthicalApproval($this->getData('ethicalApproval'), null);
        $article->setGenerativeAiDeclaration($this->getData('generativeAiDeclaration'), null);

        $article->setDateSubmitted(Core::getCurrentDate());
        $article->setStatus($this->getData('destination') === 'queue' ? STATUS_QUEUED : STATUS_PUBLISHED);
        $article->setSubmissionProgress(0);
        $article->stampStatusModified();
        $article->setCurrentRound(1);
        $article->setFastTracked(1);
        $article->setHideAuthor(0);
        $article->setCommentsStatus(0);

        $articleDao->insertArticle($article);
        $articleId = (int) $article->getId();

        // Add authors
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $authors = (array) $this->getData('authors');
        
        foreach ($authors as $i => $authorData) {
            $authorId = (int) ($authorData['authorId'] ?? 0);
            
            if ($authorId > 0) {
                // [WIZDAM BUGFIX] Anotasi tipe -- lihat penjelasan di
                // import() paling atas file ini.
                /** @var Author $author */
                $author = $authorDao->getAuthor($authorId, $articleId);
                $isExistingAuthor = true;
            } else {
                $author = new Author();
                $isExistingAuthor = false;
            }

            if ($author !== null) {
                $author->setSubmissionId($articleId);
                $author->setFirstName($authorData['firstName']);
                $author->setMiddleName($authorData['middleName'] ?? '');
                $author->setLastName($authorData['lastName']);
                if (array_key_exists('affiliation', $authorData)) {
                    $author->setAffiliation($authorData['affiliation'], null);
                }
                $author->setCountry($authorData['country'] ?? '');
                $author->setEmail($authorData['email']);
                $author->setData('orcid', $authorData['orcid'] ?? '');
                $author->setUrl($authorData['url'] ?? '');
                if (array_key_exists('creditRoles', $authorData)) {
                    // [WIZDAM] Menggantikan setData('competingInterests', ...)
                    // per-penulis lama -- lihat penjelasan lengkap di
                    // MetadataForm.inc.php/AuthorSubmitStep2Form.inc.php.
                    $author->setCreditRolesArray($authorData['creditRoles'] ?? []);
                }
                $author->setBiography($authorData['biography'] ?? '', null);
                $author->setPrimaryContact(((int) $this->getData('primaryContact')) === $i ? 1 : 0);
                $author->setSequence((int) ($authorData['seq'] ?? 0));

                if (!$isExistingAuthor) {
                    $authorDao->insertAuthor($author);
                }
            }
        }

        $article->initializePermissions();
        $articleDao->updateLocaleFields($article);

        // [WIZDAM] Simpan funders (pendanaan/hibah) -- pola sama persis
        // AuthorSubmitStep2Form/MetadataForm.
        /** @var ArticleFunderDAO $funderDao */
        $funderDao = DAORegistry::getDAO('ArticleFunderDAO');
        $funders = (array) $this->getData('funders');
        foreach ($funders as $i => $funderData) {
            $funderName = trim((string) ($funderData['funderName'] ?? ''));
            if ($funderName === '') {
                continue;
            }
            $funder = $funderDao->newDataObject();
            $funder->setArticleId($articleId);
            $funder->setFunderName($funderName);
            $funder->setAwardNumber(trim((string) ($funderData['awardNumber'] ?? '')) ?: null);
            $funder->setSequence($i + 1);
            $funderDao->insertArticleFunder($funder);
        }
        // Tidak perlu penanganan "deletedFunders" di sini -- QuickSubmit
        // SELALU membuat artikel BARU (insertArticle di atas), jadi
        // tidak ada funder existing yang mungkin perlu dihapus dalam
        // satu kali proses submit.

        // Add the submission files as galleys
        import('classes.file.TemporaryFileManager');
        import('classes.file.ArticleFileManager');
        $tempFileIds = (array) $this->getData('tempFileId');
        $temporaryFileManager = new TemporaryFileManager();
        $articleFileManager = new ArticleFileManager($articleId);
        $designatedPrimary = false;
        
        foreach (array_keys($tempFileIds) as $locale) {
            $tempFileId = (int) ($tempFileIds[$locale] ?? 0);
            if ($tempFileId <= 0) continue;

            $temporaryFile = $temporaryFileManager->getFile($tempFileId, (int) $user->getId());
            if (!$temporaryFile) continue;

            $fileId = $articleFileManager->temporaryFileToArticleFile($temporaryFile, ARTICLE_FILE_SUBMISSION);
            $fileType = $temporaryFile->getFileType();

            if (str_contains($fileType, 'html')) {
                import('classes.article.ArticleHTMLGalley');
                $galley = new ArticleHTMLGalley();
            } else {
                import('classes.article.ArticleGalley');
                $galley = new ArticleGalley();
            }
            
            $galley->setArticleId($articleId);
            $galley->setFileId($fileId);
            $galley->setLocale($locale);

            if ($galley->isHTMLGalley()) {
                $galley->setLabel('HTML');
            } elseif (str_contains($fileType, 'pdf')) {
                $galley->setLabel('PDF');
            } elseif (str_contains($fileType, 'postscript')) {
                $galley->setLabel('Postscript');
            } elseif (str_contains($fileType, 'xml')) {
                $galley->setLabel('XML');
            } else {
                $galley->setLabel(__('common.untitled'));
            }

            /** @var ArticleGalleyDAO $galleyDao */
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            $galleyDao->insertGalley($galley);

            if (!$designatedPrimary) {
                $article->setSubmissionFileId($fileId);
                $article->setReviewFileId($fileId);
                if ($locale === $journal->getPrimaryLocale()) {
                    $designatedPrimary = true;
                }
            }

            // Update file search index
            import('classes.search.ArticleSearchIndex');
            $articleSearchIndex = new ArticleSearchIndex();
            $articleSearchIndex->articleFileChanged($articleId, ARTICLE_SEARCH_GALLEY_FILE, (int) $galley->getFileId());
            $articleSearchIndex->articleChangesFinished();
        }

        // Designate this as the review version by default.
        /** @var AuthorSubmissionDAO $authorSubmissionDao */
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
        $authorSubmission = $authorSubmissionDao->getAuthorSubmission($articleId);
        import('classes.submission.author.AuthorAction');
        AuthorAction::designateReviewVersion($authorSubmission, true);

        // Accept the submission
        $sectionEditorSubmission = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
        $articleFileManager = new ArticleFileManager($articleId);
        $sectionEditorSubmission->setReviewFile($articleFileManager->getFile((int) $article->getSubmissionFileId()));
        import('classes.submission.sectionEditor.SectionEditorAction');
        SectionEditorAction::recordDecision($sectionEditorSubmission, SUBMISSION_EDITOR_DECISION_ACCEPT, $this->request);

        // Create signoff infrastructure
        $copyeditInitialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
        $copyeditAuthorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId);
        $copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $articleId);
        
        $copyeditInitialSignoff->setUserId(0);
        $copyeditAuthorSignoff->setUserId((int) $user->getId());
        $copyeditFinalSignoff->setUserId(0);
        
        $signoffDao->updateObject($copyeditInitialSignoff);
        $signoffDao->updateObject($copyeditAuthorSignoff);
        $signoffDao->updateObject($copyeditFinalSignoff);

        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
        $layoutSignoff->setUserId(0);
        $signoffDao->updateObject($layoutSignoff);

        $proofAuthorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId);
        $proofProofreaderSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
        $proofLayoutEditorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
        
        $proofAuthorSignoff->setUserId((int) $user->getId());
        $proofProofreaderSignoff->setUserId(0);
        $proofLayoutEditorSignoff->setUserId(0);
        
        $signoffDao->updateObject($proofAuthorSignoff);
        $signoffDao->updateObject($proofProofreaderSignoff);
        $signoffDao->updateObject($proofLayoutEditorSignoff);

        import('classes.author.form.submit.AuthorSubmitForm');
        AuthorSubmitForm::assignEditors($article);

        $articleDao->updateArticle($article);

        // Add to end of editing queue
        import('classes.submission.editor.EditorAction');
        if (isset($galley)) {
            EditorAction::expediteSubmission($article, $this->request);
        }

        if ($this->getData('destination') === 'issue') {
            $issueId = (int) $this->getData('issueId'); // [WIZDAM FIX] Corrected variable assignment casting
            $this->scheduleForPublication($articleId, $issueId);
        }

        // Import the references list.
        /** @var CitationDAO $citationDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $rawCitationList = $article->getCitations();
        $citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $articleId, $rawCitationList);

        // Index article.
        import('classes.search.ArticleSearchIndex');
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->articleMetadataChanged($article);
        $articleSearchIndex->articleChangesFinished();
    }

    /**
     * Schedule an article for publication in a given issue
     * @param int $articleId
     * @param int $issueId
     * @return void
     */
    public function scheduleForPublication($articleId, $issueId): void {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');

        $request = $this->request;
        $journal = $request->getJournal();
        $submission = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($articleId);
        $issue = $issueDao->getIssueById($issueId, (int) $journal->getId());
        $bigNumber = defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999;

        if ($issue) {
            if ($publishedArticle) {
                $publishedArticle->setIssueId($issueId);
                $publishedArticleDao->updatePublishedArticle($publishedArticle);
            } else {
                $publishedArticle = new PublishedArticle();
                $publishedArticle->setId($submission->getId());
                $publishedArticle->setIssueId($issueId);
                $publishedArticle->setDatePublished($this->getData('datePublished'));
                $publishedArticle->setSeq($bigNumber);
                $publishedArticle->setAccessStatus(ARTICLE_ACCESS_ISSUE_DEFAULT);

                $publishedArticleDao->insertPublishedArticle($publishedArticle);
                $publishedArticleDao->resequencePublishedArticles((int) $submission->getSectionId(), $issueId);

                if ($sectionDao->customSectionOrderingExists($issueId)) {
                    if ($sectionDao->getCustomSectionOrder($issueId, (int) $submission->getSectionId()) === null) {
                        $sectionDao->insertCustomSectionOrder($issueId, (int) $submission->getSectionId(), $bigNumber);
                        $sectionDao->resequenceCustomSectionOrders($issueId);
                    }
                }
            }
        } else {
            if ($publishedArticle) {
                $publishedArticleDao->resequencePublishedArticles((int) $submission->getSectionId(), (int) $publishedArticle->getIssueId());
                $publishedArticleDao->deletePublishedArticleByArticleId($articleId);
            }
        }
        
        $submission->stampStatusModified();

        if ($issue && $issue->getPublished()) {
            $submission->setStatus(STATUS_PUBLISHED);
            if ($publishedArticle && !$publishedArticle->getDatePublished()) {
                $publishedArticle->setDatePublished($issue->getDatePublished());
            }
        } else {
            $submission->setStatus(STATUS_QUEUED);
        }

        $sectionEditorSubmissionDao->updateSectionEditorSubmission($submission);
        
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);
        $article->initializePermissions();
        $articleDao->updateLocaleFields($article);
    }

}
?>