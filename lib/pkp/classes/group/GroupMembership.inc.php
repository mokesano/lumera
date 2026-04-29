<?php
declare(strict_types=1);

/**
 * @file classes/group/GroupMembership.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupMembership
 * @ingroup group
 * @see GroupMembershipDAO, Group
 *
 * @brief Describes memberships for editorial board positions.
 */

class GroupMembership extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Legacy Constructor Shim.
     */
    public function GroupMembership() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::GroupMembership(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }


    //
    // Get/set methods
    //
    /**
     * Get ID of board position.
     * @return int
     */
    public function getGroupId() {
        return $this->getData('groupId');
    }

    /**
     * Set ID of board position.
     * @param $groupId int
     */
    public function setGroupId($groupId) {
        return $this->setData('groupId', $groupId);
    }

    /**
     * Get user ID of membership.
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set user ID of membership.
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }

    /**
     * Get user for this membership.
     * @return User
     */
    public function getUser() {
        return $this->getData('user');
    }

    /**
     * Set user for this membership.
     * @param $user User
     */
    public function setUser($user) {
        return $this->setData('user', $user);
    }

    /**
     * Get sequence of membership.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of membership.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get flag indicating whether or not the membership is displayed in "About"
     * @return boolean
     */
    public function getAboutDisplayed() {
        return $this->getData('aboutDisplayed');
    }

    /**
     * Set flag indicating whether or not the membership is displayed in "About"
     * @param $aboutDisplayed boolean
     */
    public function setAboutDisplayed($aboutDisplayed) {
        return $this->setData('aboutDisplayed',$aboutDisplayed);
    }
}

?>