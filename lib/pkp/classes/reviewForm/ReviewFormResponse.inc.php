<?php
declare(strict_types=1);

/**
 * @file classes/reviewForm/ReviewFormResponse.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormResponse
 * @ingroup reviewForm
 * @see ReviewFormResponseDAO
 *
 * @brief Basic class describing a review form response.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Visibility)
 */

class ReviewFormResponse extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewFormResponse() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewFormResponse(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the review ID.
     * @return int
     */
    public function getReviewId() {
        return $this->getData('reviewId');
    }

    /**
     * Set the review ID.
     * @param $reviewId int
     */
    public function setReviewId($reviewId) {
        return $this->setData('reviewId', $reviewId);
    }

    /**
     * Get ID of review form element.
     * @return int
     */
    public function getReviewFormElementId() {
        return $this->getData('reviewFormElementId');
    }

    /**
     * Set ID of review form element.
     * @param $reviewFormElementId int
     */
    public function setReviewFormElementId($reviewFormElementId) {
        return $this->setData('reviewFormElementId', $reviewFormElementId);
    }

    /**
     * Get response value.
     * @return int
     */
    public function getValue() {
        return $this->getData('value');
    }

    /**
     * Set response value.
     * @param $value int
     */
    public function setValue($value) {
        return $this->setData('value', $value);
    }

    /**
     * Get response type.
     * @return string
     */
    public function getResponseType() {
        return $this->getData('type');
    }

    /**
     * Set response type.
     * @param $type string
     */
    public function setResponseType($type) {
        return $this->setData('type', $type);
    }
}

?>