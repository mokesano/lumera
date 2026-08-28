<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep1Form.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class AuthorSubmitStep1Form
 * @ingroup author_form_submit
 *
 * @brief [WIZDAM] Step 1 dari wizard submit yang DIRESTRUKTURISASI --
 * Metadata (judul, abstrak, kata kunci, dst) SEKALIGUS pembuatan
 * record artikel itu sendiri (section, locale, penulis awal dari
 * profil user login).
 *
 * Ini PENGGABUNGAN dua form lama: logika pembuatan artikel dari
 * AuthorSubmitStep1Form LAMA (sectionId, locale, insertArticle(),
 * penulis awal) DIGABUNG dengan field metadata dari
 * AuthorSubmitStep3Form LAMA (title, abstract, discipline,
 * subjectClass, subject, coverage*, type, language, sponsor,
 * citations) -- authors/funders/CRediT SENGAJA TIDAK di sini,
 * dipindahkan ke AuthorSubmitStep2Form baru.
 *
 * commentsToEditor SENGAJA tidak lagi dikumpulkan di step ini
 * (sebelumnya ada di Step 1 lama DAN Step 5 lama sekaligus) --
 * disederhanakan jadi HANYA di Step 5 (Overview+Submit), yang jauh
 * lebih relevan konteksnya (catatan untuk editor biasanya ditulis
 * setelah melihat seluruh input, bukan di awal sebelum apa pun diisi).
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
        parent::__construct($article, 1, $journal, $request);

        // --- Validasi dari Step 1 LAMA (pembuatan artikel) ---
        $this->addCheck(new FormValidator($this, 'sectionId', 'required', 'author.submit.form.sectionRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'sectionId', 'required', 'author.submit.form.sectionRequired', [DAORegistry::getDAO('SectionDAO'), 'sectionExists'], [$journal->getId()]));

        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (!is_array($supportedSubmissionLocales) || count($supportedSubmissionLocales) < 1) $supportedSubmissionLocales = [$journal->getPrimaryLocale()];
        $this->addCheck(new FormValidatorInSet($this, 'locale', 'required', 'author.submit.form.localeRequired', $supportedSubmissionLocales));

        // --- Validasi dari Step 3 LAMA (metadata) ---
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired', $this->getRequiredLocale()));

        // Validasi jumlah kata abstrak & wajib/tidaknya abstrak baru bisa
        // diketahui SETELAH section diketahui -- section BARU ada kalau
        // $this->article sudah ada (artikel sudah pernah dibuat, user
        // kembali ke step ini untuk edit). Untuk pembuatan BARU (section
        // belum dipilih sama sekali saat form pertama kali dirender),
        // validasi abstrak wajib/word-count ditambahkan di readInputData()
        // setelah section diketahui dari input, mengikuti pola persis
        // Step 3 lama.
        if (isset($article)) {
            /** @var SectionDAO $sectionDao */
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            $section = $sectionDao->getSection($article->getSectionId());
            if ($section) {
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
        }
    }

    /**
     * [SHIM] Backward Compatibility
     * @param Article|null $article
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function AuthorSubmitStep1Form($article, $journal, $request) {
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
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        if (!$this->request) $this->request = $request;

        $journal = $this->request->getJournal();
        $user = $this->request->getUser();

        $templateMgr = TemplateManager::getManager($request);

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $isEditor = $roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_EDITOR) || $roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_SECTION_EDITOR);
        $templateMgr->assign('sectionOptions', ['0' => __('author.submit.selectSection')] + $sectionDao->getSectionTitles($journal->getId(), !$isEditor));

        // [WIZDAM] Tipe Artikel -- opsi dipersempit ke Section yang
        // SUDAH dipilih (kalau sudah ada, mis. sedang mengedit kembali
        // sebelum lanjut step berikutnya); kalau BELUM ada section
        // terpilih sama sekali (kunjungan pertama, sebelum submit
        // pertama form ini), tampilkan yang aktif di level jurnal saja
        // sebagai pendekatan terbaik -- lihat ArticleType::buildTypeOptions().
        // TIDAK menyertakan tipe editorial-only (erratum dkk) -- ini
        // form PENULIS, bukan editorial.
        import('classes.article.ArticleType');
        $currentSectionId = (int) ($this->getData('sectionId') ?: 0);
        $templateMgr->assign(
            'articleTypeOptions',
            ['' => __('article.type.selectType')] + ArticleType::buildTypeOptions($journal->getId(), $currentSectionId ?: null, false)
        );
        $templateMgr->assign(
            'articleTypeChoice',
            ArticleType::toChoiceValue($this->getData('articleTypeCode'), $this->getData('articleTypeCustomId'))
        );

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
        $formLocales = $this->getSubmissionLocales();

        if (isset($this->article)) {
            $article = $this->article;
            /** @var SectionDAO $sectionDao */
            $sectionDao = DAORegistry::getDAO('SectionDAO');

            $this->_data = [
                'sectionId' => $article->getSectionId(),
                'locale' => $article->getLocale(),
                'section' => $sectionDao->getSection($article->getSectionId()),
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
                'citations' => $article->getCitations(),
                // [WIZDAM] Tipe Artikel -- lihat ArticleType::buildTypeOptions()/
                // toChoiceValue() untuk penjelasan lengkap kenapa disimpan
                // sebagai DUA field terpisah tapi diedit lewat SATU <select>.
                'articleTypeCode' => $article->getArticleTypeCode(),
                'articleTypeCustomId' => $article->getArticleTypeCustomId(),
            ];

            if (!is_array($this->_data['title'])) $this->_data['title'] = [];
            if (!is_array($this->_data['abstract'])) $this->_data['abstract'] = [];
            foreach ($formLocales as $locale) {
                if (!isset($this->_data['title'][$locale])) $this->_data['title'][$locale] = '';
                if (!isset($this->_data['abstract'][$locale])) $this->_data['abstract'][$locale] = '';
            }
        } else {
            // [WIZDAM] Singleton Fallback -- artikel BELUM dibuat (kunjungan
            // pertama ke wizard submit). Pola persis Step 1 lama untuk
            // menentukan locale default.
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();
            $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
            $fallbackLocales = array_keys($supportedSubmissionLocales);
            $tryLocales = [
                $this->getFormLocale(),
                AppLocale::getLocale(),
                $journal->getPrimaryLocale(),
                $supportedSubmissionLocales[array_shift($fallbackLocales)]
            ];
            $this->_data = [];
            foreach ($tryLocales as $locale) {
                if (in_array($locale, $supportedSubmissionLocales)) {
                    $this->_data['locale'] = $locale;
                    break;
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
            'locale', 'sectionId',
            'title', 'abstract', 'discipline', 'subjectClass', 'subject',
            'coverageGeo', 'coverageChron', 'coverageSample', 'type',
            'language', 'sponsor', 'citations',
            'articleTypeChoice',
        ]);

        // [WIZDAM] Pecah pilihan gabungan "std:<code>"/"custom:<id>" dari
        // <select> tunggal articleTypeChoice menjadi dua field aktual di
        // Article -- lihat ArticleType::parseTypeChoice().
        import('classes.article.ArticleType');
        [$articleTypeCode, $articleTypeCustomId] = ArticleType::parseTypeChoice($this->getData('articleTypeChoice'));
        $this->setData('articleTypeCode', $articleTypeCode);
        $this->setData('articleTypeCustomId', $articleTypeCustomId);

        $formLocales = $this->getSubmissionLocales();

        if (!is_array($this->_data['title'])) $this->_data['title'] = [];
        if (!is_array($this->_data['abstract'])) $this->_data['abstract'] = [];
        foreach ($formLocales as $formLocale) {
            if (!isset($this->_data['title'][$formLocale])) $this->_data['title'][$formLocale] = '';
            if (!isset($this->_data['abstract'][$formLocale])) $this->_data['abstract'][$formLocale] = '';
        }

        // Load the section (dari input, karena bisa jadi INI kunjungan
        // pertama -- $this->article belum tentu ada) -- pola persis
        // Step 3 lama.
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionId = $this->getData('sectionId');
        $this->_data['section'] = $sectionId ? $sectionDao->getSection($sectionId) : null;

        if ($this->_data['section'] && $this->_data['section']->getAbstractsNotRequired() == 0) {
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
     * Save changes to article -- kalau artikel BELUM ada, buat baru
     * (persis Step 1 lama, TERMASUK pembuatan penulis awal dari profil
     * user login). Kalau SUDAH ada, update field metadata (persis
     * bagian metadata Step 3 lama).
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        if (isset($this->article)) {
            // Update existing article
            $article = $this->article;
            $previousRawCitationList = $article->getCitations();

            $article->setSectionId($this->getData('sectionId'));
            $article->setLocale($this->getData('locale'));
            $article->setTitle($this->getData('title'), null);
            $article->setAbstract($this->getData('abstract'), null);
            $article->setDiscipline($this->getData('discipline'), null);
            $article->setSubjectClass($this->getData('subjectClass'), null);
            $article->setSubject($this->getData('subject'), null);
            $article->setCoverageGeo($this->getData('coverageGeo'), null);
            $article->setCoverageChron($this->getData('coverageChron'), null);
            $article->setCoverageSample($this->getData('coverageSample'), null);
            $article->setType($this->getData('type'), null);
            $article->setLanguage($this->getData('language'));
            $article->setSponsor($this->getData('sponsor'), null);
            $article->setCitations($this->getData('citations'));
            $article->setArticleTypeCode($this->getData('articleTypeCode'));
            $article->setArticleTypeCustomId($this->getData('articleTypeCustomId'));

            if ($article->getSubmissionProgress() <= $this->step) {
                $article->stampStatusModified();
                $article->setSubmissionProgress($this->step + 1);
            }
            $articleDao->updateArticle($article);

            // Perbarui daftar referensi kalau berubah -- pola persis
            // Step 3 lama.
            /** @var CitationDAO $citationDao */
            $citationDao = DAORegistry::getDAO('CitationDAO');
            $rawCitationList = $article->getCitations();
            if ($previousRawCitationList != $rawCitationList) {
                $request = $this->request ? $this->request : Application::get()->getRequest();
                $citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $article->getId(), $rawCitationList);
            }

        } else {
            // Insert new article -- pola PERSIS Step 1 lama.
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();
            $user = $request->getUser();

            $this->article = new Article();
            $this->article->setLocale($this->getData('locale'));
            $this->article->setUserId($user->getId());
            $this->article->setJournalId($journal->getId());
            $this->article->setSectionId($this->getData('sectionId'));
            $this->article->setTitle($this->getData('title'), null);
            $this->article->setAbstract($this->getData('abstract'), null);
            $this->article->setDiscipline($this->getData('discipline'), null);
            $this->article->setSubjectClass($this->getData('subjectClass'), null);
            $this->article->setSubject($this->getData('subject'), null);
            $this->article->setCoverageGeo($this->getData('coverageGeo'), null);
            $this->article->setCoverageChron($this->getData('coverageChron'), null);
            $this->article->setCoverageSample($this->getData('coverageSample'), null);
            $this->article->setType($this->getData('type'), null);
            $this->article->setSponsor($this->getData('sponsor'), null);
            $this->article->setCitations($this->getData('citations'));
            $this->article->setArticleTypeCode($this->getData('articleTypeCode'));
            $this->article->setArticleTypeCustomId($this->getData('articleTypeCustomId'));
            $this->article->stampStatusModified();
            $this->article->setSubmissionProgress($this->step + 1);
            $this->article->setLanguage(PKPString::substr($this->article->getLocale(), 0, 2));
            $articleDao->insertArticle($this->article);
            $this->articleId = $this->article->getId();

            // Set user to initial author -- pola PERSIS Step 1 lama.
            /** @var AuthorDAO $authorDao */
            $authorDao = DAORegistry::getDAO('AuthorDAO');
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

            // Referensi (kalau diinput saat pembuatan baru -- jarang
            // terjadi tapi tetap ditangani, pola persis cabang existing
            // article di atas).
            $rawCitationList = $this->article->getCitations();
            if (!empty($rawCitationList)) {
                /** @var CitationDAO $citationDao */
                $citationDao = DAORegistry::getDAO('CitationDAO');
                $citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $this->articleId, $rawCitationList);
            }
        }

        parent::execute();

        return $this->articleId;
    }

}
?>