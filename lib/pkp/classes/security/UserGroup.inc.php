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
 * @brief Describes user groups.
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
        $this->__construct();
    }

    /**
     * Get role ID.
     * @return int
     */
    public function getRoleId() {
        $data = $this->getData('roleId');
        return $data !== null ? (int) $data : 0;
    }

    /**
     * Set role ID.
     * @param mixed $roleId
     */
    public function setRoleId($roleId) {
        $this->setData('roleId', $roleId !== null ? (int) $roleId : 0);
    }

    /**
     * Get path.
     * @return string
     */
    public function getPath() {
        $data = $this->getData('path');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set path.
     * @param mixed $path
     */
    public function setPath($path) {
        $this->setData('path', $path !== null ? (string) $path : '');
    }

    /**
     * Get context ID.
     * @return int
     */
    public function getContextId() {
        $data = $this->getData('contextId');
        return $data !== null ? (int) $data : 0;
    }

    /**
     * Set context ID.
     * @param mixed $contextId
     */
    public function setContextId($contextId) {
        $this->setData('contextId', $contextId !== null ? (int) $contextId : 0);
    }

    /**
     * Get default flag.
     * @return boolean
     */
    public function getDefault() {
        $data = $this->getData('isDefault');
        return (bool) $data;
    }

    /**
     * Set default flag.
     * @param mixed $isDefault
     */
    public function setDefault($isDefault) {
        $this->setData('isDefault', (bool) $isDefault);
    }

    /**
     * Get localized name.
     * @return string
     */
    public function getLocalizedName() {
        $data = $this->getLocalizedData('name');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Get user group name
     * @param mixed $locale
     * @return string
     */
    public function getName($locale) {
        $data = $this->getData('name', $locale);
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set user group name
     * @param mixed $name
     * @param mixed $locale
     */
    public function setName($name, $locale) {
        $this->setData('name', $name !== null ? (string) $name : '', $locale);
    }

    /**
     * Get localized abbreviation.
     * @return string
     */
    public function getLocalizedAbbrev() {
        $data = $this->getLocalizedData('abbrev');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Get user group abbrev
     * @param mixed $locale
     * @return string
     */
    public function getAbbrev($locale) {
        $data = $this->getData('abbrev', $locale);
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set user group abbrev
     * @param mixed $abbrev
     * @param mixed $locale
     */
    public function setAbbrev($abbrev, $locale) {
        $this->setData('abbrev', $abbrev !== null ? (string) $abbrev : '', $locale);
    }
    
}
?>