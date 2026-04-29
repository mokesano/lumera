<?php
declare(strict_types=1);

/**
 * @file classes/group/GroupMembershipDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupMembershipDAO
 * @ingroup group
 * @see GroupMembership, Group
 *
 * @brief Operations for retrieving and modifying group membership info.
 */

import ('lib.pkp.classes.group.GroupMembership');

class GroupMembershipDAO extends DAO {
    /** @var UserDAO */
    public $userDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->userDao = DAORegistry::getDAO('UserDAO');
    }

    /**
     * Legacy Constructor Shim.
     */
    public function GroupMembershipDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::GroupMembershipDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve a membership by ID.
     * @param $groupId int
     * @param $userId int
     * @return GroupMembership
     */
    public function getMembership($groupId, $userId) {
        $result = $this->retrieve(
            'SELECT * FROM group_memberships WHERE group_id = ? AND user_id = ?',
            array((int) $groupId, (int) $userId)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnMembershipFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        unset($result);
        return $returner;
    }

    /**
     * Retrieve memberships by group ID.
     * @param $groupId int
     * @param $rangeInfo object
     * @return DAOResultFactory
     */
    public function getMemberships($groupId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM group_memberships m, users u WHERE group_id = ? AND u.user_id = m.user_id ORDER BY m.seq',
            (int) $groupId,
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnMembershipFromRow');
        return $returner;
    }

    /**
     * Instantiate a new data object.
     * @return GroupMembership
     */
    public function newDataObject() {
        return new GroupMembership();
    }

    /**
     * Internal function to return a GroupMembership object from a row.
     * @param $row array
     * @return GroupMembership
     */
    public function _returnMembershipFromRow($row) {
        // Keep a cache of users.
        static $users;
        if (!isset($users)) {
            $users = array();
        }
        $userId = $row['user_id'];
        if (!isset($users[$userId])) {
            $users[$userId] = $this->userDao->getById($userId);
        }

        $membership = $this->newDataObject();
        $membership->setGroupId($row['group_id']);
        $membership->setUserId($row['user_id']);
        $membership->setUser($users[$userId]);
        $membership->setSequence($row['seq']);
        $membership->setAboutDisplayed($row['about_displayed']);

        // MODERN HOOK: Using dispatch() and NO references for objects
        HookRegistry::dispatch('GroupMembershipDAO::_returnMemberFromRow', array($membership, $row));

        return $membership;
    }

    /**
     * Insert a new group membership.
     * @param $membership GroupMembership
     */
    public function insertMembership($membership) {
        $this->update(
            'INSERT INTO group_memberships
                (group_id, user_id, seq, about_displayed)
                VALUES
                (?, ?, ?, ?)',
            array(
                (int) $membership->getGroupId(),
                (int) $membership->getUserId(),
                $membership->getSequence() == null ? 0 : (float) $membership->getSequence(),
                (int) $membership->getAboutDisplayed()
            )
        );
    }

    /**
     * Update an existing group membership.
     * @param $membership GroupMembership
     */
    public function updateObject($membership) {
        return $this->update(
            'UPDATE group_memberships
                SET
                    seq = ?,
                    about_displayed = ?
                WHERE
                    group_id = ? AND
                    user_id = ?',
            array(
                (float) $membership->getSequence(),
                (int) $membership->getAboutDisplayed(),
                (int) $membership->getGroupId(),
                (int) $membership->getUserId()
            )
        );
    }

    public function updateMembership($membership) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($membership);
    }

    /**
     * Delete a membership
     * @param $membership GroupMembership
     */
    public function deleteObject($membership) {
        return $this->deleteMembershipById($membership->getGroupId(), $membership->getUserId());
    }

    public function deleteMembership($membership) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->deleteObject($membership);
    }

    /**
     * Delete a membership
     * @param $groupId int
     * @param $userId int
     */
    public function deleteMembershipById($groupId, $userId) {
        return $this->update(
            'DELETE FROM group_memberships WHERE group_id = ? AND user_id = ?',
            array((int) $groupId, (int) $userId)
        );
    }

    /**
     * Delete group membership by group ID
     * @param $groupId int
     */
    public function deleteMembershipByGroupId($groupId) {
        return $this->update(
            'DELETE FROM group_memberships WHERE group_id = ?',
            (int) $groupId
        );
    }

    /**
     * Delete group membership by user ID
     * @param $userId int
     */
    public function deleteMembershipByUserId($userId) {
        return $this->update(
            'DELETE FROM group_memberships WHERE user_id = ?',
            (int) $userId
        );
    }

    /**
     * Sequentially renumber group members in their sequence order.
     * @param $groupId int
     */
    public function resequenceMemberships($groupId) {
        $result = $this->retrieve(
            'SELECT user_id, group_id FROM group_memberships WHERE group_id = ? ORDER BY seq',
            (int) $groupId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($userId, $groupId) = $result->fields;
            $this->update(
                'UPDATE group_memberships SET seq = ? WHERE user_id = ? AND group_id = ?',
                array(
                    $i,
                    (int) $userId,
                    (int) $groupId
                )
            );

            $result->MoveNext();
        }

        $result->Close();
        unset($result);
    }
}

?>