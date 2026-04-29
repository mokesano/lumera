<?php
declare(strict_types=1);

/**
 * @defgroup submission
 */

/**
 * @file classes/submission/Submission.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Submission
 * @ingroup submission
 *
 * @brief Submission class.
 */

class Submission extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Submission() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::Submission(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Returns the association type of this submission
     * @return integer one of the ASSOC_TYPE_* constants
     */
    public function getAssocType() {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Get a piece of data for this object, localized to the current
     * locale if possible.
     * @param $key string
     * @param $preferredLocale string
     * @return mixed
     */
    public function getLocalizedData($key, $preferredLocale = null) {
        if (is_null($preferredLocale)) $preferredLocale = AppLocale::getLocale();
        $localePrecedence = array($preferredLocale, $this->getLocale());
        foreach ($localePrecedence as $locale) {
            if (empty($locale)) continue;
            // Hapus '&'
            $value = $this->getData($key, $locale);
            if (!empty($value)) return $value;
            unset($value);
        }

        // Fallback: Get the first available piece of data.
        // Hapus '&'
        $data = $this->getData($key, null);
        if (!empty($data)) return $data[array_shift(array_keys($data))];

        // No data available; return null.
        unset($data);
        $data = null;
        return $data;
    }

    //
    // Get/set methods
    //

    /**
     * Return first author
     * @param $lastOnly boolean return lastname only (default false)
     * @return string
     */
    public function getFirstAuthor($lastOnly = false) {
        $authors = $this->getAuthors();
        if (is_array($authors) && !empty($authors)) {
            $author = $authors[0];
            return $lastOnly ? $author->getLastName() : $author->getFullName();
        } else {
            return null;
        }
    }

    /**
     * Return string of author names, separated by the specified token
     * @param $lastOnly boolean return list of lastnames only (default false)
     * @param $separator string separator for names (default comma+space)
     * @return string
     */
    public function getAuthorString($lastOnly = false, $separator = ', ') {
        $authors = $this->getAuthors();

        $str = '';
        foreach($authors as $author) {
            if (!empty($str)) {
                $str .= $separator;
            }
            $str .= $lastOnly ? $author->getLastName() : $author->getFullName();
        }
        return $str;
    }

    /**
     * Return a list of author email addresses.
     * @return array
     */
    public function getAuthorEmails() {
        $authors = $this->getAuthors();

        import('lib.pkp.classes.mail.Mail');
        $returner = array();
        foreach($authors as $author) {
            $returner[] = Mail::encodeDisplayName($author->getFullName()) . ' <' . $author->getEmail() . '>';
        }
        return $returner;
    }

    /**
     * Get all authors of this submission.
     * @return array Authors
     */
    public function getAuthors() {
        // Hapus '&'
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        return $authorDao->getAuthorsBySubmissionId($this->getId());
    }

    /**
     * Get the primary author of this submission.
     * @return Author
     */
    public function getPrimaryAuthor() {
        // Hapus '&'
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        return $authorDao->getPrimaryContact($this->getId());
    }

    /**
     * Get user ID of the submitter.
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set user ID of the submitter.
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }

    /**
     * Return the user of the submitter.
     * @return User
     */
    public function getUser() {
        // Hapus '&'
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getById($this->getUserId(), true);
    }

    /**
     * Get the locale of the submission.
     * @return string
     */
    public function getLocale() {
        return $this->getData('locale');
    }

    /**
     * Set the locale of the submission.
     * @param $locale string
     */
    public function setLocale($locale) {
        return $this->setData('locale', $locale);
    }

    /**
     * Get "localized" submission title (if applicable).
     * @param $preferredLocale string
     * @return string
     */
    public function getLocalizedTitle($preferredLocale = null) {
        return $this->getLocalizedData('title', $preferredLocale);
    }

    /**
     * Get title.
     * @param $locale
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title.
     * @param $title string
     * @param $locale
     */
    public function setTitle($title, $locale) {
        $this->setCleanTitle($title, $locale);
        return $this->setData('title', $title, $locale);
    }

    /**
     * Set 'clean' title (with punctuation removed).
     * @param $cleanTitle string
     * @param $locale
     */
    public function setCleanTitle($cleanTitle, $locale) {
        $punctuation = array ("\"", "\'", ",", ".", "!", "?", "-", "$", "(", ")");
        $cleanTitle = str_replace($punctuation, "", $cleanTitle);
        return $this->setData('cleanTitle', $cleanTitle, $locale);
    }

    /**
     * Get "localized" submission prefix (if applicable).
     * @return string
     */
    public function getLocalizedPrefix() {
        return $this->getLocalizedData('prefix');
    }

    /**
     * Get prefix.
     * @param $locale
     * @return string
     */
    public function getPrefix($locale) {
        return $this->getData('prefix', $locale);
    }

    /**
     * Set prefix.
     * @param $prefix string
     * @param $locale
     */
    public function setPrefix($prefix, $locale) {
        return $this->setData('prefix', $prefix, $locale);
    }

    /**
     * Get "localized" submission abstract (if applicable).
     * @return string
     */
    public function getLocalizedAbstract() {
        return $this->getLocalizedData('abstract');
    }

    /**
     * Get abstract.
     * @param $locale
     * @return string
     */
    public function getAbstract($locale) {
        return $this->getData('abstract', $locale);
    }

    /**
     * Set abstract.
     * @param $abstract string
     * @param $locale
     */
    public function setAbstract($abstract, $locale) {
        return $this->setData('abstract', $abstract, $locale);
    }

    /**
     * Return the localized discipline
     * @return string
     */
    public function getLocalizedDiscipline() {
        return $this->getLocalizedData('discipline');
    }

    /**
     * Get discipline
     * @param $locale
     * @return string
     */
    public function getDiscipline($locale) {
        return $this->getData('discipline', $locale);
    }

    /**
     * Set discipline
     * @param $discipline string
     * @param $locale
     */
    public function setDiscipline($discipline, $locale) {
        return $this->setData('discipline', $discipline, $locale);
    }

    /**
     * Return the localized subject classification
     * @return string
     */
    public function getLocalizedSubjectClass() {
        return $this->getLocalizedData('subjectClass');
    }

    /**
     * Get subject classification.
     * @param $locale
     * @return string
     */
    public function getSubjectClass($locale) {
        return $this->getData('subjectClass', $locale);
    }

    /**
     * Set subject classification.
     * @param $subjectClass string
     * @param $locale
     */
    public function setSubjectClass($subjectClass, $locale) {
        return $this->setData('subjectClass', $subjectClass, $locale);
    }

    /**
     * Return the localized subject
     * @return string
     */
    public function getLocalizedSubject() {
        return $this->getLocalizedData('subject');
    }

    /**
     * Get subject.
     * @param $locale
     * @return string
     */
    public function getSubject($locale) {
        return $this->getData('subject', $locale);
    }

    /**
     * Set subject.
     * @param $subject string
     * @param $locale
     */
    public function setSubject($subject, $locale) {
        return $this->setData('subject', $subject, $locale);
    }

    /**
     * Return the localized geographical coverage
     * @return string
     */
    public function getLocalizedCoverageGeo() {
        return $this->getLocalizedData('coverageGeo');
    }

    /**
     * Get geographical coverage.
     * @param $locale
     * @return string
     */
    public function getCoverageGeo($locale) {
        return $this->getData('coverageGeo', $locale);
    }

    /**
     * Set geographical coverage.
     * @param $coverageGeo string
     * @param $locale
     */
    public function setCoverageGeo($coverageGeo, $locale) {
        return $this->setData('coverageGeo', $coverageGeo, $locale);
    }

    /**
     * Return the localized chronological coverage
     * @return string
     */
    public function getLocalizedCoverageChron() {
        return $this->getLocalizedData('coverageChron');
    }

    /**
     * Get chronological coverage.
     * @param $locale
     * @return string
     */
    public function getCoverageChron($locale) {
        return $this->getData('coverageChron', $locale);
    }

    /**
     * Set chronological coverage.
     * @param $coverageChron string
     * @param $locale
     */
    public function setCoverageChron($coverageChron, $locale) {
        return $this->setData('coverageChron', $coverageChron, $locale);
    }

    /**
     * Return the localized sample coverage
     * @return string
     */
    public function getLocalizedCoverageSample() {
        return $this->getLocalizedData('coverageSample');
    }

    /**
     * Get research sample coverage.
     * @param $locale
     * @return string
     */
    public function getCoverageSample($locale) {
        return $this->getData('coverageSample', $locale);
    }

    /**
     * Set geographical coverage.
     * @param $coverageSample string
     * @param $locale
     */
    public function setCoverageSample($coverageSample, $locale) {
        return $this->setData('coverageSample', $coverageSample, $locale);
    }

    /**
     * Return the localized type (method/approach)
     * @return string
     */
    public function getLocalizedType() {
        return $this->getLocalizedData('type');
    }

    /**
     * Get type (method/approach).
     * @param $locale
     * @return string
     */
    public function getType($locale) {
        return $this->getData('type', $locale);
    }

    /**
     * Set type (method/approach).
     * @param $type string
     * @param $locale
     */
    public function setType($type, $locale) {
        return $this->setData('type', $type, $locale);
    }

    /**
     * Get rights.
     * @param $locale
     * @return string
     */
    public function getRights($locale) {
        return $this->getData('rights', $locale);
    }

    /**
     * Set rights.
     * @param $rights string
     * @param $locale
     */
    public function setRights($rights, $locale) {
        return $this->setData('rights', $rights, $locale);
    }

    /**
     * Get source.
     * @param $locale
     * @return string
     */
    public function getSource($locale) {
        return $this->getData('source', $locale);
    }

    /**
     * Set source.
     * @param $source string
     * @param $locale
     */
    public function setSource($source, $locale) {
        return $this->setData('source', $source, $locale);
    }

    /**
     * Get language.
     * @return string
     */
    public function getLanguage() {
        return $this->getData('language');
    }

    /**
     * Set language.
     * @param $language string
     */
    public function setLanguage($language) {
        return $this->setData('language', $language);
    }

    /**
     * Return the localized sponsor
     * @return string
     */
    public function getLocalizedSponsor() {
        return $this->getLocalizedData('sponsor');
    }

    /**
     * Get sponsor.
     * @param $locale
     * @return string
     */
    public function getSponsor($locale) {
        return $this->getData('sponsor', $locale);
    }

    /**
     * Set sponsor.
     * @param $sponsor string
     * @param $locale
     */
    public function setSponsor($sponsor, $locale) {
        return $this->setData('sponsor', $sponsor, $locale);
    }

    /**
     * Get citations.
     * @return string
     */
    public function getCitations() {
        return $this->getData('citations');
    }

    /**
     * Set citations.
     * @param $citations string
     */
    public function setCitations($citations) {
        return $this->setData('citations', $citations);
    }

    /**
     * Get the localized cover filename
     * @return string
     */
    public function getLocalizedFileName() {
        return $this->getLocalizedData('fileName');
    }

    /**
     * get file name
     * @param $locale string
     * @return string
     */
    public function getFileName($locale) {
        return $this->getData('fileName', $locale);
    }

    /**
     * set file name
     * @param $fileName string
     * @param $locale string
     */
    public function setFileName($fileName, $locale) {
        return $this->setData('fileName', $fileName, $locale);
    }

    /**
     * Get the localized submission cover width
     * @return string
     */
    public function getLocalizedWidth() {
        return $this->getLocalizedData('width');
    }

    /**
     * get width of cover page image
     * @param $locale string
     * @return string
     */
    public function getWidth($locale) {
        return $this->getData('width', $locale);
    }

    /**
     * set width of cover page image
     * @param $locale string
     * @param $width int
     */
    public function setWidth($width, $locale) {
        return $this->setData('width', $width, $locale);
    }

    /**
     * Get the localized submission cover height
     * @return string
     */
    public function getLocalizedHeight() {
        return $this->getLocalizedData('height');
    }

    /**
     * get height of cover page image
     * @param $locale string
     * @return string
     */
    public function getHeight($locale) {
        return $this->getData('height', $locale);
    }

    /**
     * set height of cover page image
     * @param $locale string
     * @param $height int
     */
    public function setHeight($height, $locale) {
        return $this->setData('height', $height, $locale);
    }

    /**
     * Get the localized cover filename on the uploader's computer
     * @return string
     */
    public function getLocalizedOriginalFileName() {
        return $this->getLocalizedData('originalFileName');
    }

    /**
     * get original file name
     * @param $locale string
     * @return string
     */
    public function getOriginalFileName($locale) {
        return $this->getData('originalFileName', $locale);
    }

    /**
     * set original file name
     * @param $originalFileName string
     * @param $locale string
     */
    public function setOriginalFileName($originalFileName, $locale) {
        return $this->setData('originalFileName', $originalFileName, $locale);
    }

    /**
     * Get the localized cover alternate text
     * @return string
     */
    public function getLocalizedCoverPageAltText() {
        return $this->getLocalizedData('coverPageAltText');
    }

    /**
     * get cover page alternate text
     * @param $locale string
     * @return string
     */
    public function getCoverPageAltText($locale) {
        return $this->getData('coverPageAltText', $locale);
    }

    /**
     * set cover page alternate text
     * @param $coverPageAltText string
     * @param $locale string
     */
    public function setCoverPageAltText($coverPageAltText, $locale) {
        return $this->setData('coverPageAltText', $coverPageAltText, $locale);
    }

    /**
     * Get the flag indicating whether or not to show
     * a cover page.
     * @return string
     */
    public function getLocalizedShowCoverPage() {
        return $this->getLocalizedData('showCoverPage');
    }

    /**
     * get show cover page
     * @param $locale string
     * @return int
     */
    public function getShowCoverPage($locale) {
        return $this->getData('showCoverPage', $locale);
    }

    /**
     * set show cover page
     * @param $showCoverPage int
     * @param $locale string
     */
    public function setShowCoverPage($showCoverPage, $locale) {
        return $this->setData('showCoverPage', $showCoverPage, $locale);
    }

    /**
     * get hide cover page thumbnail in Toc
     * @param $locale string
     * @return int
     */
    public function getHideCoverPageToc($locale) {
        return $this->getData('hideCoverPageToc', $locale);
    }

    /**
     * set hide cover page thumbnail in Toc
     * @param $hideCoverPageToc int
     * @param $locale string
     */
    public function setHideCoverPageToc($hideCoverPageToc, $locale) {
        return $this->setData('hideCoverPageToc', $hideCoverPageToc, $locale);
    }

    /**
     * get hide cover page in abstract view
     * @param $locale string
     * @return int
     */
    public function getHideCoverPageAbstract($locale) {
        return $this->getData('hideCoverPageAbstract', $locale);
    }

    /**
     * set hide cover page in abstract view
     * @param $hideCoverPageAbstract int
     * @param $locale string
     */
    public function setHideCoverPageAbstract($hideCoverPageAbstract, $locale) {
        return $this->setData('hideCoverPageAbstract', $hideCoverPageAbstract, $locale);
    }

    /**
     * Get localized hide cover page in abstract view
     */
    public function getLocalizedHideCoverPageAbstract() {
        return $this->getLocalizedData('hideCoverPageAbstract');
    }

    /**
     * Get submission date.
     * @return string
     */
    public function getDateSubmitted() {
        return $this->getData('dateSubmitted');
    }

    /**
     * Set submission date.
     * @param $dateSubmitted string
     */
    public function setDateSubmitted($dateSubmitted) {
        return $this->setData('dateSubmitted', $dateSubmitted);
    }

    /**
     * Get the date of the last status modification.
     * @return string
     */
    public function getDateStatusModified() {
        return $this->getData('dateStatusModified');
    }

    /**
     * Set the date of the last status modification.
     * @param $dateModified string
     */
    public function setDateStatusModified($dateModified) {
        return $this->setData('dateStatusModified', $dateModified);
    }

    /**
     * Get the date of the last modification.
     * @return string
     */
    public function getLastModified() {
        return $this->getData('lastModified');
    }

    /**
     * Set the date of the last modification.
     * @param $dateModified string
     */
    public function setLastModified($dateModified) {
        return $this->setData('lastModified', $dateModified);
    }

    /**
     * Stamp the date of the last modification to the current time.
     */
    public function stampModified() {
        return $this->setLastModified(Core::getCurrentDate());
    }

    /**
     * Stamp the date of the last status modification to the current time.
     */
    public function stampStatusModified() {
        return $this->setDateStatusModified(Core::getCurrentDate());
    }

    /**
     * Get submission status.
     * @return int
     */
    public function getStatus() {
        return $this->getData('status');
    }

    /**
     * Set submission status.
     * @param $status int
     */
    public function setStatus($status) {
        return $this->setData('status', $status);
    }

    /**
     * Get a map for status constant to locale key.
     * @return array
     */
    public function getStatusMap() {
        // Hapus '&'
        static $statusMap;
        if (!isset($statusMap)) {
            $statusMap = array(
                STATUS_ARCHIVED => 'submissions.archived',
                STATUS_QUEUED => 'submissions.queued',
                STATUS_PUBLISHED => 'submissions.published',
                STATUS_DECLINED => 'submissions.declined',
                STATUS_QUEUED_UNASSIGNED => 'submissions.queuedUnassigned',
                STATUS_QUEUED_REVIEW => 'submissions.queuedReview',
                STATUS_QUEUED_EDITING => 'submissions.queuedEditing',
                STATUS_INCOMPLETE => 'submissions.incomplete'
            );
        }
        return $statusMap;
    }

    /**
     * Get a locale key for the paper's current status.
     * @return string
     */
    public function getStatusKey() {
        $statusMap = $this->getStatusMap(); // Hapus '&'
        return $statusMap[$this->getStatus()];
    }

    /**
     * Get submission progress (most recently completed submission step).
     * @return int
     */
    public function getSubmissionProgress() {
        return $this->getData('submissionProgress');
    }

    /**
     * Set submission progress.
     * @param $submissionProgress int
     */
    public function setSubmissionProgress($submissionProgress) {
        return $this->setData('submissionProgress', $submissionProgress);
    }

    /**
     * Get submission file id.
     * @return int
     */
    public function getSubmissionFileId() {
        return $this->getData('submissionFileId');
    }

    /**
     * Set submission file id.
     * @param $submissionFileId int
     */
    public function setSubmissionFileId($submissionFileId) {
        return $this->setData('submissionFileId', $submissionFileId);
    }

    /**
     * Get revised file id.
     * @return int
     */
    public function getRevisedFileId() {
        return $this->getData('revisedFileId');
    }

    /**
     * Set revised file id.
     * @param $revisedFileId int
     */
    public function setRevisedFileId($revisedFileId) {
        return $this->setData('revisedFileId', $revisedFileId);
    }

    /**
     * Get review file id.
     * @return int
     */
    public function getReviewFileId() {
        return $this->getData('reviewFileId');
    }

    /**
     * Set review file id.
     * @param $reviewFileId int
     */
    public function setReviewFileId($reviewFileId) {
        return $this->setData('reviewFileId', $reviewFileId);
    }

    /**
     * get pages
     * @return string
     */
    public function getPages() {
        return $this->getData('pages');
    }

    /**
     * get pages as a nested array of page ranges
     * for example, pages of "pp. ii-ix, 9,15-18,a2,b2-b6" will return array( array(0 => 'ii', 1, => 'ix'), array(0 => '9'), array(0 => '15', 1 => '18'), array(0 => 'a2'), array(0 => 'b2', 1 => 'b6') )
     * @return array
     */
    public function getPageArray() {
        $pages = $this->getData('pages');
        // Strip any leading word
        if (preg_match('/^[[:alpha:]]+\W/', $pages)) {
            // but don't strip a leading roman numeral
            if (!preg_match('/^[MDCLXVUI]+\W/i', $pages)) {
                // strip the word or abbreviation, including the period or colon
                $pages = preg_replace('/^[[:alpha:]]+[:.]?/', '', $pages);
            }
        }
        // strip leading and trailing space
        $pages = trim($pages);
        // shortcut the explode/foreach if the remainder is an empty value
        if ($pages === '') {
            return array();
        }
        // commas indicate distinct ranges
        $ranges = explode(',', $pages);
        $pageArray = array();
        foreach ($ranges as $range) {
            // hyphens (or double-hyphens) indicate range spans
            $pageArray[] = array_map('trim', explode('-', str_replace('--', '-', $range), 2));
        }
        return $pageArray;
    }

    /**
     * set pages
     * @param $pages string
     */
    public function setPages($pages) {
        return $this->setData('pages',$pages);
    }

    /**
     * Return submission RT comments status.
     * @return int
     */
    public function getCommentsStatus() {
        return $this->getData('commentsStatus');
    }

    /**
     * Set submission RT comments status.
     * @param $commentsStatus boolean
     */
    public function setCommentsStatus($commentsStatus) {
        return $this->setData('commentsStatus', $commentsStatus);
    }
}

?>