<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep3Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 3 of author article submission.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep3Form extends AuthorSubmitForm {

    /**
     * Constructor.
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function __construct($article, $journal, $request) {
        parent::__construct($article, 3, $journal, $request);

        // Validation checks for this form
        // [WIZDAM] Replaced create_function with Closure
        $this->addCheck(new FormValidatorCustom(
            $this, 'authors', 'required', 'author.submit.form.authorRequired',
            function($authors) { return count($authors) > 0; }
        ));

        $this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', ['firstName', 'lastName']));

        // [WIZDAM] Replaced create_function with Closure for Email Validation
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'authors', 'required', 'author.submit.form.authorRequiredFields',
            function($email, $regExp) { return PKPString::regexp_match($regExp, $email); },
            [ValidatorEmail::getRegexp()],
            false,
            ['email']
        ));
        
        // URL validation
        // [WIZDAM] Replaced create_function with Closure
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'authors', 'required', 'user.profile.form.urlInvalid',
            function($url, $regExp) { return empty($url) ? true : PKPString::regexp_match($regExp, $url); },
            [ValidatorUrl::getRegexp()],
            false,
            ['url']
        ));

        // Add ORCiD validation
        import('lib.pkp.classes.validation.ValidatorORCID');
        // [WIZDAM] Replaced create_function with Closure
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'authors', 'required', 'user.profile.form.orcidInvalid',
            function($orcid) {
                $validator = new ValidatorORCID();
                return empty($orcid) ? true : $validator->isValid($orcid);
            },
            [],
            false,
            ['orcid']
        ));

        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired', $this->getRequiredLocale()));

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId());
        $abstractWordCount = $section->getAbstractWordCount();

        if (isset($abstractWordCount) && $abstractWordCount > 0) {
            // [WIZDAM] Replaced create_function with Closure for Word Count
            $this->addCheck(new FormValidatorCustom(
                $this, 'abstract', 'required', 'author.submit.form.wordCountAlert',
                function($abstract, $wordCount) {
                    foreach ($abstract as $localizedAbstract) {
                        return count(preg_split("/\s+/", trim(str_replace("&nbsp;", " ", strip_tags($localizedAbstract))))) <= $wordCount;
                    }
                    return true;
                },
                [$abstractWordCount]
            ));
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorSubmitStep3Form($article, $journal, $request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($article, $journal, $request);
    }

    /**
     * Get the article associated with this object.
     *
     * @return Article The article instance.
     * @throws Exception If the article cannot be retrieved.
     */
    public function getArticle() {
        return $this->article;
    }

    /**
     * Initialize form data from current article.
     */
    public function initData() {
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        if (isset($this->article)) {
            $article = $this->article;
            $this->_data = [
                'authors' => [],
                'title' => $article->getTitle(null), // Localized
                'abstract' => $article->getAbstract(null), // Localized
                'discipline' => $article->getDiscipline(null), // Localized
                'subjectClass' => $article->getSubjectClass(null), // Localized
                'subject' => $article->getSubject(null), // Localized
                'coverageGeo' => $article->getCoverageGeo(null), // Localized
                'coverageChron' => $article->getCoverageChron(null), // Localized
                'coverageSample' => $article->getCoverageSample(null), // Localized
                'type' => $article->getType(null), // Localized
                'language' => $article->getLanguage(),
                'sponsor' => $article->getSponsor(null), // Localized
                'section' => $sectionDao->getSection($article->getSectionId()),
                'citations' => $article->getCitations()
            ];

            $authors = $article->getAuthors();
            for ($i=0, $count=count($authors); $i < $count; $i++) {
                $this->_data['authors'][] = [
                    'authorId' => $authors[$i]->getId(),
                    'firstName' => $authors[$i]->getFirstName(),
                    'middleName' => $authors[$i]->getMiddleName(),
                    'lastName' => $authors[$i]->getLastName(),
                    'affiliation' => $authors[$i]->getAffiliation(null),
                    'country' => $authors[$i]->getCountry(),
                    'email' => $authors[$i]->getEmail(),
                    'orcid' => $authors[$i]->getData('orcid'),
                    'url' => $authors[$i]->getUrl(),
                    'competingInterests' => $authors[$i]->getCompetingInterests(null),
                    'biography' => $authors[$i]->getBiography(null)
                ];
                if ($authors[$i]->getPrimaryContact()) {
                    $this->setData('primaryContact', $i);
                }
            }
        }
        return parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'authors',
            'deletedAuthors',
            'primaryContact',
            'title',
            'abstract',
            'discipline',
            'subjectClass',
            'subject',
            'coverageGeo',
            'coverageChron',
            'coverageSample',
            'type',
            'language',
            'sponsor',
            'citations'
        ]);

        // Load the section. This is used in the step 3 form to
        // determine whether or not to display indexing options.
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $this->_data['section'] = $sectionDao->getSection($this->article->getSectionId());

        if ($this->_data['section']->getAbstractsNotRequired() == 0) {
            $this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired', $this->getRequiredLocale()));
        }
    }

    /**
     * Get the names of fields for which data should be localized
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), [
            'title', 'abstract', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 
            'coverageSample', 'type', 'sponsor'
        ]);
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        // Ensure internal state consistency
        if (!$this->request) $this->request = $request;

        $templateMgr = TemplateManager::getManager($request);

        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
        // [WIZDAM] Use assign instead of assign_by_ref
        $templateMgr->assign('countries', $countries);

        if ($this->request->getUserVar('addAuthor') || $this->request->getUserVar('delAuthor')  || $this->request->getUserVar('moveAuthor')) {
            $templateMgr->assign('scrollToAuthor', true);
        }

        parent::display($request, $template);
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $article = $this->article;

        // Retrieve the previous citation list for comparison.
        $previousRawCitationList = $article->getCitations();

        // Update article
        $article->setTitle($this->getData('title'), null); // Localized
        $article->setAbstract($this->getData('abstract'), null); // Localized
        $article->setDiscipline($this->getData('discipline'), null); // Localized
        $article->setSubjectClass($this->getData('subjectClass'), null); // Localized
        $article->setSubject($this->getData('subject'), null); // Localized
        $article->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
        $article->setCoverageChron($this->getData('coverageChron'), null); // Localized
        $article->setCoverageSample($this->getData('coverageSample'), null); // Localized
        $article->setType($this->getData('type'), null); // Localized
        $article->setLanguage($this->getData('language'));
        $article->setSponsor($this->getData('sponsor'), null); // Localized
        $article->setCitations($this->getData('citations'));
        if ($article->getSubmissionProgress() <= $this->step) {
            $article->stampStatusModified();
            $article->setSubmissionProgress($this->step + 1);
        }

        // Update authors
        $authors = $this->getData('authors');
        for ($i=0, $count=count($authors); $i < $count; $i++) {
            if ($authors[$i]['authorId'] > 0) {
                // Update an existing author
                $author = $authorDao->getAuthor($authors[$i]['authorId'], $article->getId());
                $isExistingAuthor = true;

            } else {
                // Create a new author
                $author = new Author();
                $isExistingAuthor = false;
            }

            if ($author != null) {
                $author->setSubmissionId($article->getId());
                $author->setFirstName($authors[$i]['firstName']);
                $author->setMiddleName($authors[$i]['middleName']);
                $author->setLastName($authors[$i]['lastName']);
                $author->setAffiliation($authors[$i]['affiliation'], null);
                $author->setCountry($authors[$i]['country']);
                $author->setEmail($authors[$i]['email']);
                $author->setData('orcid', $authors[$i]['orcid']);
                $author->setUrl($authors[$i]['url']);
                if (array_key_exists('competingInterests', $authors[$i])) {
                    $author->setCompetingInterests($authors[$i]['competingInterests'], null);
                }
                $author->setBiography($authors[$i]['biography'], null);
                $author->setPrimaryContact($this->getData('primaryContact') == $i ? 1 : 0);
                $author->setSequence($authors[$i]['seq']);

                // [WIZDAM] HookRegistry call using array construction for references
                HookRegistry::dispatch('Author::Form::Submit::AuthorSubmitStep3Form::Execute', [&$author, &$authors[$i]]);

                if ($isExistingAuthor) {
                    $authorDao->updateAuthor($author);
                } else {
                    $authorDao->insertAuthor($author);
                }
            }
            unset($author);
        }

        // Remove deleted authors
        $deletedAuthors = preg_split('/:/', $this->getData('deletedAuthors'), -1,  PREG_SPLIT_NO_EMPTY);
        for ($i=0, $count=count($deletedAuthors); $i < $count; $i++) {
            $authorDao->deleteAuthorById($deletedAuthors[$i], $article->getId());
        }

        parent::execute();

        // Save the article
        $articleDao->updateArticle($article);

        // Update references list if it changed.
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $rawCitationList = $article->getCitations();
        if ($previousRawCitationList != $rawCitationList) {
            // [WIZDAM] Ensure request is available
            $request = $this->request ? $this->request : Application::get()->getRequest();
            $citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $article->getId(), $rawCitationList);
        }

        return $this->articleId;
    }
}

?>