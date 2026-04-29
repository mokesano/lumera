<?php
declare(strict_types=1);

/**
 * @defgroup rt
 */

/**
 * @file classes/rt/RT.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RT
 * @ingroup rt
 * @see RTDAO
 *
 * @brief Class to process and respond to Reading Tools requests.
 * * REFACTORED: Wizdam Edition (PHP 8 Compatibility, Visibility, Annotations)
 */

import('lib.pkp.classes.rt.RTStruct');

class RT {

    /** @var RTVersion|null The current RT version object */
    public $version;

    /** @var bool Toggle to enable/disable Reading Tools */
    public $enabled;

    /** @var bool Toggle to display abstract */
    public $abstract;

    /** @var bool Toggle to display review policy */
    public $viewReviewPolicy;

    /** @var bool Toggle to allow citation capture */
    public $captureCite;

    /** @var bool Toggle to display item metadata */
    public $viewMetadata;

    /** @var bool Toggle to display supplementary files */
    public $supplementaryFiles;

    /** @var bool Toggle for printer-friendly version */
    public $printerFriendly;

    /** @var bool Toggle to display author biography */
    public $authorBio;

    /** @var bool Toggle to display term definitions */
    public $defineTerms;

    /** @var bool Toggle to allow emailing the author */
    public $emailAuthor;

    /** @var bool Toggle to allow emailing others */
    public $emailOthers;

    /** @var bool Toggle to enable finding references */
    public $findingReferences;

    /**
     * Constructor.
     * Initializes the object with default values.
     */
    public function __construct() {
        $this->version = null;
        $this->enabled = false;
        $this->abstract = false;
        $this->viewReviewPolicy = false;
        $this->captureCite = false;
        $this->viewMetadata = false;
        $this->supplementaryFiles = false;
        $this->printerFriendly = false;
        $this->authorBio = false;
        $this->defineTerms = false;
        $this->emailAuthor = false;
        $this->emailOthers = false;
        $this->findingReferences = false;
    }

    //
    // Getter/Setter functions
    //

    /**
     * Set the enabled status of Reading Tools.
     * @param bool $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    /**
     * Get the enabled status of Reading Tools.
     * @return bool
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * Set the current RT Version.
     * @param RTVersion $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * Get the current RT Version.
     * @return RTVersion|null
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Set the Capture Citation flag.
     * @param bool $captureCite
     */
    public function setCaptureCite($captureCite) {
        $this->captureCite = $captureCite;
    }

    /**
     * Get the Capture Citation flag.
     * @return bool
     */
    public function getCaptureCite() {
        return $this->captureCite;
    }

    /**
     * Set the View Abstract flag.
     * @param bool $abstract
     */
    public function setAbstract($abstract) {
        $this->abstract = $abstract;
    }

    /**
     * Get the View Abstract flag.
     * @return bool
     */
    public function getAbstract() {
        return $this->abstract;
    }

    /**
     * Set the View Review Policy flag.
     * @param bool $viewReviewPolicy
     */
    public function setViewReviewPolicy($viewReviewPolicy) {
        $this->viewReviewPolicy = $viewReviewPolicy;
    }

    /**
     * Get the View Review Policy flag.
     * @return bool
     */
    public function getViewReviewPolicy() {
        return $this->viewReviewPolicy;
    }

    /**
     * Set the View Metadata flag.
     * @param bool $viewMetadata
     */
    public function setViewMetadata($viewMetadata) {
        $this->viewMetadata = $viewMetadata;
    }

    /**
     * Get the View Metadata flag.
     * @return bool
     */
    public function getViewMetadata() {
        return $this->viewMetadata;
    }

    /**
     * Set the Supplementary Files flag.
     * @param bool $supplementaryFiles
     */
    public function setSupplementaryFiles($supplementaryFiles) {
        $this->supplementaryFiles = $supplementaryFiles;
    }

    /**
     * Get the Supplementary Files flag.
     * @return bool
     */
    public function getSupplementaryFiles() {
        return $this->supplementaryFiles;
    }

    /**
     * Set the Printer Friendly flag.
     * @param bool $printerFriendly
     */
    public function setPrinterFriendly($printerFriendly) {
        $this->printerFriendly = $printerFriendly;
    }

    /**
     * Get the Printer Friendly flag.
     * @return bool
     */
    public function getPrinterFriendly() {
        return $this->printerFriendly;
    }

    /**
     * Set the Author Bio flag.
     * @deprecated
     * @param bool $authorBio
     */
    public function setAuthorBio($authorBio) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        $this->authorBio = $authorBio;
    }

    /**
     * Get the Author Bio flag.
     * @deprecated
     * @return bool
     */
    public function getAuthorBio() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->authorBio;
    }

    /**
     * Set the Define Terms flag.
     * @param bool $defineTerms
     */
    public function setDefineTerms($defineTerms) {
        $this->defineTerms = $defineTerms;
    }

    /**
     * Get the Define Terms flag.
     * @return bool
     */
    public function getDefineTerms() {
        return $this->defineTerms;
    }

    /**
     * Set the Email Author flag.
     * @param bool $emailAuthor
     */
    public function setEmailAuthor($emailAuthor) {
        $this->emailAuthor = $emailAuthor;
    }

    /**
     * Get the Email Author flag.
     * @return bool
     */
    public function getEmailAuthor() {
        return $this->emailAuthor;
    }

    /**
     * Set the Email Others flag.
     * @param bool $emailOthers
     */
    public function setEmailOthers($emailOthers) {
        $this->emailOthers = $emailOthers;
    }

    /**
     * Get the Email Others flag.
     * @return bool
     */
    public function getEmailOthers() {
        return $this->emailOthers;
    }

    /**
     * Set the Finding References flag.
     * @param bool $findingReferences
     */
    public function setFindingReferences($findingReferences) {
        $this->findingReferences = $findingReferences;
    }

    /**
     * Get the Finding References flag.
     * @return bool
     */
    public function getFindingReferences() {
        return $this->findingReferences;
    }
}

?>