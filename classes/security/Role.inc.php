<?php
declare(strict_types=1);

/**
 * @file classes/security/Role.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Role
 * @ingroup security
 * @see RoleDAO
 *
 * @brief Describes user roles within the system and the associated permissions.
 */

/** ID codes for all user roles */
define('ROLE_ID_SITE_ADMIN',          0x00000001);
define('ROLE_ID_JOURNAL_MANAGER',     0x00000010);
define('ROLE_ID_EDITOR',              0x00000100);
define('ROLE_ID_SECTION_EDITOR',      0x00000200);
define('ROLE_ID_LAYOUT_EDITOR',       0x00000300);
define('ROLE_ID_REVIEWER',            0x00001000);
define('ROLE_ID_COPYEDITOR',          0x00002000);
define('ROLE_ID_PROOFREADER',         0x00003000);
define('ROLE_ID_AUTHOR',              0x00010000);
define('ROLE_ID_READER',              0x00100000);
define('ROLE_ID_SUBSCRIPTION_MANAGER', 0x00200000);

class Role extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function Role() {
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
     * Get the i18n key name associated with this role.
     * @return string
     */
    public function getRoleName() {
        return RoleDAO::getRoleName($this->getData('roleId'));
    }

    /**
     * Get the URL path associated with this role's operations.
     * @return string
     */
    public function getRolePath() {
        return RoleDAO::getRolePath($this->getData('roleId'));
    }

    //
    // Get/set methods
    //

    /**
     * Get journal ID associated with role.
     * @return int|null
     */
    public function getJournalId() {
        $id = $this->getData('journalId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set journal ID associated with role.
     * @param int|null $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journalId', $journalId !== null ? (int) $journalId : null);
    }

    /**
     * Get user ID associated with role.
     * @return int|null
     */
    public function getUserId() {
        $id = $this->getData('userId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set user ID associated with role.
     * @param int|null $userId
     */
    public function setUserId($userId) {
        $this->setData('userId', $userId !== null ? (int) $userId : null);
    }

    /**
     * Get role ID of this role.
     * @return int|null
     */
    public function getRoleId() {
        $id = $this->getData('roleId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set role ID of this role.
     * @param int|null $roleId
     */
    public function setRoleId($roleId) {
        $this->setData('roleId', $roleId !== null ? (int) $roleId : null);
    }
    
}
?>