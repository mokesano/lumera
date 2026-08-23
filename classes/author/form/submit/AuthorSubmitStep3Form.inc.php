<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitStep3Form.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class AuthorSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief [WIZDAM] Step 3 dari wizard submit yang DIRESTRUKTURISASI --
 * Deklarasi: Submission Checklist + Copyright Notice Agreement (dari
 * Step 1 lama, dipindahkan ke sini sesuai keputusan eksplisit), PLUS
 * tiga deklarasi baru level artikel -- Competing Interest, Ethical
 * Approval, Declaration of Generative AI.
 *
 * KETIGANYA level artikel (SATU pernyataan mencakup seluruh penulis),
 * BUKAN per-penulis -- dikonfirmasi lewat riset publikasi akademik
 * nyata (Elsevier, Springer, Taylor & Francis, Cell Press, arXiv):
 * "The authors declare...", bukan entri terpisah per penulis. Field
 * disimpan lewat Article::setCompetingInterest()/setEthicalApproval()/
 * setGenerativeAiDeclaration() (lihat classes/article/Article.inc.php)
 * -- mekanisme generic settings yang sama seperti sponsor, TIDAK ada
 * kolom tabel baru.
 *
 * PENTING: submissionChecklist dan copyrightNoticeAgree TIDAK
 * dipersist ke database (murni gerbang validasi "centang untuk
 * lanjut") -- pola ini SAMA seperti perilaku Step 1 lama (dikonfirmasi
 * lewat pembacaan kode aslinya: readUserVars() membaca kedua field
 * ini, tapi execute() TIDAK PERNAH menyimpannya ke Article manapun).
 * DITAMBAHKAN validasi FormValidator eksplisit untuk keduanya di sini
 * -- form LAMA membaca kedua field ini tapi TIDAK PERNAH memvalidasi
 * wajib dicentang, celah yang diperbaiki sekalian dalam restrukturisasi
 * ini karena berpotensi jadi isu kepatuhan (checklist/copyright
 * seharusnya benar-benar wajib, bukan cuma tampilan).
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

        // [WIZDAM BUGFIX -- KEPATUHAN] submissionChecklist dan
        // copyrightNoticeAgree SEBELUMNYA dibaca tapi TIDAK PERNAH
        // divalidasi wajib dicentang di form lama manapun -- ditambahkan
        // di sini.
        $this->addCheck(new FormValidator($this, 'submissionChecklist', 'required', 'author.submit.form.checklistRequired'));
        $this->addCheck(new FormValidator($this, 'copyrightNoticeAgree', 'required', 'author.submit.form.copyrightNoticeAgreeRequired'));

        // [WIZDAM] Ketiga deklarasi baru -- WAJIB diisi (bukan sekadar
        // dicentang), sesuai permintaan eksplisit "elemen input WAJIB".
        // Pola dunia nyata (lihat riset) selalu punya SATU teks
        // pernyataan, meski isinya cuma "Not applicable"/"The authors
        // declare no competing interests" -- WAJIB di sini berarti kolom
        // tidak boleh KOSONG, bukan memaksa isi tertentu.
        $this->addCheck(new FormValidatorLocale($this, 'competingInterest', 'required', 'author.submit.form.competingInterestRequired', $this->getRequiredLocale()));
        $this->addCheck(new FormValidatorLocale($this, 'ethicalApproval', 'required', 'author.submit.form.ethicalApprovalRequired', $this->getRequiredLocale()));
        $this->addCheck(new FormValidatorLocale($this, 'generativeAiDeclaration', 'required', 'author.submit.form.generativeAiDeclarationRequired', $this->getRequiredLocale()));
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
                'competingInterest' => $article->getCompetingInterest(null),
                'ethicalApproval' => $article->getEthicalApproval(null),
                'generativeAiDeclaration' => $article->getGenerativeAiDeclaration(null),
                // submissionChecklist/copyrightNoticeAgree TIDAK dimuat
                // dari database -- tidak pernah dipersist (lihat
                // penjelasan di atas), user WAJIB mencentang ulang setiap
                // kali melewati step ini (perilaku sama seperti form lama).
            ];

            foreach (['competingInterest', 'ethicalApproval', 'generativeAiDeclaration'] as $field) {
                if (!is_array($this->_data[$field])) $this->_data[$field] = [];
                foreach ($formLocales as $locale) {
                    if (!isset($this->_data[$field][$locale])) $this->_data[$field][$locale] = '';
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
            'submissionChecklist',
            'copyrightNoticeAgree',
            'competingInterest',
            'ethicalApproval',
            'generativeAiDeclaration',
        ]);

        $formLocales = $this->getSubmissionLocales();
        foreach (['competingInterest', 'ethicalApproval', 'generativeAiDeclaration'] as $field) {
            if (!is_array($this->_data[$field])) $this->_data[$field] = [];
            foreach ($formLocales as $formLocale) {
                if (!isset($this->_data[$field][$formLocale])) $this->_data[$field][$formLocale] = '';
            }
        }
    }

    /**
     * Get the names of fields for which data should be localized
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), [
            'competingInterest', 'ethicalApproval', 'generativeAiDeclaration'
        ]);
    }

    /**
     * Save changes to article.
     * @param object|null $object
     * @return int the article ID
     */
    public function execute($object = null) {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $this->article;

        $article->setCompetingInterest($this->getData('competingInterest'), null);
        $article->setEthicalApproval($this->getData('ethicalApproval'), null);
        $article->setGenerativeAiDeclaration($this->getData('generativeAiDeclaration'), null);

        if ($article->getSubmissionProgress() <= $this->step) {
            $article->stampStatusModified();
            $article->setSubmissionProgress($this->step + 1);
        }
        $articleDao->updateArticle($article);

        parent::execute();

        return $this->articleId;
    }

}
?>