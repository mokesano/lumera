<?php
declare(strict_types=1);

/**
 * @file classes/security/UserGroup.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroup
 * @ingroup security
 * @see UserGroupDAO
 *
 * @brief Describes user groups
 */

// Bring in role constants.
import('classes.security.Role');

class UserGroup extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserGroup() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UserGroup(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get role ID.
     * @return int
     */
    public function getRoleId() {
        return $this->getData('roleId');
    }

    /**
     * Set role ID.
     * @param $roleId int
     */
    public function setRoleId($roleId) {
        $this->setData('roleId', $roleId);
    }

    /**
     * Get path.
     * @return string
     */
    public function getPath() {
        return $this->getData('path');
    }

    /**
     * Set path.
     * @param $path string
     */
    public function setPath($path) {
        $this->setData('path', $path);
    }

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
        $this->setData('contextId', $contextId);
    }

    /**
     * Get default flag.
     * @return boolean
     */
    public function getDefault() {
        return $this->getData('isDefault');
    }

    /**
     * Set default flag.
     * @param $isDefault boolean
     */
    public function setDefault($isDefault) {
        $this->setData('isDefault', $isDefault);
    }

    /**
     * Get localized name.
     * @return string
     */
    public function getLocalizedName() {
        return $this->getLocalizedData('name');
    }

    /**
     * Get user group name
     * @param $locale string
     * @return string
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set user group name
     * @param $name string
     * @param $locale string
     */
    public function setName($name, $locale) {
        return $this->setData('name', $name, $locale);
    }

    /**
     * Get localized abbreviation.
     * @return string
     */
    public function getLocalizedAbbrev() {
        return $this->getLocalizedData('abbrev');
    }

    /**
     * Get user group abbrev
     * @param $locale string
     * @return string
     */
    public function getAbbrev($locale) {
        return $this->getData('abbrev', $locale);
    }

    /**
     * Set user group abbrev
     * @param $abbrev string
     * @param $locale string
     */
    public function setAbbrev($abbrev, $locale) {
        return $this->setData('abbrev', $abbrev, $locale);
    }
}

?>