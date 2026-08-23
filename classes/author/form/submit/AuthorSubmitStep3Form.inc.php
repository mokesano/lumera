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
 */

import('classes.author.form.submit.AuthorSubmitForm');
import('classes.article.Author');

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
        $this->addCheck(new FormValidatorCustom(
            $this, 'authors', 'required', 'author.submit.form.authorRequired',
            function($authors) { return count($authors) > 0; }
        ));

        $this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', ['firstName', 'lastName']));

        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'authors', 'required', 'author.submit.form.authorRequiredFields',
            function($email, $regExp) { return PKPString::regexp_match($regExp, $email); },
            [ValidatorEmail::getRegexp()],
            false,
            ['email']
        ));
        
        // URL validation
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'authors', 'required', 'user.profile.form.urlInvalid',
            function($url, $regExp) { return empty($url) ? true : PKPString::regexp_match($regExp, $url); },
            [ValidatorUrl::getRegexp()],
            false,
            ['url']
        ));

        // Add ORCiD validation
        import('lib.pkp.classes.validation.ValidatorORCID');
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

        // [WIZDAM] Validasi funders -- keseluruhan field OPSIONAL (tidak
        // semua artikel punya pendanaan eksternal), TAPI kalau satu baris
        // funder sudah ditambahkan lewat grid, funderName WAJIB diisi
        // (awardNumber tetap opsional -- tidak semua pengakuan pendanaan
        // menyertakan nomor hibah spesifik). Pola FormValidatorArrayCustom
        // mengikuti persis validasi email/url authors di atas.
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'funders', 'required', 'author.submit.form.funderNameRequired',
            function($funderName) { return trim((string) $funderName) !== ''; },
            [],
            false,
            ['funderName']
        ));

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId());
        $abstractWordCount = $section->getAbstractWordCount();

        if (isset($abstractWordCount) && $abstractWordCount > 0) {
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
     * @param Article $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function AuthorSubmitStep3Form($article, $journal, $request) {
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
     * Get the article associated with this object.
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
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $formLocales = $this->getSubmissionLocales();

        if (isset($this->article)) {
            $article = $this->article;
            $this->_data = [
                'authors' => [],
                'funders' => [],
                'title' => $article->getTitle(null),
                'abstract' => $article->getAbstract(null),
                'discipline' => $article->getDiscipline(null),
                'subjectClass' => $article->getSubjectClass(null),
                'subject' => $article->getSubject(null),
                'coverageGeo' => $article->getCoverageGeo(null),
                'coverageChron' => $article->getCoverageChron(null),
                'coverageSample' => $article->getCoverageSample(null),
                'type' => $article->getType(null),
                'language' => $article->getLanguage(),
                'sponsor' => $article->getSponsor(null),
                'section' => $sectionDao->getSection($article->getSectionId()),
                'citations' => $article->getCitations()
            ];

            if (!is_array($this->_data['title'])) $this->_data['title'] = [];
            if (!is_array($this->_data['abstract'])) $this->_data['abstract'] = [];
            foreach ($formLocales as $locale) {
                if (!isset($this->_data['title'][$locale])) $this->_data['title'][$locale] = '';
                if (!isset($this->_data['abstract'][$locale])) $this->_data['abstract'][$locale] = '';
            }

            $authors = $article->getAuthors();
            for ($i=0, $count=count($authors); $i < $count; $i++) {
                $affiliationArray = $authors[$i]->getAffiliation(null);
                $competingInterestsArray = $authors[$i]->getCompetingInterests(null);
                $biographyArray = $authors[$i]->getBiography(null);

                $affiliationArray = is_array($affiliationArray) ? $affiliationArray : [];
                $competingInterestsArray = is_array($competingInterestsArray) ? $competingInterestsArray : [];
                $biographyArray = is_array($biographyArray) ? $biographyArray : [];

                foreach ($formLocales as $locale) {
                    if (!isset($affiliationArray[$locale])) $affiliationArray[$locale] = '';
                    if (!isset($competingInterestsArray[$locale])) $competingInterestsArray[$locale] = '';
                    if (!isset($biographyArray[$locale])) $biographyArray[$locale] = '';
                }

                $this->_data['authors'][] = [
                    'authorId' => $authors[$i]->getId(),
                    'firstName' => $authors[$i]->getFirstName(),
                    'middleName' => $authors[$i]->getMiddleName(),
                    'lastName' => $authors[$i]->getLastName(),
                    'affiliation' => $affiliationArray,
                    'country' => $authors[$i]->getCountry(),
                    'email' => $authors[$i]->getEmail(),
                    'orcid' => $authors[$i]->getData('orcid'),
                    'url' => $authors[$i]->getUrl(),
                    'competingInterests' => $competingInterestsArray,
                    'biography' => $biographyArray
                ];
                if ($authors[$i]->getPrimaryContact()) {
                    $this->setData('primaryContact', $i);
                }
            }

            // [WIZDAM] Isi data funders (pendanaan/hibah) dari database --
            // pola sama seperti authors di atas, tapi TANPA perulangan
            // multi-bahasa karena funder_name/award_number bukan field
            // terlokalisasi.
            /** @var ArticleFunderDAO $funderDao */
            $funderDao = DAORegistry::getDAO('ArticleFunderDAO');
            $funders = $funderDao->getByArticleId($article->getId())->toArray();
            foreach ($funders as $funder) {
                $this->_data['funders'][] = [
                    'funderId' => $funder->getId(),
                    'funderName' => $funder->getFunderName(),
                    'awardNumber' => $funder->getAwardNumber(),
                ];
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
            'funders',
            'deletedFunders',
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

        $formLocales = $this->getSubmissionLocales();

        if (!is_array($this->_data['title'])) $this->_data['title'] = [];
        if (!is_array($this->_data['abstract'])) $this->_data['abstract'] = [];
        foreach ($formLocales as $formLocale) {
            if (!isset($this->_data['title'][$formLocale])) $this->_data['title'][$formLocale] = '';
            if (!isset($this->_data['abstract'][$formLocale])) $this->_data['abstract'][$formLocale] = '';
        }

        if (is_array($this->_data['authors'])) {
            foreach ($this->_data['authors'] as $i => $author) {
                if (!isset($author['affiliation']) || !is_array($author['affiliation'])) {
                    $this->_data['authors'][$i]['affiliation'] = [];
                }
                if (!isset($author['competingInterests']) || !is_array($author['competingInterests'])) {
                    $this->_data['authors'][$i]['competingInterests'] = [];
                }
                if (!isset($author['biography']) || !is_array($author['biography'])) {
                    $this->_data['authors'][$i]['biography'] = [];
                }

                foreach ($formLocales as $formLocale) {
                    if (!isset($this->_data['authors'][$i]['affiliation'][$formLocale])) {
                        $this->_data['authors'][$i]['affiliation'][$formLocale] = '';
                    }
                    if (!isset($this->_data['authors'][$i]['competingInterests'][$formLocale])) {
                        $this->_data['authors'][$i]['competingInterests'][$formLocale] = '';
                    }
                    if (!isset($this->_data['authors'][$i]['biography'][$formLocale])) {
                        $this->_data['authors'][$i]['biography'][$formLocale] = '';
                    }
                }
            }
        } else {
            $this->_data['authors'] = [];
        }

        // [WIZDAM] Normalisasi funders -- pola sama seperti authors di
        // atas, tapi tanpa perulangan locale.
        if (!is_array($this->_data['funders'])) {
            $this->_data['funders'] = [];
        }

        // Load the section.
        /** @var SectionDAO $sectionDao */
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

        /** @var CountryDAO $countryDao  */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
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
        /** @var ArticleDAO $articleDao  */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var AuthorDAO $authorDao  */
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
                /** @var Author $author */
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

                // [LUMERA] HookRegistry dispatch using array construction for references
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

        // [WIZDAM] Simpan funders (pendanaan/hibah) -- pola PERSIS sama
        // dengan authors di atas (insert baru kalau funderId <= 0,
        // update kalau sudah ada), tapi tanpa penanganan
        // primaryContact/HookRegistry khusus karena funder tidak
        // punya konsep setara itu.
        /** @var ArticleFunderDAO $funderDao */
        $funderDao = DAORegistry::getDAO('ArticleFunderDAO');
        $funders = $this->getData('funders');
        if (is_array($funders)) {
            for ($i = 0, $count = count($funders); $i < $count; $i++) {
                $funderName = trim((string) ($funders[$i]['funderName'] ?? ''));
                if ($funderName === '') {
                    // Baris kosong (mis. ditambahkan lewat grid lalu tidak
                    // diisi) -- dilewati, tidak disimpan sebagai baris
                    // funder kosong.
                    continue;
                }

                $funderId = (int) ($funders[$i]['funderId'] ?? 0);
                if ($funderId > 0) {
                    $funder = $funderDao->getById($funderId, $article->getId());
                    $isExistingFunder = ($funder !== null);
                } else {
                    $funder = null;
                    $isExistingFunder = false;
                }
                if ($funder === null) {
                    $funder = $funderDao->newDataObject();
                    $isExistingFunder = false;
                }

                $funder->setArticleId($article->getId());
                $funder->setFunderName($funderName);
                $funder->setAwardNumber(trim((string) ($funders[$i]['awardNumber'] ?? '')) ?: null);
                $funder->setSequence($i + 1);

                if ($isExistingFunder) {
                    $funderDao->updateArticleFunder($funder);
                } else {
                    $funderDao->insertArticleFunder($funder);
                }
                unset($funder);
            }
        }

        // Remove deleted funders
        $deletedFunders = preg_split('/:/', (string) $this->getData('deletedFunders'), -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0, $count = count($deletedFunders); $i < $count; $i++) {
            $funderDao->deleteById((int) $deletedFunders[$i], $article->getId());
        }

        parent::execute();

        // Save the article
        $articleDao->updateArticle($article);

        // Update references list if it changed.
        /** @var CitationDAO $citationDao  */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $rawCitationList = $article->getCitations();
        if ($previousRawCitationList != $rawCitationList) {
            // [LUMERA] Ensure request is available
            $request = $this->request ? $this->request : Application::get()->getRequest();
            $citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $article->getId(), $rawCitationList);
        }

        return $this->articleId;
    }

}
?>