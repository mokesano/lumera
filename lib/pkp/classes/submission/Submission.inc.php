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
     * [SHIM] Backward Compatibility.
     */
    public function Submission() {
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
     * Returns the association type of this submission.
     * @return int
     */
    public function getAssocType() {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Get a piece of data for this object, localized to the current
     * locale if possible.
     * @param string $key
     * @param string|null $preferredLocale
     * @return mixed
     */
    public function getLocalizedData($key, $preferredLocale = null) {
        if ($preferredLocale === null) {
            $preferredLocale = AppLocale::getLocale();
        }
        
        $localePrecedence = [$preferredLocale, $this->getLocale()];
        foreach ($localePrecedence as $locale) {
            if ($locale === null || $locale === '') {
                continue;
            }
            $value = $this->getData($key, $locale);
            if (is_array($value)) {
                continue;
            }
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $data = $this->getData($key, null);
        if (is_array($data) && !empty($data)) {
            $keys = array_keys($data);
            return $data[array_shift($keys)];
        }

        return null;
    }

    //
    // Get/set methods
    //

    /**
     * Return first author.
     * @param bool $lastOnly
     * @return string|null
     */
    public function getFirstAuthor($lastOnly = false) {
        $authors = $this->getAuthors();
        if (is_array($authors) && !empty($authors)) {
            $author = $authors[0];
            return $lastOnly ? $author->getLastName() : $author->getFullName();
        }
        return null;
    }

    /**
     * Return string of author names, separated by the specified token.
     * @param bool $lastOnly
     * @param string $separator
     * @return string
     */
    public function getAuthorString($lastOnly = false, $separator = ', ') {
        $authors = $this->getAuthors();
        $str = '';
        foreach ($authors as $author) {
            if ($str !== '') {
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
        $returner = [];
        foreach ($authors as $author) {
            $returner[] = Mail::encodeDisplayName($author->getFullName()) . ' <' . $author->getEmail() . '>';
        }
        return $returner;
    }

    /**
     * Get all authors of this submission.
     * @return array Authors
     */
    public function getAuthors() {
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        return $authorDao->getAuthorsBySubmissionId($this->getId());
    }

    /**
     * Get the primary author of this submission.
     * @return Author|null
     */
    public function getPrimaryAuthor() {
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        return $authorDao->getPrimaryContact($this->getId());
    }

    /**
     * Get user ID of the submitter.
     * @return int|null
     */
    public function getUserId() {
        $id = $this->getData('userId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set user ID of the submitter.
     * @param int|null $userId
     */
    public function setUserId($userId) {
        $this->setData('userId', $userId !== null ? (int) $userId : null);
    }

    /**
     * Return the user of the submitter.
     * @return User|null
     */
    public function getUser() {
        /** @var UserDAO $userDao */
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
     * @param string $locale
     */
    public function setLocale($locale) {
        $this->setData('locale', (string) $locale);
    }

    /**
     * Get "localized" submission title (if applicable).
     * @param string|null $preferredLocale
     * @return string
     */
    public function getLocalizedTitle($preferredLocale = null) {
        return $this->getLocalizedData('title', $preferredLocale);
    }

    /**
     * Get title.
     * @param string|null $locale
     * @return string
     */
    public function getTitle($locale) {
        $title = $this->getData('title', $locale);
        return is_array($title) ? $title : (string) $title;
    }

    /**
     * Set title.
     * @param string $title
     * @param string|null $locale
     */
    public function setTitle($title, $locale) {
        $this->setCleanTitle($title, $locale);
        return $this->setData('title', is_array($title) ? $title : (string) $title, $locale);
    }

    /**
     * Set 'clean' title (with punctuation removed).
     * @param string $cleanTitle
     * @param string|null $locale
     */
    public function setCleanTitle($cleanTitle, $locale) {
        $punctuation = ['"', "'", ',', '.', '!', '?', '-', '$', '(', ')'];
        if (is_array($cleanTitle)) {
            $cleanTitle = array_map(function ($value) use ($punctuation) {
                return str_replace($punctuation, '', (string) $value);
            }, $cleanTitle);
        } else {
            $cleanTitle = str_replace($punctuation, '', (string) $cleanTitle);
        }
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
     * @param string|null $locale
     * @return string
     */
    public function getPrefix($locale) {
        $value = $this->getData('prefix', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set prefix.
     * @param string $prefix
     * @param string|null $locale
     */
    public function setPrefix($prefix, $locale) {
        return $this->setData('prefix', is_array($prefix) ? $prefix : (string) $prefix, $locale);
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
     * @param string|null $locale
     * @return string
     */
    public function getAbstract($locale) {
        return $this->getData('abstract', $locale);
    }

    /**
     * Set abstract.
     * @param string $abstract
     * @param string|null $locale
     */
    public function setAbstract($abstract, $locale) {
        return $this->setData('abstract', is_array($abstract) ? $abstract : (string) $abstract, $locale);
    }

    /**
     * Return the localized discipline.
     * @return string
     */
    public function getLocalizedDiscipline() {
        return $this->getLocalizedData('discipline');
    }

    /**
     * Get discipline.
     * @param string|null $locale
     * @return string
     */
    public function getDiscipline($locale) {
        return $this->getData('discipline', $locale);
    }

    /**
     * Set discipline.
     * @param string $discipline
     * @param string|null $locale
     */
    public function setDiscipline($discipline, $locale) {
        return $this->setData('discipline', is_array($discipline) ? $discipline : (string) $discipline, $locale);
    }

    /**
     * Return the localized subject classification.
     * @return string
     */
    public function getLocalizedSubjectClass() {
        return $this->getLocalizedData('subjectClass');
    }

    /**
     * Get subject classification.
     * @param string|null $locale
     * @return string
     */
    public function getSubjectClass($locale) {
        return $this->getData('subjectClass', $locale);
    }

    /**
     * Set subject classification.
     * @param string $subjectClass
     * @param string|null $locale
     */
    public function setSubjectClass($subjectClass, $locale) {
        return $this->setData('subjectClass', is_array($subjectClass) ? $subjectClass : (string) $subjectClass, $locale);
    }

    /**
     * Return the localized subject.
     * @return string
     */
    public function getLocalizedSubject() {
        return $this->getLocalizedData('subject');
    }

    /**
     * Get subject.
     * @param string|null $locale
     * @return string
     */
    public function getSubject($locale) {
        return $this->getData('subject', $locale);
    }

    /**
     * Set subject.
     * @param string $subject
     * @param string|null $locale
     */
    public function setSubject($subject, $locale) {
        return $this->setData('subject', is_array($subject) ? $subject : (string) $subject, $locale);
    }

    /**
     * Return the localized geographical coverage.
     * @return string
     */
    public function getLocalizedCoverageGeo() {
        return $this->getLocalizedData('coverageGeo');
    }

    /**
     * Get geographical coverage.
     * @param string|null $locale
     * @return string
     */
    public function getCoverageGeo($locale) {
        $coverageGeo = $this->getData('coverageGeo', $locale);
        return is_array($coverageGeo) ? $coverageGeo : (string) $coverageGeo;
    }

    /**
     * Set geographical coverage.
     * @param string $coverageGeo
     * @param string|null $locale
     */
    public function setCoverageGeo($coverageGeo, $locale) {
        return $this->setData('coverageGeo', is_array($coverageGeo) ? $coverageGeo : (string) $coverageGeo, $locale);
    }

    /**
     * Return the localized chronological coverage.
     * @return string
     */
    public function getLocalizedCoverageChron() {
        return $this->getLocalizedData('coverageChron');
    }

    /**
     * Get chronological coverage.
     * @param string|null $locale
     * @return string
     */
    public function getCoverageChron($locale) {
        $coverageChron = $this->getData('coverageChron', $locale);
        return is_array($coverageChron) ? $coverageChron : (string) $coverageChron;
    }

    /**
     * Set chronological coverage.
     * @param string $coverageChron
     * @param string|null $locale
     */
    public function setCoverageChron($coverageChron, $locale) {
        return $this->setData('coverageChron', is_array($coverageChron) ? $coverageChron : (string) $coverageChron, $locale);
    }

    /**
     * Return the localized sample coverage.
     * @return string
     */
    public function getLocalizedCoverageSample() {
        return $this->getLocalizedData('coverageSample');
    }

    /**
     * Get research sample coverage.
     * @param string|null $locale
     * @return string
     */
    public function getCoverageSample($locale) {
        $coverageSample = $this->getData('coverageSample', $locale);
        return is_array($coverageSample) ? $coverageSample : (string) $coverageSample;
    }

    /**
     * Set sample coverage.
     * @param string $coverageSample
     * @param string|null $locale
     */
    public function setCoverageSample($coverageSample, $locale) {
        return $this->setData('coverageSample', is_array($coverageSample) ? $coverageSample : (string) $coverageSample, $locale);
    }

    /**
     * Return the localized type (method/approach).
     * @return string
     */
    public function getLocalizedType() {
        return $this->getLocalizedData('type');
    }

    /**
     * Get type (method/approach).
     * @param string|null $locale
     * @return string
     */
    public function getType($locale) {
        return $this->getData('type', $locale);
    }

    /**
     * Set type (method/approach).
     * @param string $type
     * @param string|null $locale
     */
    public function setType($type, $locale) {
        return $this->setData('type', is_array($type) ? $type : (string) $type, $locale);
    }

    /**
     * Get rights.
     * @param string|null $locale
     * @return string
     */
    public function getRights($locale) {
        $value = $this->getData('rights', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set rights.
     * @param string $rights
     * @param string|null $locale
     */
    public function setRights($rights, $locale) {
        return $this->setData('rights', is_array($rights) ? $rights : (string) $rights, $locale);
    }

    /**
     * Get source.
     * @param string|null $locale
     * @return string
     */
    public function getSource($locale) {
        $value = $this->getData('source', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set source.
     * @param string $source
     * @param string|null $locale
     */
    public function setSource($source, $locale) {
        return $this->setData('source', is_array($source) ? $source : (string) $source, $locale);
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
     * @param string $language
     */
    public function setLanguage($language) {
        $this->setData('language', (string) $language);
    }

    /**
     * Return the localized sponsor.
     * @return string
     */
    public function getLocalizedSponsor() {
        return $this->getLocalizedData('sponsor');
    }

    /**
     * Get sponsor.
     * @param string|null $locale
     * @return string
     */
    public function getSponsor($locale) {
        $sponsor = $this->getData('sponsor', $locale);
        return is_array($sponsor) ? $sponsor : (string) $sponsor;
    }

    /**
     * Set sponsor.
     * @param string $sponsor
     * @param string|null $locale
     */
    public function setSponsor($sponsor, $locale) {
        return $this->setData('sponsor', is_array($sponsor) ? $sponsor : (string) $sponsor, $locale);
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
     * @param string $citations
     */
    public function setCitations($citations) {
        $this->setData('citations', (string) $citations);
    }

    /**
     * Get the localized cover filename.
     * @return string
     */
    public function getLocalizedFileName() {
        return $this->getLocalizedData('fileName');
    }

    /**
     * Get file name.
     * @param string|null $locale
     * @return string
     */
    public function getFileName($locale) {
        $value = $this->getData('fileName', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set file name.
     * @param string $fileName
     * @param string|null $locale
     */
    public function setFileName($fileName, $locale) {
        return $this->setData('fileName', is_array($fileName) ? $fileName : (string) $fileName, $locale);
    }

    /**
     * Get the localized submission cover width.
     * @return int|null
     */
    public function getLocalizedWidth() {
        $width = $this->getLocalizedData('width');
        return $width !== null ? (int) $width : null;
    }

    /**
     * Get width of cover page image.
     * @param string|null $locale
     * @return int|null
     */
    public function getWidth($locale) {
        $width = $this->getData('width', $locale);
        if (is_array($width)) return $width;
        return $width !== null ? (int) $width : null;
    }

    /**
     * Set width of cover page image.
     * @param int|null $width
     * @param string|null $locale
     */
    public function setWidth($width, $locale) {
        if (is_array($width)) {
            $this->setData('width', $width, $locale);
        } else {
            $this->setData('width', $width !== null ? (int) $width : null, $locale);
        }
    }

    /**
     * Get the localized submission cover height.
     * @return int|null
     */
    public function getLocalizedHeight() {
        $height = $this->getLocalizedData('height');
        return $height !== null ? (int) $height : null;
    }

    /**
     * Get height of cover page image.
     * @param string|null $locale
     * @return int|null
     */
    public function getHeight($locale) {
        $height = $this->getData('height', $locale);
        if (is_array($height)) return $height;
        return $height !== null ? (int) $height : null;
    }

    /**
     * Set height of cover page image.
     * @param int|null $height
     * @param string|null $locale
     */
    public function setHeight($height, $locale) {
        if (is_array($height)) {
            $this->setData('height', $height, $locale);
        } else {
            $this->setData('height', $height !== null ? (int) $height : null, $locale);
        }
    }

    /**
     * Get the localized cover filename on the uploader's computer.
     * @return string
     */
    public function getLocalizedOriginalFileName() {
        return $this->getLocalizedData('originalFileName');
    }

    /**
     * Get original file name.
     * @param string|null $locale
     * @return string
     */
    public function getOriginalFileName($locale) {
        $value = $this->getData('originalFileName', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set original file name.
     * @param string $originalFileName
     * @param string|null $locale
     */
    public function setOriginalFileName($originalFileName, $locale) {
        return $this->setData('originalFileName', is_array($originalFileName) ? $originalFileName : (string) $originalFileName, $locale);
    }

    /**
     * Get the localized cover alternate text.
     * @return string
     */
    public function getLocalizedCoverPageAltText() {
        return $this->getLocalizedData('coverPageAltText');
    }

    /**
     * Get cover page alternate text.
     * @param string|null $locale
     * @return string
     */
    public function getCoverPageAltText($locale) {
        $value = $this->getData('coverPageAltText', $locale);
        return is_array($value) ? $value : (string) $value;
    }

    /**
     * Set cover page alternate text.
     * @param string $coverPageAltText
     * @param string|null $locale
     */
    public function setCoverPageAltText($coverPageAltText, $locale) {
        return $this->setData('coverPageAltText', is_array($coverPageAltText) ? $coverPageAltText : (string) $coverPageAltText, $locale);
    }

    /**
     * Get the flag indicating whether or not to show a cover page.
     * @return int|null
     */
    public function getLocalizedShowCoverPage() {
        $show = $this->getLocalizedData('showCoverPage');
        return $show !== null ? (int) $show : null;
    }

    /**
     * Get show cover page.
     * @param string|null $locale
     * @return int|null
     */
    public function getShowCoverPage($locale) {
        $show = $this->getData('showCoverPage', $locale);
        if (is_array($show)) return $show;
        return $show !== null ? (int) $show : null;
    }

    /**
     * Set show cover page.
     * @param int|null $showCoverPage
     * @param string|null $locale
     */
    public function setShowCoverPage($showCoverPage, $locale) {
        if (is_array($showCoverPage)) {
            $this->setData('showCoverPage', $showCoverPage, $locale);
        } else {
            $this->setData('showCoverPage', $showCoverPage !== null ? (int) $showCoverPage : null, $locale);
        }
    }

    /**
     * Get hide cover page thumbnail in Toc.
     * @param string|null $locale
     * @return int|null
     */
    public function getHideCoverPageToc($locale) {
        $hide = $this->getData('hideCoverPageToc', $locale);
        if (is_array($hide)) return $hide;
        return $hide !== null ? (int) $hide : null;
    }

    /**
     * Set hide cover page thumbnail in Toc.
     * @param int|null $hideCoverPageToc
     * @param string|null $locale
     */
    public function setHideCoverPageToc($hideCoverPageToc, $locale) {
        if (is_array($hideCoverPageToc)) {
            $this->setData('hideCoverPageToc', $hideCoverPageToc, $locale);
        } else {
            $this->setData('hideCoverPageToc', $hideCoverPageToc !== null ? (int) $hideCoverPageToc : null, $locale);
        }
    }

    /**
     * Get hide cover page in abstract view.
     * @param string|null $locale
     * @return int|null
     */
    public function getHideCoverPageAbstract($locale) {
        $hide = $this->getData('hideCoverPageAbstract', $locale);
        if (is_array($hide)) return $hide;
        return $hide !== null ? (int) $hide : null;
    }

    /**
     * Set hide cover page in abstract view.
     * @param int|null $hideCoverPageAbstract
     * @param string|null $locale
     */
    public function setHideCoverPageAbstract($hideCoverPageAbstract, $locale) {
        if (is_array($hideCoverPageAbstract)) {
            $this->setData('hideCoverPageAbstract', $hideCoverPageAbstract, $locale);
        } else {
            $this->setData('hideCoverPageAbstract', $hideCoverPageAbstract !== null ? (int) $hideCoverPageAbstract : null, $locale);
        }
    }

    /**
     * Get localized hide cover page in abstract view.
     * @return int|null
     */
    public function getLocalizedHideCoverPageAbstract() {
        $hide = $this->getLocalizedData('hideCoverPageAbstract');
        return $hide !== null ? (int) $hide : null;
    }

    /**
     * Get submission date.
     * @return string|null
     */
    public function getDateSubmitted() {
        $value = $this->getData('dateSubmitted');
        return $value !== null ? (string) $value : null;
    }

    /**
     * Set submission date.
     * @param string|null $dateSubmitted
     */
    public function setDateSubmitted($dateSubmitted) {
        $this->setData('dateSubmitted', $dateSubmitted !== null ? (string) $dateSubmitted : null);
    }

    /**
     * Get the date of the last status modification.
     * @return string|null
     */
    public function getDateStatusModified() {
        $value = $this->getData('dateStatusModified');
        return $value !== null ? (string) $value : null;
    }

    /**
     * Set the date of the last status modification.
     * @param string|null $dateModified
     */
    public function setDateStatusModified($dateModified) {
        $this->setData('dateStatusModified', $dateModified !== null ? (string) $dateModified : null);
    }

    /**
     * Get the date of the last modification.
     * @return string|null
     */
    public function getLastModified() {
        $value = $this->getData('lastModified');
        return $value !== null ? (string) $value : null;
    }

    /**
     * Set the date of the last modification.
     * @param string|null $dateModified
     */
    public function setLastModified($dateModified) {
        $this->setData('lastModified', $dateModified !== null ? (string) $dateModified : null);
    }

    /**
     * Stamp the date of the last modification to the current time.
     * @return void
     */
    public function stampModified() {
        $this->setLastModified(Core::getCurrentDate());
    }

    /**
     * Stamp the date of the last status modification to the current time.
     * @return void
     */
    public function stampStatusModified() {
        $this->setDateStatusModified(Core::getCurrentDate());
    }

    /**
     * Get submission status.
     * @return int|null
     */
    public function getStatus() {
        $status = $this->getData('status');
        return $status !== null ? (int) $status : null;
    }

    /**
     * Set submission status.
     * @param int|null $status
     */
    public function setStatus($status) {
        $this->setData('status', $status !== null ? (int) $status : null);
    }

    /**
     * Get a map for status constant to locale key.
     * @return array
     */
    public function getStatusMap() {
        static $statusMap;
        if (!isset($statusMap)) {
            $statusMap = [
                STATUS_ARCHIVED => 'submissions.archived',
                STATUS_QUEUED => 'submissions.queued',
                STATUS_PUBLISHED => 'submissions.published',
                STATUS_DECLINED => 'submissions.declined',
                STATUS_QUEUED_UNASSIGNED => 'submissions.queuedUnassigned',
                STATUS_QUEUED_REVIEW => 'submissions.queuedReview',
                STATUS_QUEUED_EDITING => 'submissions.queuedEditing',
                STATUS_INCOMPLETE => 'submissions.incomplete'
            ];
        }
        return $statusMap;
    }

    /**
     * Get a locale key for the paper's current status.
     * @return string|null
     */
    public function getStatusKey() {
        $statusMap = $this->getStatusMap();
        $status = $this->getStatus();
        return $status !== null && isset($statusMap[$status]) ? $statusMap[$status] : null;
    }

    /**
     * Get submission progress (most recently completed submission step).
     * @return int|null
     */
    public function getSubmissionProgress() {
        $progress = $this->getData('submissionProgress');
        return $progress !== null ? (int) $progress : null;
    }

    /**
     * Set submission progress.
     * @param int|null $submissionProgress
     */
    public function setSubmissionProgress($submissionProgress) {
        $this->setData('submissionProgress', $submissionProgress !== null ? (int) $submissionProgress : null);
    }

    /**
     * Get submission file id.
     * @return int|null
     */
    public function getSubmissionFileId() {
        $id = $this->getData('submissionFileId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set submission file id.
     * @param int|null $submissionFileId
     */
    public function setSubmissionFileId($submissionFileId) {
        $this->setData('submissionFileId', $submissionFileId !== null ? (int) $submissionFileId : null);
    }

    /**
     * Get revised file id.
     * @return int|null
     */
    public function getRevisedFileId() {
        $id = $this->getData('revisedFileId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set revised file id.
     * @param int|null $revisedFileId
     */
    public function setRevisedFileId($revisedFileId) {
        $this->setData('revisedFileId', $revisedFileId !== null ? (int) $revisedFileId : null);
    }

    /**
     * Get review file id.
     * @return int|null
     */
    public function getReviewFileId() {
        $id = $this->getData('reviewFileId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set review file id.
     * @param int|null $reviewFileId
     */
    public function setReviewFileId($reviewFileId) {
        $this->setData('reviewFileId', $reviewFileId !== null ? (int) $reviewFileId : null);
    }

    /**
     * Get pages.
     * @return string
     */
    public function getPages() {
        return (string) $this->getData('pages');
    }

    /**
     * Get pages as a nested array of page ranges.
     * For example, pages of "pp. ii-ix, 9,15-18,a2,b2-b6" will return 
     * array( array(0 => 'ii', 1 => 'ix'), array(0 => '9'), array(0 => '15', 1 => '18'), array(0 => 'a2'), array(0 => 'b2', 1 => 'b6') )
     * @return array
     */
    public function getPageArray() {
        $pages = (string) $this->getData('pages');
        
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
            return [];
        }
        
        // commas indicate distinct ranges
        $ranges = explode(',', $pages);
        $pageArray = [];
        foreach ($ranges as $range) {
            // hyphens (or double-hyphens) indicate range spans
            $pageArray[] = array_map('trim', explode('-', str_replace('--', '-', $range), 2));
        }
        
        return $pageArray;
    }

    /**
     * Set pages.
     * @param string $pages
     */
    public function setPages($pages) {
        $this->setData('pages', (string) $pages);
    }

    /**
     * Return submission RT comments status.
     * @return int|null
     */
    public function getCommentsStatus() {
        $status = $this->getData('commentsStatus');
        return $status !== null ? (int) $status : null;
    }

    /**
     * Set submission RT comments status.
     * @param int|null $commentsStatus
     */
    public function setCommentsStatus($commentsStatus) {
        $this->setData('commentsStatus', $commentsStatus !== null ? (int) $commentsStatus : null);
    }
    
}
?>