<?php
declare(strict_types=1);

/**
 * @defgroup article
 */

/**
 * @file classes/article/Article.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Article
 * @ingroup article
 * @see ArticleDAO
 *
 * @brief Article class.
 */

// Submission status constants
define('STATUS_ARCHIVED', 0);
define('STATUS_QUEUED', 1);
define('STATUS_PUBLISHED', 3);
define('STATUS_DECLINED', 4);

// AuthorSubmission::getSubmissionStatus will return one of these in place of QUEUED:
define ('STATUS_QUEUED_UNASSIGNED', 5);
define ('STATUS_QUEUED_REVIEW', 6);
define ('STATUS_QUEUED_EDITING', 7);
define ('STATUS_INCOMPLETE', 8);

// Author display in ToC
define ('AUTHOR_TOC_DEFAULT', 0);
define ('AUTHOR_TOC_HIDE', 1);
define ('AUTHOR_TOC_SHOW', 2);

// Article RT comments
define ('COMMENTS_SECTION_DEFAULT', 0);
define ('COMMENTS_DISABLE', 1);
define ('COMMENTS_ENABLE', 2);

// License settings (internal use only)
define ('PERMISSIONS_FIELD_LICENSE_URL', 1);
define ('PERMISSIONS_FIELD_COPYRIGHT_HOLDER', 2);
define ('PERMISSIONS_FIELD_COPYRIGHT_YEAR', 3);

// Access status
// [LUMERA] Article Access Constants
define ('ARTICLE_ACCESS_ISSUE_DEFAULT', 0);
define ('ARTICLE_ACCESS_OPEN', 1);
define ('ARTICLE_ACCESS_SUBSCRIPTION', 2);

import('lib.pkp.classes.submission.Submission');

class Article extends Submission {
    
    /**
     * Constructor.
     */
    public function __construct() {
        // Switch on meta-data adapter support.
        $this->setHasLoadableAdapters(true);

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Article() {
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
     * Get the type of the association. Default to ASSOC_TYPE_ARTICLE.
     * @see Submission::getAssocType()
     */
    public function getAssocType() {
        return ASSOC_TYPE_ARTICLE;
    }


    //
    // LUMERA EXTENSION START (Article Type & Scope) ---
    //
    /**
     * Get the article genre/type (e.g. Research, Review)
     * @return string
     */
    public function getArticleType() {
        return $this->getData('articleType');
    }

    /**
     * Set the article genre/type (e.g. Research, Review)
     * @param string $type string
     */
    public function setArticleType($type) {
        return $this->setData('articleType', $type);
    }

    /**
     * Get the publication scope (e.g. Full Length, Abstract Only)
     * @return string
     */
    public function getPubScope() {
        return $this->getData('pubScope');
    }

    /**
     * Set the publication scope (e.g. Full Length, Abstract Only)
     * @param string $scope string
     */
    public function setPubScope($scope) {
        return $this->setData('pubScope', $scope);
    }


    //
    // [WIZDAM] - Milestones Genesis Editorial
    //
    /**
     * Get the revision date of the article, if available.
     * @return string|null
     */
    function getRevisionDate() {
        return $this->getData('revisionDate');
    }
    
    /**
     * Get the accepted date of the article, if available.
     * @return string|null
     */
    function getAcceptedDate() {
        return $this->getData('acceptedDate');
    }
    
    /**
     * Get the published date of the article, if available.
     * @return string|null
     */
    function getDatePublished() {
        if (method_exists($this, 'getPublishedArticleDatePublished')) {
            return $this->getPublishedArticleDatePublished();
        }
        return $this->getData('datePublished');
    }
    
    /**
     * Get the date when the article status was last modified, if available.
     * @return string|null
     */
    function getDateStatusModified() {
        return $this->getData('dateStatusModified');
    }

    /**
     * Get "localized" article title (if applicable). 
     * DEPRECATED in favour of getLocalizedTitle
	 * @return string
	 */
    public function getArticleTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }

    /**
     * Get "localized" article abstract (if applicable). 
     * DEPRECATED in favour of getLocalizedAbstract
	 * @return string
	 */
    public function getArticleAbstract() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedAbstract();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of article. 
     * DEPRECATED in favor of getId()
	 * @return int
	 */
    public function getArticleId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set ID of article. 
     * DEPRECATED in favor of setId($id)
	 * @param int $articleId int
	 */
    public function setArticleId($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($articleId);
    }

    /**
     * Return the "best" article ID
	 * @param $journal Object the journal this article is in
	 * @return string
	 */
    public function getBestArticleId($journal = null) {
        // Retrieve the journal, if necessary.
        if (!isset($journal)) {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($this->getJournalId());
        }

        if ($journal->getSetting('enablePublicArticleId')) {
            $publicArticleId = $this->getPubId('publisher-id');
            if (!empty($publicArticleId)) return $publicArticleId;
        }
        return $this->getId();
    }

    /**
     * Get the localized copyright holder for this article.
     * @param $preferredLocale string
     * @return string
     */
    public function getLocalizedCopyrightHolder($preferredLocale = null) {
        return $this->getLocalizedData('copyrightHolder', $preferredLocale);
    }

    /**
     * Get the license URL for this article.
     * @return string
     */
    public function getDefaultLicenseUrl() {
        return $this->_getDefaultLicenseFieldValue(null, PERMISSIONS_FIELD_LICENSE_URL);
    }

    /**
     * Get the copyright holder for this article.
	 * @param string $locale string Locale
	 * @return string
	 */
    public function getDefaultCopyrightHolder($locale) {
        return $this->_getDefaultLicenseFieldValue($locale, PERMISSIONS_FIELD_COPYRIGHT_HOLDER);
    }

    /**
     * Get the copyright year for this article.
     * @return string
     */
    public function getDefaultCopyrightYear() {
        return $this->_getDefaultLicenseFieldValue(null, PERMISSIONS_FIELD_COPYRIGHT_YEAR);
    }

    /**
     * Get the best guess license field for this article.
	 * @param string $locale string Locale
	 * @param int $field int PERMISSIONS_FIELD_... Which to return
	 */
    public function _getDefaultLicenseFieldValue($locale, $field) {
        // If already set, use the stored permissions info
        switch ($field) {
            case PERMISSIONS_FIELD_LICENSE_URL:
                $fieldValue = $this->getLicenseURL();
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_HOLDER:
                $fieldValue = $this->getCopyrightHolder($locale);
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_YEAR:
                $fieldValue = $this->getCopyrightYear();
                break;
            default: assert(false);
        }
        if (!empty($fieldValue)) {
            if ($locale === null || !is_array($fieldValue)) return $fieldValue;
            if (isset($fieldValue[$locale])) return $fieldValue[$locale];
        }

        // Otherwise, get the permissions info from journal settings.
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($this->getJournalId());
        
        // Initialize vars to avoid undefined variable notice
        $licenseUrl = null;
        $copyrightHolder = null;
        $copyrightYear = null;

        switch ($field) {
            case PERMISSIONS_FIELD_LICENSE_URL:
                $licenseUrl = $journal->getSetting('licenseURL');
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_HOLDER:
                switch($journal->getSetting('copyrightHolderType')) {
                    case 'author':
                        $copyrightHolder = array($journal->getPrimaryLocale() => $this->getAuthorString());
                        break;
                    case 'other':
                        $copyrightHolder = $journal->getSetting('copyrightHolderOther');
                        break;
                    case 'journal':
                    default:
                        $copyrightHolder = $journal->getTitle(null);
                        break;
                }
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_YEAR:
                // Default copyright year to current year
                $copyrightYear = date('Y');
                // Override based on journal settings
                /** @var PublishedArticleDAO $publishedArticleDao */
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($this->getId());
                if ($publishedArticle) {
                    switch($journal->getSetting('copyrightYearBasis')) {
                        case 'article':
                            // override to the article's year if published as you go
                            $copyrightYear = date('Y', strtotime($publishedArticle->getDatePublished()));
                            break;
                        case 'issue':
                            if ($publishedArticle->getIssueId()) {
                                // override to the issue's year if published as issue-based
                                /** @var IssueDAO $issueDao */
                                $issueDao = DAORegistry::getDAO('IssueDAO');
                                $issue = $issueDao->getIssueByArticleId($this->getId());
                                if ($issue && $issue->getDatePublished()) {
                                    $copyrightYear = date('Y', strtotime($issue->getDatePublished()));
                                }
                            }
                            break;
                        default: assert(false);
                    }
                }
                break;
            default: assert(false);
        }

        switch ($field) {
            case PERMISSIONS_FIELD_LICENSE_URL:
                $fieldValue = $licenseUrl;
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_HOLDER:
                $fieldValue = $copyrightHolder;
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_YEAR:
                $fieldValue = $copyrightYear;
                break;
            default: assert(false);
        }

        // Return the fetched license field
        if ($locale === null || !is_array($fieldValue)) return $fieldValue;
        if (isset($fieldValue[$locale])) return $fieldValue[$locale];
        return null;
    }

    /**
     * Get a public ID for this article.
	 * @param string $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @var $preview boolean If true, generate a non-persisted preview only.
	 */
    public function getPubId($pubIdType, $preview = false) {
        if ($pubIdType === 'publisher-id') {
            $pubId = $this->getStoredPubId($pubIdType);
            return ($pubId ? $pubId : null);
        }

        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $this->getJournalId());

        if (is_array($pubIdPlugins)) {
            foreach ($pubIdPlugins as $pubIdPlugin) {
                if ($pubIdPlugin->getPubIdType() == $pubIdType) {
                    $storedId = $this->getStoredPubId($pubIdType);
                    if (!empty($storedId)) return $storedId;

                    return $pubIdPlugin->getPubId($this, $preview);
                }
            }
        }
        return null;
    }

    /**
     * Get ID of journal.
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journalId');
    }

    /**
     * Set ID of journal.
     * @param int $journalId int
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', $journalId);
    }

    /**
     * Get ID of article's section.
     * @return int
     */
    public function getSectionId() {
        return $this->getData('sectionId');
    }

    /**
     * Set ID of article's section.
     * @param int $sectionId int
     */
    public function setSectionId($sectionId) {
        return $this->setData('sectionId', $sectionId);
    }

    /**
     * Get stored public ID of the submission.
	 * @param string $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @return int
	 */
    public function getStoredPubId($pubIdType) {
        return $this->getData('pub-id::'.$pubIdType);
    }

    /**
     * Set the stored public ID of the submission.
	 * @param string $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param int $pubId string
	 */
    public function setStoredPubId($pubIdType, $pubId) {
        return $this->setData('pub-id::'.$pubIdType, $pubId);
    }

    /**
     * Get stored copyright holder for the submission.
	 * @param string $locale string locale
	 * @return string
	 */
    public function getCopyrightHolder($locale) {
        return $this->getData('copyrightHolder', $locale);
    }

    /**
     * Set the stored copyright holder for the submission.
	 * @param string $copyrightHolder string Copyright holder
	 * @param string $locale string locale
	 */
    public function setCopyrightHolder($copyrightHolder, $locale) {
        return $this->setData('copyrightHolder', $copyrightHolder, $locale);
    }

    /**
     * Get stored copyright year for the submission.
	 * @return string
	 */
    public function getCopyrightYear() {
        return $this->getData('copyrightYear');
    }

    /**
     * Set the stored copyright year for the submission.
	 * @param string $copyrightYear string Copyright holder
	 */
    public function setCopyrightYear($copyrightYear) {
        return $this->setData('copyrightYear', $copyrightYear);
    }

    /**
     * Get stored license URL for the submission content.
	 * @return string
	 */
    public function getLicenseURL() {
        return $this->getData('licenseURL');
    }

    /**
     * Set the stored license URL for the submission content.
     * @param string $licenseUrl License URL of submission content
     * @return void
     */
    public function setLicenseURL($licenseUrl) {
        return $this->setData('licenseURL', $licenseUrl);
    }

    /**
     * Get title of article's section.
	 * @return string
	 */
    public function getSectionTitle() {
        return $this->getData('sectionTitle');
    }

    /**
     * Set title of article's section.
	 * @param string $sectionTitle string
	 */
    public function setSectionTitle($sectionTitle) {
        return $this->setData('sectionTitle', $sectionTitle);
    }

    /**
     * Get section abbreviation.
	 * @return string
	 */
    public function getSectionAbbrev() {
        return $this->getData('sectionAbbrev');
    }

    /**
     * Set section abbreviation.
	 * @param string $sectionAbbrev string
	 */
    public function setSectionAbbrev($sectionAbbrev) {
        return $this->setData('sectionAbbrev', $sectionAbbrev);
    }

    /**
     * Return the localized discipline.
     * DEPRECATED in favour of getLocalizedDiscipline
	 * @return string
	 */
    public function getArticleDiscipline() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedDiscipline();
    }

    /**
     * Return the localized subject classification.
     * DEPRECATED in favour of getLocalizedSubjectClass
	 * @return string
	 */
    public function getArticleSubjectClass() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedSubjectClass();
    }

    /**
     * Return the localized subject.
     * DEPRECATED in favour of getLocalizedSubject
	 * @return string
	 */
    public function getArticleSubject() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedSubject();
    }

	/**
	 * Return the localized geographical coverage. 
	 * DEPRECATED in favour of getLocalizedCoverageGeo.
	 * @return string
	 */
    public function getArticleCoverageGeo() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedCoverageGeo();
    }

	/**
	 * Return the localized chronological coverage. 
	 * DEPRECATED in favour of getLocalizedCoverageChron.
	 * @return string
	 */
    public function getArticleCoverageChron() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedCoverageChron();
    }

	/**
	 * Return the localized sample coverage. 
	 * DEPRECATED in favour of getLocalizedCoverageSample.
	 * @return string
	 */
    public function getArticleCoverageSample() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedCoverageSample();
    }

	/**
	 * Return the localized type (method/approach).
	 * DEPRECATED in favour of getLocalizedType.
	 * @return string
	 */
    public function getArticleTypeLegacy() {
        // Renamed slightly to avoid conflict with wizdam property if needed, but here kept as getArticleType wrapper
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedType();
    }

	/**
	 * Return the localized sponsor. 
	 * DEPRECATED in favour of getLocalizedSponsor.
	 * @return string
	 */
    public function getArticleSponsor() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedSponsor();
    }

    // [WIZDAM] Deklarasi level artikel -- Competing Interest, Ethical
    // Approval, Declaration of Generative AI. Dikonfirmasi lewat riset
    // publikasi akademik nyata (Elsevier, Springer, Taylor & Francis,
    // Cell Press, arXiv): ketiganya SATU pernyataan mencakup SELURUH
    // penulis ("The authors declare..."), BUKAN entri terpisah per
    // penulis -- atribusi individual (kalau perlu) dilakukan lewat
    // inisial DI DALAM teks pernyataan itu sendiri, bukan field
    // database terpisah. Disimpan lewat mekanisme generic settings
    // yang sama seperti sponsor (getData/setData -> article_settings
    // via ArticleDAO::updateLocaleFields()) -- TIDAK perlu kolom
    // tabel baru sama sekali.

    /**
     * Get localized Competing Interest declaration.
     * @return string
     */
    public function getLocalizedCompetingInterest() {
        return $this->getLocalizedData('competingInterest');
    }

    /**
     * Get Competing Interest declaration.
     * @param string|null $locale
     * @return string
     */
    public function getCompetingInterest($locale) {
        $value = $this->getData('competingInterest', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set Competing Interest declaration.
     * @param string $competingInterest
     * @param string|null $locale
     */
    public function setCompetingInterest($competingInterest, $locale) {
        return $this->setData('competingInterest', is_array($competingInterest) ? $competingInterest : (string) $competingInterest, $locale);
    }

    /**
     * Get localized Ethical Approval declaration.
     * @return string
     */
    public function getLocalizedEthicalApproval() {
        return $this->getLocalizedData('ethicalApproval');
    }

    /**
     * Get Ethical Approval declaration.
     * @param string|null $locale
     * @return string
     */
    public function getEthicalApproval($locale) {
        $value = $this->getData('ethicalApproval', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set Ethical Approval declaration.
     * @param string $ethicalApproval
     * @param string|null $locale
     */
    public function setEthicalApproval($ethicalApproval, $locale) {
        return $this->setData('ethicalApproval', is_array($ethicalApproval) ? $ethicalApproval : (string) $ethicalApproval, $locale);
    }

    /**
     * Get localized Declaration of Generative AI.
     * @return string
     */
    public function getLocalizedGenerativeAiDeclaration() {
        return $this->getLocalizedData('generativeAiDeclaration');
    }

    /**
     * Get Declaration of Generative AI.
     * @param string|null $locale
     * @return string
     */
    public function getGenerativeAiDeclaration($locale) {
        $value = $this->getData('generativeAiDeclaration', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set Declaration of Generative AI.
     * @param string $generativeAiDeclaration
     * @param string|null $locale
     */
    public function setGenerativeAiDeclaration($generativeAiDeclaration, $locale) {
        return $this->setData('generativeAiDeclaration', is_array($generativeAiDeclaration) ? $generativeAiDeclaration : (string) $generativeAiDeclaration, $locale);
    }

    // [WIZDAM] Tipe Artikel -- lihat classes/article/ArticleType.inc.php
    // (11 tipe baku, kode JATS standar) dan
    // classes/article/ArticleTypeCustom.inc.php (tipe kustom
    // per-jurnal). Pola generic settings SAMA seperti hideAuthor di
    // atas -- TIDAK localized (kode/ID, bukan teks bebas), TIDAK perlu
    // kolom tabel baru. TEPAT SATU dari kedua field ini yang seharusnya
    // terisi dalam satu waktu (baku ATAU kustom, tidak keduanya
    // sekaligus) -- pemilihan mana yang dipakai ditentukan di
    // form/template (radio button "Tipe Baku" vs "Tipe Kustom"), BUKAN
    // dipaksakan di level data object ini.
    //
    // INI BUKAN pengganti Section (topik/Mini Jurnal, konsep terpisah)
    // ATAU field 'type' bebas teks lama (Submission::getType(),
    // dikontrol setting 'metaType') -- keduanya TETAP ada berdampingan.

    /**
     * Get the standard article type code (ARTICLE_TYPE_* constant
     * value from ArticleType.inc.php), if a standard type was chosen.
     * @return string|null
     */
    public function getArticleTypeCode() {
        $value = $this->getData('articleTypeCode');
        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * Set the standard article type code.
     * @param string|null $articleTypeCode
     */
    public function setArticleTypeCode($articleTypeCode) {
        return $this->setData('articleTypeCode', $articleTypeCode);
    }

    /**
     * Get the custom article type ID (references
     * article_type_custom.custom_type_id), if a custom (journal-
     * specific) type was chosen instead of a standard one.
     * @return int|null
     */
    public function getArticleTypeCustomId() {
        $value = $this->getData('articleTypeCustomId');
        return $value !== null && (int) $value > 0 ? (int) $value : null;
    }

    /**
     * Set the custom article type ID.
     * @param int|null $articleTypeCustomId
     */
    public function setArticleTypeCustomId($articleTypeCustomId) {
        return $this->setData('articleTypeCustomId', $articleTypeCustomId !== null ? (int) $articleTypeCustomId : null);
    }

    /**
     * Get the localized article cover filename.
     * DEPRECATED in favour of getLocalizedFileName.
     * @return string|null
     */
    public function getArticleFileName() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function: getArticleFileName(). Use getLocalizedFileName() instead.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedFileName();
    }

	/**
	 * Get the localized article cover width. 
	 * DEPRECATED in favour of getLocalizedWidth.
	 * @return string
	 */
    public function getArticleWidth() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedWidth();
    }

	/**
	 * Get the localized article cover height. 
	 * DEPRECATED in favour of getLocalizedHeight.
	 * @return string
	 */
    public function getArticleHeight() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedHeight();
    }

	/**
	 * Get the localized article cover filename on the uploader's computer.
	 * DEPRECATED in favour of getLocalizedOriginalFileName.
	 * @return string
	 */
    public function getArticleOriginalFileName() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedData('originalFileName');
    }

	/**
	 * Get the localized article cover alternate text. 
	 * DEPRECATED in favour of getLocalizedCoverPageAltText.
	 * @return string
	 */
    public function getArticleCoverPageAltText() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedCoverPageAltText();
    }

	/**
	 * Get the flag indicating whether or not to show an article cover page. 
	 * DEPRECATED in favour of getLocalizedShowCoverPage.
	 * @return string
	 */
    public function getArticleShowCoverPage() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedShowCoverPage();
    }

    /**
     * Get comments to editor.
	 * @return string
     */
    public function getCommentsToEditor() {
        return $this->getData('commentsToEditor');
    }

    /**
     * Set comments to editor.
	 * @param string $commentsToEditor string
     */
    public function setCommentsToEditor($commentsToEditor) {
        return $this->setData('commentsToEditor', $commentsToEditor);
    }

	/**
	 * Get current review round.
	 * @return int
	 */
    public function getCurrentRound() {
        return $this->getData('currentRound');
    }

	/**
	 * Set current review round.
	 * @param int $currentRound int
	 */
    public function setCurrentRound($currentRound) {
        return $this->setData('currentRound', $currentRound);
    }

	/**
	 * Get editor file id.
	 * @return int
	 */
    public function getEditorFileId() {
        return $this->getData('editorFileId');
    }

	/**
	 * Set editor file id.
	 * @param int $editorFileId int
	 */
    public function setEditorFileId($editorFileId) {
        return $this->setData('editorFileId', $editorFileId);
    }

	/**
	 * Get expedited
	 * @return boolean
	 */
    public function getFastTracked() {
        return $this->getData('fastTracked');
    }

	/**
	 * Set fastTracked
	 * @param bool $fastTracked boolean
	 */
    public function setFastTracked($fastTracked) {
        return $this->setData('fastTracked',$fastTracked);
    }

	/**
	 * Return option selection indicating if author should be hidden in issue ToC.
	 * @return int AUTHOR_TOC_...
	 */
    public function getHideAuthor() {
        return $this->getData('hideAuthor');
    }

	/**
	 * Set option selection indicating if author should be hidden in issue ToC.
	 * @param bool $hideAuthor int AUTHOR_TOC_...
	 */
    public function setHideAuthor($hideAuthor) {
        return $this->setData('hideAuthor', $hideAuthor);
    }

    /**
     * Return locale string corresponding to RT comments status.
	 * @return string
     */
    public function getCommentsStatusString() {
        switch ($this->getCommentsStatus()) {
            case COMMENTS_DISABLE:
                return 'article.comments.disable';
            case COMMENTS_ENABLE:
                return 'article.comments.enable';
            default:
                return 'article.comments.sectionDefault';
        }
    }

    /**
     * Return boolean indicating if article RT comments should be enabled.
	 * @return int
     */
    public function getEnableComments() {
        switch ($this->getCommentsStatus()) {
            case COMMENTS_DISABLE:
                return false;
            case COMMENTS_ENABLE:
                return true;
            case COMMENTS_SECTION_DEFAULT:
                /** @var SectionDAO $sectionDao */
                $sectionDao = DAORegistry::getDAO('SectionDAO');
                $section = $sectionDao->getSection($this->getSectionId(), $this->getJournalId(), true);
                if (!$section || $section->getDisableComments()) {
                    return false;
                } else {
                    return true;
                }
        }
    }

    /**
     * Get an associative array matching RT comments status codes with locale strings.
	 * @return array comments status => localeString
     */
    public function getCommentsStatusOptions() {
        static $commentsStatusOptions = array(
            COMMENTS_SECTION_DEFAULT => 'article.comments.sectionDefault',
            COMMENTS_DISABLE => 'article.comments.disable',
            COMMENTS_ENABLE => 'article.comments.enable'
        );
        return $commentsStatusOptions;
    }

    /**
     * Get an array of user IDs associated with this article
	 * @param $authors boolean
	 * @param $reviewers boolean
	 * @param $editors boolean
	 * @param $proofreader boolean
	 * @param $copyeditor boolean
	 * @param $layoutEditor boolean
	 * @return array User IDs
     */
    public function getAssociatedUserIds($authors = true, $reviewers = true, $editors = true, $proofreader = true, $copyeditor = true, $layoutEditor = true) {
        $articleId = $this->getId();
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        $userIds = array();

        if($authors) {
            $userId = $this->getUserId();
            if ($userId) $userIds[] = array('id' => $userId, 'role' => 'author');
        }

        if($editors) {
            /** @var EditAssignmentDAO $editAssignmentDao */
            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $editAssignments = $editAssignmentDao->getEditorAssignmentsByArticleId($articleId);
            while ($editAssignment = $editAssignments->next()) {
                $userId = $editAssignment->getEditorId();
                if ($userId) $userIds[] = array('id' => $userId, 'role' => 'editor');
                unset($editAssignment);
            }
        }

        if($copyeditor) {
            $copyedSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
            $userId = $copyedSignoff->getUserId();
            if ($userId) $userIds[] = array('id' => $userId, 'role' => 'copyeditor');
        }

        if($layoutEditor) {
            $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
            $userId = $layoutSignoff->getUserId();
            if ($userId) $userIds[] = array('id' => $userId, 'role' => 'layoutEditor');
        }

        if($proofreader) {
            $proofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
            $userId = $proofSignoff->getUserId();
            if ($userId) $userIds[] = array('id' => $userId, 'role' => 'proofreader');
        }

        if($reviewers) {
            /** @var ReviewAssignmentDAO $reviewAssignmentDao */
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $reviewAssignments = $reviewAssignmentDao->getBySubmissionId($articleId);
            foreach ($reviewAssignments as $reviewAssignment) {
                $userId = $reviewAssignment->getReviewerId();
                if ($userId) $userIds[] = array('id' => $userId, 'role' => 'reviewer');
                unset($reviewAssignment);
            }
        }

        return $userIds;
    }

    /**
     * Get the signoff for this article
	 * @param string $signoffType string
	 * @return Signoff
     */
    public function getSignoff($signoffType) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        return $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $this->getId());
    }

    /**
     * Get the file for this article at a given signoff stage.
	 * @param string $signoffType string
	 * @param $idOnly boolean Return only file ID
	 * @return ArticleFile
     */
    public function getFileBySignoffType($signoffType, $idOnly = false) {
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        $signoff = $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $this->getId());
        if (!$signoff) {
            return false;
        }

        if ($idOnly) {
            return $signoff->getFileId();
        }

        $articleFile = $articleFileDao->getArticleFile($signoff->getFileId(), $signoff->getFileRevision());
        return $articleFile;
    }

    /**
     * Get the user associated with a given signoff and this article.
	 * @param string $signoffType string
	 * @return User
     */
    public function getUserBySignoffType($signoffType) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $signoff = $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $this->getId());
        if (!$signoff) {
            return false;
        }

        $user = $userDao->getById($signoff->getUserId());
        return $user;
    }

    /**
     * Get the user id associated with a given signoff and this article
	 * @param string $signoffType string
	 * @return int
     */
    public function getUserIdBySignoffType($signoffType) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $signoff = $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $this->getId());
        if (!$signoff) return false;

        return $signoff->getUserId();
    }


    //
    // LUMERA EXTENSION: eLocator & PII ---
    //
    /**
     * Get the electronic locator (e-locator) of the article.
     * @return string
     */
    public function getELocator() {
        return (string) $this->getData('eLocator');
    }

    /**
     * Set the electronic locator (e-locator) of the article.
     * @param string $eLocator string
     */
    public function setELocator($eLocator) {
        return $this->setData('eLocator', $eLocator);
    }

    /**
     * Get the Publisher Item Identifier (PII).
     * @return string
     */
    public function getPii() {
        return (string) $this->getData('pii');
    }

    /**
     * Set the Publisher Item Identifier (PII).
     * @param string $pii string
     */
    public function setPii($pii) {
        return $this->setData('pii', $pii);
    }

    /**
     * [CROSSREF INTERCEPTOR]
     * Get pages or fallback to eLocator automatically for plugins.
     * Semua plugin yang meminta 'pages' akan menerima eLocator jika halaman kosong.
     * @return string
     */
    public function getPages() {
        $pages = (string) $this->getData('pages');
        if ($pages !== '') {
            return $pages;
        }
        return $this->getELocator();
    }

    /**
     * Get starting page (or eLocator if pages are empty).
     * @return string
     */
    public function getStartingPage() {
        $pagesStr = $this->getPages(); 
        
        $pattern = '/^' . preg_quote(ELOCATOR_PREFIX, '/') . '\d+$/i';
        
        if (preg_match($pattern, $pagesStr)) {
            return $pagesStr;
        }

        if ($pagesStr !== '' && preg_match('/^[^\d]*(\d+)\D*(.*)$/', $pagesStr, $pages)) {
            return $pages[1] ?? '';
        }
        return '';
    }

    /**
     * Get ending page (returns empty if using eLocator).
     * @return string
     */
    public function getEndingPage() {
        $pagesStr = $this->getPages();
        
        $pattern = '/^' . preg_quote(ELOCATOR_PREFIX, '/') . '\d+$/i';
        
        if (preg_match($pattern, $pagesStr)) {
            return ''; 
        }

        if ($pagesStr !== '' && preg_match('/^[^\d]*(\d+)\D*(.*)$/', $pagesStr, $pages)) {
            return $pages[2] ?? '';
        }
        return '';
    }

    /**
     * Initialize the copyright and license metadata for an article.
     * @param Article string
     */
    public function initializePermissions() {
        $this->setLicenseURL($this->getDefaultLicenseURL());
        $this->setCopyrightHolder($this->getDefaultCopyrightHolder(null), null);
        if ($this->getStatus() == STATUS_PUBLISHED) {
            $this->setCopyrightYear($this->getDefaultCopyrightYear());
        }
    }

    /**
     * Determines whether or not the license for copyright on this 
     * Article is a Creative Commons license or not.
	 * @return boolean
	 */
    public function isCCLicense() {
        if (preg_match('/creativecommons\.org/i', $this->getLicenseURL())) {
            return true;
        } else {
            return false;
        }
    }
    
}
?>