<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/MetadataForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetadataForm
 * @ingroup submission_form
 *
 * @brief Form to change metadata information for a submission.
 */

import('lib.pkp.classes.form.Form');
import('classes.article.Author');

define('COVER_PAGE_IMAGE_NAME', 'coverPage');

class MetadataForm extends Form {

    /** @var object|null Article current article */
    public $article = null;

    /** @var boolean can edit metadata */
    public $canEdit = false;

    /** @var boolean can view authors */
    public $canViewAuthors = false;

    /** @var boolean is an Editor, can edit all metadata */
    public $isEditor = false;

    /** @var array Locale keys (as array keys) this form instance must render fields for */
    protected $formLocales = [];

    /**
     * Constructor.
     * @param object $article Article
     * @param object $journal Journal
     */
    public function __construct($article, $journal) {
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        // [LUMERA] Request Singleton
        $request = Application::get()->getRequest();
        $user = $request->getUser();
        $roleId = $roleDao->getRoleIdFromPath($request->getRequestedPage());

        // If the user is an editor of this article, make the entire form editable.
        $this->canEdit = false;
        $this->isEditor = false;
        if ($roleId != null && ($roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_SECTION_EDITOR)) {
            $this->canEdit = true;
            $this->isEditor = true;
        }

        $copyeditInitialSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $article->getId());
        // If the user is an author and the article hasn't passed the Copyediting stage, make the form editable.
        if ($roleId == ROLE_ID_AUTHOR) {
            if ($article->getStatus() != STATUS_PUBLISHED && ($copyeditInitialSignoff == null || $copyeditInitialSignoff->getDateCompleted() == null)) {
                $this->canEdit = true;
            }
        }

        // Copy editors are also allowed to edit metadata, but only if they have
        // a current assignment to the article.
        if ($roleId != null && ($roleId == ROLE_ID_COPYEDITOR)) {
            $copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $article->getId());
            if ($copyeditFinalSignoff != null && $article->getStatus() != STATUS_PUBLISHED) {
                if ($copyeditInitialSignoff->getDateNotified() != null && $copyeditFinalSignoff->getDateCompleted() == null) {
                    $this->canEdit = true;
                }
            }
        }

        if ($this->canEdit) {
            $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
            if (empty($supportedSubmissionLocales)) $supportedSubmissionLocales = [$journal->getPrimaryLocale()];

            parent::__construct(
                'submission/metadata/metadataEdit.tpl',
                true,
                $article->getLocale(),
                array_flip(array_intersect(
                    array_flip(AppLocale::getAllLocales()),
                    $supportedSubmissionLocales
                ))
            );
            $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired', $this->getRequiredLocale()));
            $this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', ['firstName', 'lastName']));
            
            // [LUMERA] Replaced create_function with anonymous functions
            $this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', 
                function($email, $regExp) {
                    return PKPString::regexp_match($regExp, $email);
                }, 
                [ValidatorEmail::getRegexp()], false, ['email']
            ));
            
            $this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'user.profile.form.urlInvalid', 
                function($url, $regExp) {
                    return empty($url) ? true : PKPString::regexp_match($regExp, $url);
                }, 
                [ValidatorUrl::getRegexp()], false, ['url']
            ));

            // Add ORCiD validation
            import('lib.pkp.classes.validation.ValidatorORCID');
            $this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'user.profile.form.orcidInvalid', 
                function($orcid) {
                    $validator = new ValidatorORCID();
                    return empty($orcid) ? true : $validator->isValid($orcid);
                }, 
                [], false, ['orcid']
            ));

        } else {
            parent::__construct('submission/metadata/metadataView.tpl');
        }

        // If the user is a reviewer of this article, do not show authors.
        $this->canViewAuthors = true;
        if ($roleId != null && $roleId == ROLE_ID_REVIEWER) {
            $this->canViewAuthors = false;
        }

        $this->article = $article;

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param object $article Article
     * @param object $journal Journal
     */
    public function MetadataForm($article, $journal) {
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
     * Get the default form locale.
     * @return string
     */
    public function getDefaultFormLocale() {
        if ($this->article) return $this->article->getLocale();
        return parent::getDefaultFormLocale();
    }
    
    /**
     * [LUMERA] Universal getter for article object across all form types.
     * Helps plugins access the protected $article property safely.
     */
    public function getArticle() {
        // Cek apakah properti $article ada di instance class ini (misal di MetadataForm atau AuthorSubmitForm)
        if (property_exists($this, 'article')) {
            return $this->article;
        }
        
        // Fallback: Jika di kelas tersebut menggunakan nama variabel lain atau tidak ada
        return null;
    }

    /**
     * Initialize form data from current article.
     */
    public function initData() {
        if (isset($this->article)) {
            $article = $this->article;
            $this->_data = [
                'authors' => [],
                'title' => $article->getTitle(null) ?? [],
                'abstract' => $article->getAbstract(null) ?? [],
                'coverPageAltText' => $article->getCoverPageAltText(null) ?? [],
                'showCoverPage' => $article->getShowCoverPage(null) ?? [],
                'hideCoverPageToc' => $article->getHideCoverPageToc(null) ?? [],
                'hideCoverPageAbstract' => $article->getHideCoverPageAbstract(null) ?? [],
                'originalFileName' => $article->getOriginalFileName(null) ?? [],
                'fileName' => $article->getFileName(null) ?? [],
                'width' => $article->getWidth(null) ?? [],
                'height' => $article->getHeight(null) ?? [],
                'discipline' => $article->getDiscipline(null) ?? [],
                'subjectClass' => $article->getSubjectClass(null) ?? [],
                'subject' => $article->getSubject(null) ?? [],
                'coverageGeo' => $article->getCoverageGeo(null) ?? [],
                'coverageChron' => $article->getCoverageChron(null) ?? [],
                'coverageSample' => $article->getCoverageSample(null) ?? [],
                'type' => $article->getType(null) ?? [],
                'language' => $article->getLanguage(),
                'sponsor' => $article->getSponsor(null) ?? [],
                'citations' => $article->getCitations(),
                'hideAuthor' => $article->getHideAuthor(),
                // [WIZDAM] Funders (pendanaan/hibah terstruktur) -- pola
                // sama persis AuthorSubmitStep2Form, supaya editor bisa
                // mengelola Funders dari halaman metadata editorial
                // (review/copyediting), TIDAK cuma dari wizard submit
                // yang hanya berlaku sekali di awal.
                'funders' => [],
                // [WIZDAM] Deklarasi level artikel (SATU pernyataan
                // mencakup seluruh penulis, sama seperti Step 3 wizard
                // submit baru) -- lihat Article::getCompetingInterest()
                // dkk.
                'competingInterest' => $article->getCompetingInterest(null) ?? [],
                'ethicalApproval' => $article->getEthicalApproval(null) ?? [],
                'generativeAiDeclaration' => $article->getGenerativeAiDeclaration(null) ?? [],
            ];

            // [WIZDAM] Isi teks default (boilerplate) untuk locale yang
            // BELUM punya nilai deklarasi sama sekali -- supaya pengguna
            // tinggal MENGEDIT pernyataan, bukan menulis dari nol. Teks
            // default memakai locale key yang SAMA dengan fallback
            // tampilan publik di article.tpl (article.*.statement), jadi
            // konsisten dengan apa yang akhirnya tampil ke pembaca kalau
            // field dibiarkan kosong. HANYA berlaku saat form BENAR-BENAR
            // bisa diedit ($this->canEdit) -- metadataView.tpl (read-only)
            // TIDAK disentuh, tetap pakai fallback "&mdash;"-nya sendiri.
            if ($this->canEdit) {
                $declarationDefaults = [
                    'competingInterest' => 'article.competingInterest.statement',
                    'ethicalApproval' => 'article.ethicalApproval.statement',
                    'generativeAiDeclaration' => 'article.generativeAiDeclaration.statement',
                ];
                foreach ($declarationDefaults as $field => $defaultKey) {
                    if (!is_array($this->_data[$field])) $this->_data[$field] = [];
                    foreach (array_keys((array) $this->supportedLocales) as $locale) {
                        if (trim((string) ($this->_data[$field][$locale] ?? '')) === '') {
                            $this->_data[$field][$locale] = __($defaultKey, [], $locale);
                        }
                    }
                }
            }

            /** @var ArticleFunderDAO $funderDao */
            $funderDao = DAORegistry::getDAO('ArticleFunderDAO');
            foreach ($funderDao->getByArticleId($article->getId())->toArray() as $funder) {
                $this->_data['funders'][] = [
                    'funderId' => $funder->getId(),
                    'funderName' => $funder->getFunderName(),
                    'awardNumber' => $funder->getAwardNumber(),
                ];
            }
            // consider the additional field names from the public identifer plugins
            import('classes.plugins.PubIdPluginHelper');
            $pubIdPluginHelper = new PubIdPluginHelper();
            $pubIdPluginHelper->init($this, $article);

            $authors = $article->getAuthors();
            for ($i=0, $count=count($authors); $i < $count; $i++) {
                array_push(
                    $this->_data['authors'],
                    [
                        'authorId' => $authors[$i]->getId(),
                        'firstName' => $authors[$i]->getFirstName(),
                        'middleName' => $authors[$i]->getMiddleName(),
                        'lastName' => $authors[$i]->getLastName(),
                        'affiliation' => $authors[$i]->getAffiliation(null) ?? [],
                        'country' => $authors[$i]->getCountry(),
                        'countryLocalized' => $authors[$i]->getCountryLocalized(),
                        'email' => $authors[$i]->getEmail(),
                        'orcid' => $authors[$i]->getData('orcid'),
                        'url' => $authors[$i]->getUrl(),
                        // [WIZDAM] competingInterests per-penulis DIHAPUS
                        // dari sini -- digantikan competingInterest LEVEL
                        // ARTIKEL di atas (konsisten dengan wizard submit
                        // baru). Digantikan creditRoles (CRediT, BEDA
                        // kasus -- memang per-penulis, terstruktur).
                        'creditRoles' => $authors[$i]->getCreditRolesArray(),
                        'biography' => $authors[$i]->getBiography(null) ?? []
                    ]
                );
            }
            // [WIZDAM BUGFIX] primaryContact SEKARANG ARRAY berisi index
            // SEMUA penulis yang ditandai "Principal contact for editorial
            // correspondence" -- sebelumnya cuma menyimpan index TERAKHIR
            // yang ditemukan (di dalam loop di atas) karena disangka selalu
            // tunggal, padahal author.primaryContact memang sudah berupa
            // flag per-baris di database (mendukung banyak penulis
            // sekaligus) -- pembatasan "hanya satu" murni di radio button
            // template, bukan di data. Dibangun di loop TERPISAH supaya
            // urutannya tetap sesuai urutan penulis, bukan urutan ditemukan.
            $this->_data['primaryContact'] = [];
            foreach ($authors as $i => $author) {
                if ($author->getPrimaryContact()) {
                    $this->_data['primaryContact'][] = $i;
                }
            }
            if ($this->isEditor) {
                $this->setData('copyrightHolder', $article->getCopyrightHolder(null) ?? []);
                $this->setData('copyrightYear', $article->getCopyrightYear());
                $this->setData('licenseURL', $article->getLicenseURL());
            }
        }
        return parent::initData();
    }

    /**
     * Get the field names for which data can be localized
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), [
            'title', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
            'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor', 'citations',
            'copyrightHolder', 'competingInterest', 'ethicalApproval', 'generativeAiDeclaration'
        ]);
    }

    /**
     * Display the form.
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Request
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();
        
        /** @var JournalSettingsDAO $settingsDao */
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR); // editor.cover.xxx locale keys; FIXME?

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', isset($this->article) ? $this->article->getId() : null);
        $templateMgr->assign('journalSettings', $settingsDao->getJournalSettings($journal->getId()));
        $templateMgr->assign('rolePath', $request->getRequestedPage());
        $templateMgr->assign('canViewAuthors', $this->canViewAuthors);

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $templateMgr->assign('countries', $countryDao->getCountries());

        $templateMgr->assign('helpTopicId','submission.indexingAndMetadata');
        if ($this->article) {
            $templateMgr->assign('section', $sectionDao->getSection($this->article->getSectionId()));
        }

        if ($this->isEditor) {
            import('classes.article.Article');
            $hideAuthorOptions = [
                AUTHOR_TOC_DEFAULT => AppLocale::Translate('editor.article.hideTocAuthorDefault'),
                AUTHOR_TOC_HIDE => AppLocale::Translate('editor.article.hideTocAuthorHide'),
                AUTHOR_TOC_SHOW => AppLocale::Translate('editor.article.hideTocAuthorShow')
            ];
            $templateMgr->assign('hideAuthorOptions', $hideAuthorOptions);
            $templateMgr->assign('isEditor', true);
        }
        // consider public identifiers
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->assign('article', $this->article);
        $templateMgr->assign('allCreditRoles', Author::getAllCreditRoles());

        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'articleId',
                'authors',
                'deletedAuthors',
                'primaryContact',
                'title',
                'abstract',
                'coverPageAltText',
                'showCoverPage',
                'hideCoverPageToc',
                'hideCoverPageAbstract',
                'originalFileName',
                'fileName',
                'width',
                'height',
                'discipline',
                'subjectClass',
                'subject',
                'coverageGeo',
                'coverageChron',
                'coverageSample',
                'type',
                'language',
                'sponsor',
                'citations',
                'hideAuthor',
                // [WIZDAM] Funders + Deklarasi
                'funders',
                'deletedFunders',
                'competingInterest',
                'ethicalApproval',
                'generativeAiDeclaration'
            ]
        );
        if ($this->isEditor) {
            $this->readUserVars(['copyrightHolder', 'copyrightYear', 'licenseURL']);
        }

        // [WIZDAM BUGFIX] primaryContact sekarang checkbox multi-pilih
        // (name="primaryContact[]"), BUKAN lagi radio button tunggal --
        // sama seperti creditRoles, checkbox yang TIDAK dicentang sama
        // sekali TIDAK ikut terkirim dalam POST, jadi kalau key-nya hilang
        // berarti tidak ada yang dicentang (array kosong), bukan error.
        if (!isset($this->_data['primaryContact']) || !is_array($this->_data['primaryContact'])) {
            $this->_data['primaryContact'] = [];
        }
        $this->_data['primaryContact'] = array_values(array_unique(array_map('intval', $this->_data['primaryContact'])));

        // [WIZDAM] Normalisasi creditRoles per-penulis -- checkbox HTML
        // yang TIDAK dicentang tidak ikut terkirim sama sekali dalam
        // POST, jadi kalau key-nya hilang, berarti tidak ada peran
        // dipilih (array kosong), BUKAN error. Pola sama persis
        // AuthorSubmitStep2Form::readInputData().
        if (is_array($this->_data['authors'])) {
            foreach ($this->_data['authors'] as $i => $author) {
                if (!isset($this->_data['authors'][$i]['creditRoles']) || !is_array($this->_data['authors'][$i]['creditRoles'])) {
                    $this->_data['authors'][$i]['creditRoles'] = [];
                }
            }
        }

        // [WIZDAM] Normalisasi funders.
        if (!is_array($this->_data['funders'])) {
            $this->_data['funders'] = [];
        }

        // consider the additional field names from the public identifer plugins
        import('classes.plugins.PubIdPluginHelper');
        $pubIdPluginHelper = new PubIdPluginHelper();
        $pubIdPluginHelper->readInputData($this);

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($this->article->getSectionId());
        if (!$section->getAbstractsNotRequired()) {
            $this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired', $this->getRequiredLocale()));
        }

    }

    /**
     * Check to ensure that the form is correctly validated.
     */
    public function validate($callHooks = true) {
        // Verify that an image cover, if supplied, is actually an image.
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        if ($publicFileManager->uploadedFileExists(COVER_PAGE_IMAGE_NAME)) {
            $type = $publicFileManager->getUploadedFileType(COVER_PAGE_IMAGE_NAME);
            $extension = $publicFileManager->getImageExtension($type);
            if (!$extension) {
                // Not a valid image.
                $this->addError('imageFile', __('submission.layout.imageInvalid'));
                return false;
            }
        }

        // Verify additional fields from public identifer plug-ins.
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        import('classes.plugins.PubIdPluginHelper');
        $pubIdPluginHelper = new PubIdPluginHelper();
        $pubIdPluginHelper->validate((int)$journal->getId(), $this, $this->article);

        // Fall back on parent validation
        return parent::validate();
    }

    /**
     * Save changes to article.
     * @param object|null $request PKPRequest
     * @return int the article ID
     */
    public function execute($request = null) {
        // [WIZDAM] Strict Dependency Injection
        $request = $request ?? Application::get()->getRequest();
        
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        /** @var CitationDAO $citationDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $article = $this->article;

        // Retrieve the previous citation list for comparison.
        $previousRawCitationList = $article->getCitations();

        // Update article
        $article->setTitle($this->getData('title'), null); // Localized

        $section = $sectionDao->getSection($article->getSectionId());
        $article->setAbstract($this->getData('abstract'), null); // Localized

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        if ($publicFileManager->uploadedFileExists(COVER_PAGE_IMAGE_NAME)) {
            $journal = $request->getJournal();
            $originalFileName = $publicFileManager->getUploadedFileName(COVER_PAGE_IMAGE_NAME);
            $type = $publicFileManager->getUploadedFileType(COVER_PAGE_IMAGE_NAME);
            $newFileName = 'cover_article_' . $this->article->getId() . '_' . $this->getFormLocale() . $publicFileManager->getImageExtension($type);
            $publicFileManager->uploadJournalFile($journal->getId(), COVER_PAGE_IMAGE_NAME, $newFileName);
            $article->setOriginalFileName($publicFileManager->truncateFileName($originalFileName, 127), $this->getFormLocale());
            $article->setFileName($newFileName, $this->getFormLocale());

            // Store the image dimensions.
            list($width, $height) = getimagesize($publicFileManager->getJournalFilesPath($journal->getId()) . '/' . $newFileName);
            $article->setWidth($width, $this->getFormLocale());
            $article->setHeight($height, $this->getFormLocale());
        }

        $article->setCoverPageAltText($this->getData('coverPageAltText'), null); // Localized

        $showCoverPage = array_map(function($arrayElement) {
            return (int) $arrayElement;
        }, (array) $this->getData('showCoverPage'));
        
        foreach (array_keys($this->getData('coverPageAltText')) as $locale) {
            if (!array_key_exists($locale, $showCoverPage)) {
                $showCoverPage[$locale] = 0;
            }
        }
        $article->setShowCoverPage($showCoverPage, null); // Localized

        $hideCoverPageToc = array_map(function($arrayElement) {
            return (int) $arrayElement;
        }, (array) $this->getData('hideCoverPageToc'));
        
        foreach (array_keys($this->getData('coverPageAltText')) as $locale) {
            if (!array_key_exists($locale, $hideCoverPageToc)) {
                $hideCoverPageToc[$locale] = 0;
            }
        }
        $article->setHideCoverPageToc($hideCoverPageToc, null); // Localized

        $hideCoverPageAbstract = array_map(function($arrayElement) {
            return (int) $arrayElement;
        }, (array) $this->getData('hideCoverPageAbstract'));
        
        foreach (array_keys($this->getData('coverPageAltText')) as $locale) {
            if (!array_key_exists($locale, $hideCoverPageAbstract)) {
                $hideCoverPageAbstract[$locale] = 0;
            }
        }
        $article->setHideCoverPageAbstract($hideCoverPageAbstract, null); // Localized

        $article->setDiscipline($this->getData('discipline'), null); // Localized
        $article->setSubjectClass($this->getData('subjectClass'), null); // Localized
        $article->setSubject($this->getData('subject'), null); // Localized
        $article->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
        $article->setCoverageChron($this->getData('coverageChron'), null); // Localized
        $article->setCoverageSample($this->getData('coverageSample'), null); // Localized
        $article->setType($this->getData('type'), null); // Localized
        $article->setLanguage($this->getData('language')); // Localized
        $article->setSponsor($this->getData('sponsor'), null); // Localized
        // [WIZDAM] Deklarasi level artikel -- pola sama persis
        // AuthorSubmitStep3Form (wizard submit baru), sekarang JUGA bisa
        // diedit editor/penulis lewat halaman metadata editorial ini
        // (review/copyediting), tidak lagi terkunci hanya di wizard
        // submit awal.
        $article->setCompetingInterest($this->getData('competingInterest'), null);
        $article->setEthicalApproval($this->getData('ethicalApproval'), null);
        $article->setGenerativeAiDeclaration($this->getData('generativeAiDeclaration'), null);
        $article->setCitations($this->getData('citations'));
        if ($this->isEditor) {
            $article->setHideAuthor($this->getData('hideAuthor') ? $this->getData('hideAuthor') : 0);
        }
        // consider the additional field names from the public identifer plugins
        import('classes.plugins.PubIdPluginHelper');
        $pubIdPluginHelper = new PubIdPluginHelper();
        $pubIdPluginHelper->execute($this, $article);

        // Update authors
        $authors = $this->getData('authors');
        // [WIZDAM BUGFIX] primaryContact sekarang ARRAY (checkbox multi-
        // pilih) -- lihat penjelasan lengkap di initData()/readInputData().
        $primaryContactIndices = array_map('intval', (array) $this->getData('primaryContact'));
        for ($i=0, $count=count($authors); $i < $count; $i++) {
            if ($authors[$i]['authorId'] > 0) {
                // Update an existing author
                // [WIZDAM BUGFIX] Lihat penjelasan lengkap di import()
                // paling atas file ini soal kenapa anotasi ini
                // diperlukan.
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
                $author->setAffiliation($authors[$i]['affiliation'], null); // Localized
                $author->setCountry($authors[$i]['country']);
                $author->setEmail($authors[$i]['email']);
                $author->setData('orcid', $authors[$i]['orcid']);
                $author->setUrl($authors[$i]['url']);
                if (array_key_exists('creditRoles', $authors[$i])) {
                    // [WIZDAM] Menggantikan setCompetingInterests() per-
                    // penulis yang lama -- lihat penjelasan lengkap di
                    // initData().
                    $author->setCreditRolesArray($authors[$i]['creditRoles']);
                }
                $author->setBiography($authors[$i]['biography'], null); // Localized
                $author->setPrimaryContact(in_array($i, $primaryContactIndices, true) ? 1 : 0);
                $author->setSequence($authors[$i]['seq']);

                HookRegistry::dispatch('Submission::Form::MetadataForm::Execute', [&$author, &$authors[$i]]);

                if ($isExistingAuthor) {
                    $authorDao->updateAuthor($author);
                } else {
                    $authorDao->insertAuthor($author);
                }
                unset($author);
            }
        }

        // Remove deleted authors
        $deletedAuthors = preg_split('/:/', $this->getData('deletedAuthors'), -1, PREG_SPLIT_NO_EMPTY);
        for ($i=0, $count=count($deletedAuthors); $i < $count; $i++) {
            $authorDao->deleteAuthorById($deletedAuthors[$i], $article->getId());
        }

        // [WIZDAM] Simpan funders (pendanaan/hibah) -- pola sama persis
        // AuthorSubmitStep2Form, sekarang JUGA bisa dikelola dari
        // halaman metadata editorial.
        /** @var ArticleFunderDAO $funderDao */
        $funderDao = DAORegistry::getDAO('ArticleFunderDAO');
        $funders = $this->getData('funders');
        if (is_array($funders)) {
            for ($i = 0, $count = count($funders); $i < $count; $i++) {
                $funderName = trim((string) ($funders[$i]['funderName'] ?? ''));
                if ($funderName === '') {
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

        if ($this->isEditor) {
            $article->setCopyrightHolder($this->getData('copyrightHolder'), null);
            $article->setCopyrightYear($this->getData('copyrightYear'));
            $article->setLicenseURL($this->getData('licenseURL'));
        }

        parent::execute();

        // Save the article
        $articleDao->updateArticle($article);

        // Update search index
        import('classes.search.ArticleSearchIndex');
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->articleMetadataChanged($article);
        $articleSearchIndex->articleChangesFinished();

        // Update references list if it changed.
        $rawCitationList = $article->getCitations();
        if ($previousRawCitationList != $rawCitationList) {
            $citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $article->getId(), $rawCitationList);
        }

        return $article->getId();
    }

    /**
     * Determine whether or not the current user is allowed to edit metadata.
     * @return boolean
     */
    public function getCanEdit() {
        return $this->canEdit;
    }

}
?>