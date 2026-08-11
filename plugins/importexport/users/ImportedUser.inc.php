<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/users/ImportedUser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImportedUser
 * @ingroup plugins_importexport_users
 *
 * @brief Class to import and export user data from an XML format.
 * See dbscripts/xml/dtd/users.dtd for the XML schema used.
 */

/**
 * Helper class representing a user imported from a user data file.
 */
import('classes.user.User');

class ImportedUser extends User {

    /** @var array Roles of this user */
    public array $roles = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->roles = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ImportedUser() {
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
     * Set the unencrypted form of the user's password.
     * @param string $unencryptedPassword
     * @return void
     */
    public function setUnencryptedPassword(string $unencryptedPassword): void {
        $this->setData('unencryptedPassword', $unencryptedPassword);
    }

    /**
     * Get the user's unencrypted password.
     * @return string|null
     */
    public function getUnencryptedPassword(): ?string {
        return $this->getData('unencryptedPassword');
    }

    /**
     * Add a new role to this user.
     * @param Role $role
     * @return void
     */
    public function addRole(Role $role): void {
        $this->roles[] = $role;
    }

    /**
     * Get this user's roles.
     * @return array Roles
     */
    public function getRoles(): array {
        return $this->roles;
    }

    /**
     * Set the interests to be inserted after we have a user ID
     * @param string $interests
     * @return void
     */
    public function setTemporaryInterests(string $interests): void {
        $this->setData('interests', $interests);
    }

    /**
     * Get the interests to be inserted after we have a user ID
     * @return string|null
     */
    public function getTemporaryInterests(): ?string {
        return $this->getData('interests');
    }
    
}
?>