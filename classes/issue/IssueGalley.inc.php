<?php
declare(strict_types=1);

/**
 * @file classes/issue/IssueGalley.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueGalley
 * @ingroup issue
 * @see IssueGalleyDAO
 *
 * @brief A galley is a final presentation version of the full-text of an issue.
 */

import('classes.issue.IssueFile');

class IssueGalley extends IssueFile {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Legacy Constructor Shim.
     */
    public function IssueGalley() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::IssueGalley(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Check if galley is a PDF galley.
     * @return boolean
     */
    public function isPdfGalley() {
        switch ($this->getFileType()) {
            case 'application/pdf':
            case 'application/x-pdf':
            case 'text/pdf':
            case 'text/x-pdf':
                return true;
            default: return false;
        }
    }

    //
    // Get/set methods
    //

    /**
     * Get views count.
     * @return int
     */
    public function getViews() {
        $application = PKPApplication::getApplication();
        return $application->getPrimaryMetricByAssoc(ASSOC_TYPE_ISSUE_GALLEY, (int) $this->getId());
    }

    /**
     * Get the localized value of the galley label.
     * @return string
     */
    public function getGalleyLabel() {
        $label = $this->getLabel();
        if ($this->getLocale() != AppLocale::getLocale()) {
            $locales = AppLocale::getAllLocales();
            $label .= ' (' . $locales[$this->getLocale()] . ')';
        }
        return $label;
    }

    /**
     * Get label/title.
     * @return string
     */
    public function getLabel() {
        return $this->getData('label');
    }

    /**
     * Set label/title.
     * @param $label string
     */
    public function setLabel($label) {
        return $this->setData('label', $label);
    }

    /**
     * Get locale.
     * @return string
     */
    public function getLocale() {
        return $this->getData('locale');
    }

    /**
     * Set locale.
     * @param $locale string
     */
    public function setLocale($locale) {
        return $this->setData('locale', $locale);
    }

    /**
     * Get sequence order.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence order.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get file ID.
     * @return int
     */
    public function getFileId() {
        return $this->getData('fileId');
    }

    /**
     * Set file ID.
     * @param $fileId
     */
    public function setFileId($fileId) {
        return $this->setData('fileId', $fileId);
    }

    /**
     * Get a public ID for this galley.
     * @param $pubIdType string One of the NLM pub-id-type values
     * @param $preview boolean If true, generate a non-persisted preview only.
     */
    public function getPubId($pubIdType, $preview = false) {
        // If we already have an assigned ID, use it.
        $storedId = $this->getStoredPubId($pubIdType);

        // Ensure that blanks are treated as nulls.
        if ($storedId === '') {
            $storedId = null;
        }

        return $storedId;
    }

    /**
     * Get stored public ID of the galley.
     * @param $pubIdType string
     * @return string
     */
    public function getStoredPubId($pubIdType) {
        return $this->getData('pub-id::'.$pubIdType);
    }

    /**
     * Set stored public galley id.
     * @param $pubIdType string
     * @param $pubId string
     */
    public function setStoredPubId($pubIdType, $pubId) {
        return $this->setData('pub-id::'.$pubIdType, $pubId);
    }

    /**
     * Return the "best" article ID -- If a public article ID is set,
     * use it; otherwise use the internal article Id.
     * @param $journal Object the journal this galley is in
     * @return string
     */
    public function getBestGalleyId($journal) {
        if ($journal->getSetting('enablePublicGalleyId')) {
            $publicGalleyId = $this->getPubId('publisher-id');
            if (!empty($publicGalleyId)) return $publicGalleyId;
        }
        return $this->getId();
    }
}

?>