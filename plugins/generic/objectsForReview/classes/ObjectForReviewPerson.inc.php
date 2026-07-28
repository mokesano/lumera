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
 */

class ObjectForReviewPerson extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ObjectForReviewPerson() {
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
     * Get person's complete name.
     * Includes first name, middle name, and last name (if applicable).
     * @return string
     */
    public function getFullName() {
        $firstName = (string) $this->getData('firstName');
        $middleName = (string) $this->getData('middleName');
        $lastName = (string) $this->getData('lastName');
        
        $name = $firstName;
        if ($middleName !== '') {
            $name .= ' ' . $middleName;
        }
        if ($lastName !== '') {
            $name .= ' ' . $lastName;
        }
        return trim($name);
    }

    //
    // Get/set methods
    //

    /**
     * Get object for review ID.
     * @return int|null
     */
    public function getObjectId() {
        $id = $this->getData('objectId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set object for review ID.
     * @param int|null $objectId
     */
    public function setObjectId($objectId) {
        $this->setData('objectId', $objectId !== null ? (int) $objectId : null);
    }

    /**
     * Get role.
     * @return string|null
     */
    public function getRole() {
        $role = $this->getData('role');
        return $role !== null ? (string) $role : null;
    }

    /**
     * Set role.
     * @param string|null $role
     */
    public function setRole($role) {
        $this->setData('role', $role !== null ? (string) $role : null);
    }

    /**
     * Get first name.
     * @return string|null
     */
    public function getFirstName() {
        $firstName = $this->getData('firstName');
        return $firstName !== null ? (string) $firstName : null;
    }

    /**
     * Set first name.
     * @param string|null $firstName
     */
    public function setFirstName($firstName) {
        $this->setData('firstName', $firstName !== null ? (string) $firstName : null);
    }

    /**
     * Get middle name.
     * @return string|null
     */
    public function getMiddleName() {
        $middleName = $this->getData('middleName');
        return $middleName !== null ? (string) $middleName : null;
    }

    /**
     * Set middle name.
     * @param string|null $middleName
     */
    public function setMiddleName($middleName) {
        $this->setData('middleName', $middleName !== null ? (string) $middleName : null);
    }

    /**
     * Get last name.
     * @return string|null
     */
    public function getLastName() {
        $lastName = $this->getData('lastName');
        return $lastName !== null ? (string) $lastName : null;
    }

    /**
     * Set last name.
     * @param string|null $lastName
     */
    public function setLastName($lastName) {
        $this->setData('lastName', $lastName !== null ? (string) $lastName : null);
    }

    /**
     * Get sequence of the person in the object's for review person list.
     * @return float|null
     */
    public function getSequence() {
        $sequence = $this->getData('sequence');
        return $sequence !== null ? (float) $sequence : null;
    }

    /**
     * Set sequence of the person in the object's for review person list.
     * @param float|null $sequence
     */
    public function setSequence($sequence) {
        $this->setData('sequence', $sequence !== null ? (float) $sequence : null);
    }

}
?>