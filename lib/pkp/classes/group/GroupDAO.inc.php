<?php
declare(strict_types=1);

/**
 * @file classes/group/GroupDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupDAO
 * @ingroup group
 * @see Group
 *
 * @brief Operations for retrieving and modifying Group objects.
 */

import ('lib.pkp.classes.group.Group');

class GroupDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GroupDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::GroupDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve a group by ID.
     * @param $groupId int
     * @param $assocType int optional
     * @param $assocId int optional
     * @return Group
     */
    public function getById($groupId, $assocType = null, $assocId = null) {
        $params = array((int) $groupId);
        if ($assocType !== null) {
            $params[] = (int) $assocType;
            $params[] = (int) $assocId;
        }
        $result = $this->retrieve(
            'SELECT * FROM groups WHERE group_id = ?' . ($assocType !== null?' AND assoc_type = ? AND assoc_id = ?':''), $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnGroupFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        unset($result);
        return $returner;
    }

    /**
     * Retrieve a group by ID.
     * @param $groupId int
     * @param $assocType int optional
     * @param $assocId int optional
     * @return Group
     */
    public function getGroup($groupId, $assocType = null, $assocId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getById($groupId, $assocType, $assocId);
    }

    /**
     * Get all groups for a given context.
     * @param $assocType int
     * @param $assocId int
     * @param $context int (optional)
     * @param $rangeInfo object RangeInfo object (optional)
     * @return DAOResultFactory
     */
    public function getGroups($assocType, $assocId, $context = null, $rangeInfo = null) {
        $params = array((int) $assocType, (int) $assocId);
        if ($context !== null) $params[] = (int) $context;

        $result = $this->retrieveRange(
            'SELECT * FROM groups WHERE assoc_type = ? AND assoc_id = ?' . ($context!==null?' AND context = ?':'') . ' ORDER BY context, seq',
            $params, $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGroupFromRow', array('id'));
        return $returner;
    }

    /**
     * Get the list of fields for which locale data is stored.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), array('title'));
    }

    /**
     * Instantiate a new DataObject.
     * @return Group
     */
    public function newDataObject() {
        return new Group();
    }

    /**
     * Internal function to return a Group object from a row.
     * @param $row array
     * @return Group
     */
    public function _returnGroupFromRow($row) {
        $group = $this->newDataObject();
        $group->setId($row['group_id']);
        $group->setAboutDisplayed($row['about_displayed']);
        $group->setPublishEmail($row['publish_email']);
        $group->setSequence($row['seq']);
        $group->setContext($row['context']);
        $group->setAssocType($row['assoc_type']);
        $group->setAssocId($row['assoc_id']);
        $this->getDataObjectSettings('group_settings', 'group_id', $row['group_id'], $group);

        // MODERN HOOK: Using dispatch() and NO references for objects
        HookRegistry::dispatch('GroupDAO::_returnGroupFromRow', array($group, &$row));

        return $group;
    }

    /**
     * Update the settings for this object
     * @param $group object
     */
    public function updateLocaleFields($group) {
        $this->updateDataObjectSettings('group_settings', $group, array(
            'group_id' => $group->getId()
        ));
    }

    /**
     * Insert a new board group.
     * @param $group Group
     * @return int
     */
    public function insertGroup($group) {
        $this->update(
            'INSERT INTO groups
                (seq, assoc_type, assoc_id, about_displayed, context, publish_email)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            array(
                (int) $group->getSequence(),
                (int) $group->getAssocType(),
                (int) $group->getAssocId(),
                (int) $group->getAboutDisplayed(),
                (int) $group->getContext(),
                (int) $group->getPublishEmail()
            )
        );

        $group->setId($this->getInsertGroupId());
        $this->updateLocaleFields($group);
        return $group->getId();
    }

    /**
     * Update an existing board group.
     * @param $group Group
     */
    public function updateObject($group) {
        $returner = $this->update(
            'UPDATE groups
                SET    seq = ?,
                    assoc_type = ?,
                    assoc_id = ?,
                    about_displayed = ?,
                    context = ?,
                    publish_email = ?
                WHERE    group_id = ?',
            array(
                (float) $group->getSequence(),
                (int) $group->getAssocType(),
                (int) $group->getAssocId(),
                (int) $group->getAboutDisplayed(),
                (int) $group->getContext(),
                (int) $group->getPublishEmail(),
                (int) $group->getId()
            )
        );
        $this->updateLocaleFields($group);
        return $returner;
    }

    /**
     * Update an existing board group.
     * @param $group Group
     */
    public function updateGroup($group) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($group);
    }

    /**
     * Delete a board group, including membership info
     * @param $group Group
     */
    public function deleteObject($group) {
        return $this->deleteGroupById($group->getId());
    }

    /**
     * Delete a board group, including membership info
     * @param $group Group
     */
    public function deleteGroup($group) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->deleteObject($group);
    }

    /**
     * Delete a board group, including membership info
     * @param $groupId int
     */
    public function deleteGroupById($groupId) {
        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $groupMembershipDao->deleteMembershipByGroupId($groupId);
        $this->update('DELETE FROM group_settings WHERE group_id = ?', (int) $groupId);
        return $this->update('DELETE FROM groups WHERE group_id = ?', (int) $groupId);
    }

    /**
     * Delete board groups by assoc ID, including membership info
     * @param $assocType int
     * @param $assocId int
     */
    public function deleteGroupsByAssocId($assocType, $assocId) {
        $groups = $this->getGroups($assocType, $assocId);
        while ($group = $groups->next()) {
            $this->deleteObject($group);
            unset($group);
        }
    }

    /**
     * Sequentially renumber board groups in their sequence order, 
     * optionally by assoc info.
     * @param $assocType int
     * @param $assocId int
     */
    public function resequenceGroups($assocType = null, $assocId = null) {
        if ($assocType !== null) $params = array((int) $assocType, (int) $assocId);
        else $params = array();
        
        $result = $this->retrieve(
            'SELECT group_id FROM groups' .
            ($assocType !== null?' WHERE assoc_type = ? AND assoc_id = ?':'') .
            ' ORDER BY seq',
            $params
        );

        for ($i=1; !$result->EOF; $i++) {
            list($groupId) = $result->fields;
            $this->update(
                'UPDATE groups SET seq = ? WHERE group_id = ?',
                array(
                    $i,
                    (int) $groupId
                )
            );

            $result->MoveNext();
        }

        $result->Close();
        unset($result);
    }

    /**
     * Get the ID of the last inserted board group.
     * @return int
     */
    public function getInsertGroupId() {
        return $this->getInsertId('groups', 'group_id');
    }
    
    /**
     * [WIZDAM FEATURE] Get board groups specifically for frontend display.
     * Mengambil grup yang memiliki anggota (membership) dan 
     * diset untuk tampil (about_displayed).
     * @param $journalId int
     * @return array
     */
    public function getBoardGroupsForDisplay($journalId) {
        $locale = AppLocale::getLocale();
        
        // Konstanta ASSOC_TYPE_JOURNAL biasanya 256
        $assocType = 256; 

        $result = $this->retrieve(
            'SELECT DISTINCT g.group_id, g.seq, gs.setting_value as title
            FROM groups g 
            LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = ? AND gs.locale = ?) 
            INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
            WHERE g.assoc_type = ? AND g.assoc_id = ? AND g.about_displayed = 1 AND g.context = ?
            ORDER BY g.seq',
            array(
                'title',        // setting_name
                $locale,        // locale
                (int) $assocType, 
                (int) $journalId,
                2               // context = 2 (Display Membership)
            )
        );

        $groups = array();
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $groups[] = array(
                'group_id' => $row['group_id'],
                'title' => $row['title'],
                'sequence' => $row['seq']
            );
            $result->MoveNext();
        }

        $result->Close();
        return $groups;
    }
    
    /**
     * [WIZDAM] Mendapatkan judul keanggotaan spesifik seorang user dalam sebuah grup.
     * Menggantikan raw query dari Handler yang lama.
     * @param int $journalId
     * @param int $userId
     * @param int $context
     * @return string
     */
    public function getMembershipTitleByUser($journalId, $userId, $context) {
        $result = &$this->retrieve(
            "SELECT gs.setting_value as title
             FROM groups g 
             LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
             INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
             WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 
             AND gm.user_id = ? AND g.context = ?
             ORDER BY g.seq
             LIMIT 1",
            array(AppLocale::getLocale(), (int) $journalId, (int) $userId, (int) $context)
        );

        $returner = '';
        if ($result->RecordCount() != 0) {
            $returner = $result->fields['title'];
        }
        
        $result->Close();
        return $returner;
    }
    
    /**
     * [WIZDAM] Mendapatkan data grup navigasi (context = 2) untuk menu dropdown/list.
     * Hanya mengambil grup yang memiliki minimal 1 anggota dan disetel tampil di About.
     * @param int $journalId
     * @return array Array of associative arrays (group_id, title, sequence)
     */
    public function getDisplayMembershipGroupsData($journalId) {
        $result = &$this->retrieve(
            "SELECT DISTINCT g.group_id, g.seq, gs.setting_value as title
             FROM groups g 
             LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
             INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
             WHERE g.assoc_type = 256 /* ASSOC_TYPE_JOURNAL */ 
             AND g.assoc_id = ? 
             AND g.about_displayed = 1 
             AND g.context = 2
             ORDER BY g.seq",
            array(AppLocale::getLocale(), (int) $journalId)
        );

        $displayGroups = array();
        while (!$result->EOF) {
            $displayGroups[] = array(
                'group_id' => $result->fields['group_id'],
                'title' => $result->fields['title'],
                'sequence' => $result->fields['seq']
            );
            $result->MoveNext();
        }
        $result->Close();
        
        return $displayGroups;
    }

    /**
     * [WIZDAM] Cek apakah ada grup navigasi (context = 2) yang layak tampil.
     * Digunakan untuk menentukan apakah menu "Membership" harus di-render 
     * atau disembunyikan.
     * @param int $journalId
     * @return boolean
     */
    public function hasDisplayMembershipGroups($journalId) {
        $result = &$this->retrieve(
            "SELECT COUNT(*) as group_count
             FROM groups g 
             INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
             WHERE g.assoc_type = 256 /* ASSOC_TYPE_JOURNAL */ 
             AND g.assoc_id = ? 
             AND g.about_displayed = 1 
             AND g.context = 2",
            array((int) $journalId)
        );

        $returner = false;
        if (isset($result->fields['group_count']) && $result->fields['group_count'] > 0) {
            $returner = true;
        }

        $result->Close();
        return $returner;
    }
}

?>