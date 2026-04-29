<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ReviewObjectType.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectType
 * @ingroup plugins_generic_objectsForReview
 * @see ReviewObjectTypeDAO
 *
 * @brief Basic class describing a review object type.
 * * MODERNIZED FOR WIZDAM FORK
 */

class ReviewObjectType extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewObjectType() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewObjectType(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get localized type name.
     * @return string
     */
    public function getLocalizedName() {
        return $this->getLocalizedData('name');
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
     * Get context ID.
     * @return int
     */
    public function getContextId() {
        return $this->getData('contextId');
    }

    /**
     * Set context ID.
     * @param $contextId int
     */
    public function setContextId($contextId) {
        return $this->setData('contextId', $contextId);
    }

    /**
     * Get active flag.
     * @return int
     */
    public function getActive() {
        return $this->getData('active');
    }

    /**
     * Set active flag.
     * @param $active int
     */
    public function setActive($active) {
        return $this->setData('active', $active);
    }

    /**
     * Get key.
     * @return string
     */
    public function getKey() {
        return $this->getData('key');
    }

    /**
     * Set key.
     * @param $key string
     */
    public function setKey($key) {
        return $this->setData('key', $key);
    }

    /**
     * Get name.
     * @param $locale string
     * @return string
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set name.
     * @param $name string
     * @param $locale string
     */
    public function setName($name, $locale) {
        return $this->setData('name', $name, $locale);
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

}

?>