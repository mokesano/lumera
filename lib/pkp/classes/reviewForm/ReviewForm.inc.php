<?php
declare(strict_types=1);

/**
 * @defgroup reviewForm
 */

/**
 * @file classes/reviewForm/ReviewForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewForm
 * @ingroup reviewForm
 * @see ReviewerFormDAO
 *
 * @brief Basic class describing a review form.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Visibility)
 */

class ReviewForm extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewForm() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get localized title.
     * @return string
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * Get localized description.
     * @return string
     */
    public function getLocalizedDescription() {
        return $this->getLocalizedData('description');
    }

    //
    // Get/set methods
    //

    /**
     * Get the associated type.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set the associated type.
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get the Id of the associated type.
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set the Id of the associated type.
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get sequence of review form.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of review form.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get active flag
     * @return int
     */
    public function getActive() {
        return $this->getData('active');
    }

    /**
     * Set active flag
     * @param $active int
     */
    public function setActive($active) {
        return $this->setData('active', $active);
    }

    /**
     * Get title.
     * @param $locale string
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title.
     * @param $title string
     * @param $locale string
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get description.
     * @param $locale string
     * @return string
     */
    public function getDescription($locale) {
        return $this->getData('description', $locale);
    }

    /**
     * Set description.
     * @param $description string
     * @param $locale string
     */
    public function setDescription($description, $locale) {
        return $this->setData('description', $description, $locale);
    }

    /** DEPRECATED **/

    public function getReviewFormTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }

    public function getReviewFormDescription() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedDescription();
    }

    /**
     * Get the ID of the review form.
     * @return int
     */
    public function getReviewFormId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set the ID of the review form.
     * @param $reviewFormId int
     */
    public function setReviewFormId($reviewFormId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($reviewFormId);
    }
}

?>