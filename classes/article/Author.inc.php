<?php
declare(strict_types=1);

/**
 * @file classes/article/Author.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Author
 * @ingroup article
 * @see AuthorDAO
 *
 * @brief Article author metadata class.
 */

import('lib.pkp.classes.submission.PKPAuthor');

// [WIZDAM] 14 peran baku CRediT (Contributor Role Taxonomy), sesuai
// definisi resmi https://credit.niso.org/ -- kode (snake_case) dipakai
// sebagai NILAI yang disimpan di database (lewat getCreditRoles()/
// setCreditRoles()), label untuk ditampilkan diambil lewat locale key
// terpisah (author.credit.<kode>) supaya bisa diterjemahkan tanpa
// mengubah kode yang tersimpan.
define('CREDIT_ROLE_CONCEPTUALIZATION', 'conceptualization');
define('CREDIT_ROLE_DATA_CURATION', 'data_curation');
define('CREDIT_ROLE_FORMAL_ANALYSIS', 'formal_analysis');
define('CREDIT_ROLE_FUNDING_ACQUISITION', 'funding_acquisition');
define('CREDIT_ROLE_INVESTIGATION', 'investigation');
define('CREDIT_ROLE_METHODOLOGY', 'methodology');
define('CREDIT_ROLE_PROJECT_ADMINISTRATION', 'project_administration');
define('CREDIT_ROLE_RESOURCES', 'resources');
define('CREDIT_ROLE_SOFTWARE', 'software');
define('CREDIT_ROLE_SUPERVISION', 'supervision');
define('CREDIT_ROLE_VALIDATION', 'validation');
define('CREDIT_ROLE_VISUALIZATION', 'visualization');
define('CREDIT_ROLE_WRITING_ORIGINAL_DRAFT', 'writing_original_draft');
define('CREDIT_ROLE_WRITING_REVIEW_EDITING', 'writing_review_editing');

class Author extends PKPAuthor {

    /**
     * [WIZDAM] Daftar SELURUH kode peran CRediT yang valid, dalam urutan
     * baku resmi -- dipakai untuk validasi (FormValidatorArrayCustom di
     * AuthorSubmitStep3Form) dan membangun daftar checkbox di template,
     * satu sumber kebenaran supaya tidak perlu mengetik ulang 14 kode
     * di banyak tempat.
     * @return string[]
     */
    public static function getAllCreditRoles() {
        return [
            CREDIT_ROLE_CONCEPTUALIZATION,
            CREDIT_ROLE_DATA_CURATION,
            CREDIT_ROLE_FORMAL_ANALYSIS,
            CREDIT_ROLE_FUNDING_ACQUISITION,
            CREDIT_ROLE_INVESTIGATION,
            CREDIT_ROLE_METHODOLOGY,
            CREDIT_ROLE_PROJECT_ADMINISTRATION,
            CREDIT_ROLE_RESOURCES,
            CREDIT_ROLE_SOFTWARE,
            CREDIT_ROLE_SUPERVISION,
            CREDIT_ROLE_VALIDATION,
            CREDIT_ROLE_VISUALIZATION,
            CREDIT_ROLE_WRITING_ORIGINAL_DRAFT,
            CREDIT_ROLE_WRITING_REVIEW_EDITING,
        ];
    }
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Author() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Get/set methods
    //

    /**
     * [DEPRECATED] Get ID of article.
     * Use in favor of getSubmissionId().
     * @return int
     */
    public function getArticleId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getSubmissionId();
    }

    /**
     * [DEPRECATED] Set ID of article.
     * Use in favor of setSubmissionId().
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setSubmissionId($articleId);
    }

    /**
     * Get the localized competing interests statement for this author
     * @return string
     */
    public function getLocalizedCompetingInterests() {
        return $this->getLocalizedData('competingInterests');
    }

    /**
     * [DEPRECATED] Get author competing interests.
     * Use in favor of getLocalizedCompetingInterests().
     * @deprecated
     * @return string
     */
    public function getAuthorCompetingInterests() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedCompetingInterests();
    }

    /**
     * Get author competing interests.
     * @param string $locale
     * @return string
     */
    public function getCompetingInterests($locale) {
        return $this->getData('competingInterests', $locale);
    }

    /**
     * Set author competing interests.
     * @param string $competingInterests
     * @param string $locale
     */
    public function setCompetingInterests($competingInterests, $locale) {
        return $this->setData('competingInterests', $competingInterests, $locale);
    }

    // [WIZDAM] CRediT (Contributor Role Taxonomy) -- 14 peran kontribusi
    // baku, per-penulis (BEDA dari Competing Interest/Ethical Approval/
    // Generative AI Declaration yang levelnya artikel -- dikonfirmasi
    // lewat riset publikasi akademik: CRediT SECARA DESAIN memang
    // per-penulis, setiap penulis dapat satu atau lebih peran dari
    // daftar baku ini, BUKAN teks bebas). Disimpan sebagai daftar kode
    // dipisah koma (mis. "conceptualization,methodology"), TANPA locale
    // -- kode CRediT bersifat universal, tidak perlu terjemahan per
    // bahasa. Pola sama seperti getUrl()/setUrl() di PKPAuthor -- lewat
    // mekanisme generic settings (article_author_settings), TIDAK perlu
    // kolom tabel baru.

    /**
     * Get CRediT roles (daftar kode dipisah koma).
     * @return string
     */
    public function getCreditRoles() {
        return (string) $this->getData('creditRoles');
    }

    /**
     * Set CRediT roles (daftar kode dipisah koma).
     * @param string $creditRoles
     */
    public function setCreditRoles($creditRoles) {
        $this->setData('creditRoles', (string) $creditRoles);
    }

    /**
     * Get CRediT roles sebagai array kode (bukan string dipisah koma) --
     * kemudahan pemakaian di template/validasi.
     * @return string[]
     */
    public function getCreditRolesArray() {
        $roles = trim($this->getCreditRoles());
        if ($roles === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $roles)), function ($r) {
            return $r !== '';
        }));
    }

    /**
     * Set CRediT roles dari array kode (kebalikan getCreditRolesArray()).
     * @param string[] $rolesArray
     */
    public function setCreditRolesArray($rolesArray) {
        $rolesArray = is_array($rolesArray) ? $rolesArray : [];
        $this->setCreditRoles(implode(',', array_filter(array_map('trim', $rolesArray), function ($r) {
            return $r !== '';
        })));
    }
    
}
?>