<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep2Form.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * @class AuthorSubmitStep2Form
 * @ingroup author_form_submit
 *
 * @brief [WIZDAM] Step 2 dari wizard submit yang DIRESTRUKTURISASI --
 * Authors + CRediT (peran kontribusi per-penulis) + Funders
 * (pendanaan/hibah).
 *
 * Ini KELANJUTAN dari bagian authors+funders milik
 * AuthorSubmitStep3Form LAMA (metadata-nya sudah pindah ke
 * AuthorSubmitStep1Form baru). Artikel SELALU sudah ada di titik ini
 * (dibuat di Step 1 baru) -- TIDAK ADA lagi cabang "insert new
 * article" seperti form lama.
 *
 * PERUBAHAN: competingInterests PER-PENULIS (field lama) TIDAK LAGI
 * dikumpulkan di sini -- digantikan field competingInterest LEVEL
 * ARTIKEL yang baru (lihat AuthorSubmitStep3Form BARU / Deklarasi),
 * sesuai riset publikasi akademik (satu pernyataan mencakup seluruh
 * penulis, bukan per-penulis terpisah). Data lama sudah dimigrasi
 * lewat Upgrade::migrateAuthorCompetingInterestsToArticle().
 *
 * PENTING soal nama hook: file/class ini SEBELUMNYA bernama
 * AuthorSubmitStep3Form -- GoogleAnalyticsPlugin punya DUA hook yang
 * bergantung pada nama class lama ('authorsubmitstep3form::initdata'
 * otomatis dari nama class, dan
 * 'Author::Form::Submit::AuthorSubmitStep3Form::Execute' eksplisit)
 * untuk menyimpan field custom 'gs' (Google Scholar ID) per penulis.
 * KEDUA hook itu SUDAH diperbarui di
 * plugins/generic/googleAnalytics/GoogleAnalyticsPlugin.inc.php untuk
 * mengikuti nama class baru -- lihat file itu untuk detail.
 */

import('classes.author.form.submit.AuthorSubmitForm');
import('classes.article.Author');

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

        // [WIZDAM] Validasi funders -- keseluruhan field OPSIONAL (tidak
        // semua artikel punya pendanaan eksternal), TAPI kalau satu baris
        // funder sudah ditambahkan lewat grid, funderName WAJIB diisi.
        $this->addCheck(new FormValidatorArrayCustom(
            $this, 'funders', 'required', 'author.submit.form.funderNameRequired',
            function($funderName) { return trim((string) $funderName) !== ''; },
            [],
            false,
            ['funderName']
        ));

        // [WIZDAM] CRediT SENGAJA tidak diwajibkan (opsional, dianjurkan)
        // -- memaksa SETIAP penulis memilih minimal satu peran bisa
        // menghalangi submission untuk kasus di mana taksonomi CRediT
        // tidak sepenuhnya berlaku (mis. artikel non-riset). Tidak ada
        // FormValidator ditambahkan untuk creditRoles.
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
     */
    public function getArticle() {
        return $this->article;
    }

    /**
     * Initialize form data from current article.
     */
    public function initData() {
        $formLocales = $this->getSubmissionLocales();

        if (isset($this->article)) {
            $article = $this->article;
            $this->_data = [
                'authors' => [],
                'funders' => [],
            ];

            $authors = $article->getAuthors();
            for ($i=0, $count=count($authors); $i < $count; $i++) {
                $affiliationArray = $authors[$i]->getAffiliation(null);
                $biographyArray = $authors[$i]->getBiography(null);

                $affiliationArray = is_array($affiliationArray) ? $affiliationArray : [];
                $biographyArray = is_array($biographyArray) ? $biographyArray : [];

                foreach ($formLocales as $locale) {
                    if (!isset($affiliationArray[$locale])) $affiliationArray[$locale] = '';
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
                    'biography' => $biographyArray,
                    // [WIZDAM] CRediT -- array kode peran (bukan string
                    // dipisah koma) supaya langsung cocok dengan pola
                    // checkbox multi-pilih di template.
                    'creditRoles' => $authors[$i]->getCreditRolesArray(),
                ];
            }
            // [WIZDAM BUGFIX] primaryContact SEKARANG ARRAY berisi index
            // SEMUA penulis yang ditandai principal contact -- lihat
            // penjelasan lengkap di MetadataForm::initData(). Dibangun di
            // loop TERPISAH supaya urutannya tetap sesuai urutan penulis.
            $this->_data['primaryContact'] = [];
            foreach ($authors as $i => $author) {
                if ($author->getPrimaryContact()) {
                    $this->_data['primaryContact'][] = $i;
                }
            }

            // [WIZDAM] Isi data funders (pendanaan/hibah) dari database.
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
        ]);

        $formLocales = $this->getSubmissionLocales();

        if (is_array($this->_data['authors'])) {
            foreach ($this->_data['authors'] as $i => $author) {
                if (!isset($author['affiliation']) || !is_array($author['affiliation'])) {
                    $this->_data['authors'][$i]['affiliation'] = [];
                }
                if (!isset($author['biography']) || !is_array($author['biography'])) {
                    $this->_data['authors'][$i]['biography'] = [];
                }

                foreach ($formLocales as $formLocale) {
                    if (!isset($this->_data['authors'][$i]['affiliation'][$formLocale])) {
                        $this->_data['authors'][$i]['affiliation'][$formLocale] = '';
                    }
                    if (!isset($this->_data['authors'][$i]['biography'][$formLocale])) {
                        $this->_data['authors'][$i]['biography'][$formLocale] = '';
                    }
                }

                // [WIZDAM] Normalisasi creditRoles -- checkbox HTML yang
                // TIDAK dicentang tidak ikut terkirim sama sekali dalam
                // POST, jadi kalau key-nya hilang, berarti tidak ada
                // peran dipilih (array kosong), BUKAN error.
                if (!isset($this->_data['authors'][$i]['creditRoles']) || !is_array($this->_data['authors'][$i]['creditRoles'])) {
                    $this->_data['authors'][$i]['creditRoles'] = [];
                }
            }
        } else {
            $this->_data['authors'] = [];
        }

        // [WIZDAM] Normalisasi funders.
        if (!is_array($this->_data['funders'])) {
            $this->_data['funders'] = [];
        }

        // [WIZDAM BUGFIX] primaryContact sekarang checkbox multi-pilih
        // (name="primaryContact[]") -- checkbox yang TIDAK dicentang sama
        // sekali TIDAK ikut terkirim dalam POST, jadi kalau key-nya hilang
        // berarti tidak ada yang dicentang (array kosong), bukan error.
        if (!isset($this->_data['primaryContact']) || !is_array($this->_data['primaryContact'])) {
            $this->_data['primaryContact'] = [];
        }
        $this->_data['primaryContact'] = array_values(array_unique(array_map('intval', $this->_data['primaryContact'])));
    }

    /**
     * Get the names of fields for which data should be localized
     * @return array
     */
    public function getLocaleFieldNames() {
        return parent::getLocaleFieldNames();
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

        // [WIZDAM] Daftar 14 peran CRediT baku, satu sumber kebenaran
        // (Author::getAllCreditRoles()) untuk membangun checkbox di
        // template -- lihat classes/article/Author.inc.php.
        $templateMgr->assign('allCreditRoles', Author::getAllCreditRoles());

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

        if ($article->getSubmissionProgress() <= $this->step) {
            $article->stampStatusModified();
            $article->setSubmissionProgress($this->step + 1);
            $articleDao->updateArticle($article);
        }

        // Update authors
        $authors = $this->getData('authors');
        // [WIZDAM BUGFIX] primaryContact sekarang ARRAY (checkbox multi-
        // pilih) -- lihat penjelasan lengkap di initData()/readInputData().
        $primaryContactIndices = array_map('intval', (array) $this->getData('primaryContact'));
        for ($i=0, $count=count($authors); $i < $count; $i++) {
            if ($authors[$i]['authorId'] > 0) {
                // Update an existing author
                // [WIZDAM BUGFIX] PKPAuthorDAO::getAuthor() (induk
                // AuthorDAO, TIDAK di-override method-nya) mendeklarasikan
                // @return PKPAuthor|null -- generik, TIDAK menyebut Author
                // (kelas turunan level aplikasi). Anotasi @var di sini
                // menegaskan tipe yang SUDAH TERBUKTI benar lewat
                // AuthorDAO::newDataObject() ("return new Author();"),
                // bukan tebakan.
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
                $author->setBiography($authors[$i]['biography'], null);
                $author->setPrimaryContact(in_array($i, $primaryContactIndices, true) ? 1 : 0);
                $author->setSequence($authors[$i]['seq']);
                // [WIZDAM] CRediT -- array kode peran dari checkbox.
                $author->setCreditRolesArray($authors[$i]['creditRoles'] ?? []);

                // [LUMERA] HookRegistry dispatch using array construction for references
                HookRegistry::dispatch('Author::Form::Submit::AuthorSubmitStep2Form::Execute', [&$author, &$authors[$i]]);

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

        // [WIZDAM] Simpan funders (pendanaan/hibah).
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

        parent::execute();

        return $this->articleId;
    }

}
?>