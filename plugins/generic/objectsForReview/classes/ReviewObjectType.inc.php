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
 */

class ReviewObjectType extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewObjectType() {
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
     * Get localized type name.
     * @return string|null
     */
    public function getLocalizedName() {
        $name = $this->getLocalizedData('name');
        return $name !== null ? (string) $name : null;
    }

    /**
     * Get localized description.
     * @return string|null
     */
    public function getLocalizedDescription() {
        $description = $this->getLocalizedData('description');
        return $description !== null ? (string) $description : null;
    }

    //
    // Get/set methods
    //

    /**
     * Get context ID.
     * @return int|null
     */
    public function getContextId() {
        $id = $this->getData('contextId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set context ID.
     * @param int|null $contextId
     */
    public function setContextId($contextId) {
        $this->setData('contextId', $contextId !== null ? (int) $contextId : null);
    }

    /**
     * Get active flag.
     * @return int|null
     */
    public function getActive() {
        $active = $this->getData('active');
        return $active !== null ? (int) $active : null;
    }

    /**
     * Set active flag.
     * @param int|null $active
     */
    public function setActive($active) {
        $this->setData('active', $active !== null ? (int) $active : null);
    }

    /**
     * Get key.
     * @return string|null
     */
    public function getKey() {
        $key = $this->getData('key');
        return $key !== null ? (string) $key : null;
    }

    /**
     * Set key.
     * @param string|null $key
     */
    public function setKey($key) {
        $this->setData('key', $key !== null ? (string) $key : null);
    }

    /**
     * Get name.
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale) {
        $name = $this->getData('name', $locale);
        return $name !== null ? (string) $name : null;
    }

    /**
     * Set name.
     * @param string|null $name
     * @param string|null $locale
     */
    public function setName($name, $locale) {
        $this->setData('name', $name !== null ? (string) $name : null, $locale);
    }

    /**
     * Get description.
     * @param string|null $locale
     * @return string|null
     */
    public function getDescription($locale) {
        $description = $this->getData('description', $locale);
        return $description !== null ? (string) $description : null;
    }

    /**
     * Set description.
     * @param string|null $description
     * @param string|null $locale
     */
    public function setDescription($description, $locale) {
        $this->setData('description', $description !== null ? (string) $description : null, $locale);
    }

}
?>