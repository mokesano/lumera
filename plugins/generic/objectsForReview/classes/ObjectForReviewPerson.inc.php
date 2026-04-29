<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewPerson.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewPerson
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReviewPersonDAO
 *
 * @brief Object for review person metadata class.
 * * MODERNIZED FOR WIZDAM FORK
 */

class ObjectForReviewPerson extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectForReviewPerson() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectForReviewPerson(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get person's complete name.
     * Includes first name, middle name, and last name (if applicable).
     * @return string
     */
    public function getFullName() {
        return $this->getData('firstName') . ' ' . ($this->getData('middleName') != '' ? $this->getData('middleName') . ' ' : '') . $this->getData('lastName');
    }

    //
    // Get/set methods
    //
    /**
     * Get object for review ID.
     * @return int
     */
    public function getObjectId() {
        return $this->getData('objectId');
    }

    /**
     * Set object for review ID.
     * @param $objectId int
     */
    public function setObjectId($objectId) {
        return $this->setData('objectId', $objectId);
    }

    /**
     * Get role.
     * @return string
     */
    public function getRole() {
        return $this->getData('role');
    }

    /**
     * Set role.
     * @param $role int
     */
    public function setRole($role)    {
        return $this->setData('role', $role);
    }

    /**
     * Get first name.
     * @return string
     */
    public function getFirstName() {
        return $this->getData('firstName');
    }

    /**
     * Set first name.
     * @param $firstName string
     */
    public function setFirstName($firstName) {
        return $this->setData('firstName', $firstName);
    }

    /**
     * Get middle name.
     * @return string
     */
    public function getMiddleName() {
        return $this->getData('middleName');
    }

    /**
     * Set middle name.
     * @param $middleName string
     */
    public function setMiddleName($middleName) {
        return $this->setData('middleName', $middleName);
    }

    /**
     * Get last name.
     * @return string
     */
    public function getLastName() {
        return $this->getData('lastName');
    }

    /**
     * Set last name.
     * @param $lastName string
     */
    public function setLastName($lastName) {
        return $this->setData('lastName', $lastName);
    }

    /**
     * Get sequence of the person in the object's for reivew person list.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of the person in the object's for review person list.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

}

?>