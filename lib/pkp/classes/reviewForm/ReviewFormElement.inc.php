<?php
declare(strict_types=1);

/**
 * @file classes/reviewForm/ReviewFormElement.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElement
 * @ingroup reviewForm
 * @see ReviewFormElementDAO
 *
 * @brief Basic class describing a review form element.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility)
 */

define('REVIEW_FORM_ELEMENT_TYPE_SMALL_TEXT_FIELD',    0x000001);
define('REVIEW_FORM_ELEMENT_TYPE_TEXT_FIELD',        0x000002);
define('REVIEW_FORM_ELEMENT_TYPE_TEXTAREA',        0x000003);
define('REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES',        0x000004);
define('REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS',    0x000005);
define('REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX',    0x000006);

class ReviewFormElement extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewFormElement() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewFormElement(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get localized question.
     * @return string
     */
    public function getLocalizedQuestion() {
        return $this->getLocalizedData('question');
    }

    /**
     * Get localized list of possible responses.
     * @return array
     */
    public function getLocalizedPossibleResponses() {
        return $this->getLocalizedData('possibleResponses');
    }

    //
    // Get/set methods
    //

    /**
     * Get the review form ID of the review form element.
     * @return int
     */
    public function getReviewFormId() {
        return $this->getData('reviewFormId');
    }

    /**
     * Set the review form ID of the review form element.
     * @param $reviewFormId int
     */
    public function setReviewFormId($reviewFormId) {
        return $this->setData('reviewFormId', $reviewFormId);
    }

    /**
     * Get sequence of review form element.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of review form element.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get the type of the review form element.
     * @return string
     */
    public function getElementType() {
        return $this->getData('reviewFormElementType');
    }

    /**
     * Set the type of the review form element.
     * @param $reviewFormElementType string
     */
    public function setElementType($reviewFormElementType) {
        return $this->setData('reviewFormElementType', $reviewFormElementType);
    }

    /**
     * Get required flag
     * @return boolean
     */
    public function getRequired() {
        return $this->getData('required');
    }

    /**
     * Set required flag
     * @param $viewable boolean
     */
    public function setRequired($required) {
        return $this->setData('required', $required);
    }

    /**
     * get included
     * @return boolean
     */
    public function getIncluded() {
        return $this->getData('included');
    }

    /**
     * set included
     * @param $included boolean
     */
    public function setIncluded($included) {
        return $this->setData('included', $included);
    }

    /**
     * Get question.
     * @param $locale string
     * @return string
     */
    public function getQuestion($locale) {
        return $this->getData('question', $locale);
    }

    /**
     * Set question.
     * @param $question string
     * @param $locale string
     */
    public function setQuestion($question, $locale) {
        return $this->setData('question', $question, $locale);
    }

    /**
     * Get possible response.
     * @param $locale string
     * @return string
     */
    public function getPossibleResponses($locale) {
        return $this->getData('possibleResponses', $locale);
    }

    /**
     * Set possibleResponse.
     * @param $possibleResponse string
     * @param $locale string
     */
    public function setPossibleResponses($possibleResponses, $locale) {
        return $this->setData('possibleResponses', $possibleResponses, $locale);
    }

    /**
     * Get an associative array matching review form element type codes with locale strings.
     * (Includes default '' => "Choose One" string.)
     * @return array reviewFormElementType => localeString
     */
    public function getReviewFormElementTypeOptions() {
        // WIZDAM FIX: Removed reference return and static declaration for simpler array return
        return array(
            '' => 'manager.reviewFormElements.chooseType',
            REVIEW_FORM_ELEMENT_TYPE_SMALL_TEXT_FIELD => 'manager.reviewFormElements.smalltextfield',
            REVIEW_FORM_ELEMENT_TYPE_TEXT_FIELD => 'manager.reviewFormElements.textfield',
            REVIEW_FORM_ELEMENT_TYPE_TEXTAREA => 'manager.reviewFormElements.textarea',
            REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES => 'manager.reviewFormElements.checkboxes',
            REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS => 'manager.reviewFormElements.radiobuttons',
            REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX => 'manager.reviewFormElements.dropdownbox'
        );
    }

    /**
     * Get an array of all multiple responses element types.
     * @return array reviewFormElementTypes
     */
    public function getMultipleResponsesElementTypes() {
        // WIZDAM FIX: Removed reference return
        return array(REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES, REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS, REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX);
    }

    /** DEPRECATED **/

    /**
     * Get localized question.
     * @return string
     */
    public function getReviewFormElementQuestion() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedQuestion();
    }

    /**
     * Get localized list of possible responses.
     * @return array
     */
    public function getReviewFormElementPossibleResponses() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedPossibleResponses();
    }

    /**
     * Get the ID of the review form element.
     * @return int
     */
    public function getReviewFormElementId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set the ID of the review form element.
     * @param $reviewFormElementId int
     */
    public function setReviewFormElementId($reviewFormElementId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($reviewFormElementId);
    }
}

?>