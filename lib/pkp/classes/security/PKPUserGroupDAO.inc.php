<?php
declare(strict_types=1);

/**
 * @file classes/security/PKPUserGroupDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPUserGroupDAO
 * @ingroup security
 * @see UserGroup
 *
 * @brief Operations for retrieving and modifying User Groups and user group assignments.
 */

import('lib.pkp.classes.security.UserGroup');

class PKPUserGroupDAO extends DAO {

    /** @var UserDAO a shortcut to get the UserDAO **/
    public $userDao;

    /** @var UserGroupAssignmentDAO a shortcut to get the UserGroupAssignmentDAO **/
    public $userGroupAssignmentDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->userDao = DAORegistry::getDAO('UserDAO');
        $this->userGroupAssignmentDao = DAORegistry::getDAO('UserGroupAssignmentDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPUserGroupDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPUserGroupDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $this->__construct();
    }

    /**
     * create new data object
     * (allows DAO to be subclassed)
     * @return UserGroup
     */
    public function newDataObject() {
        return new UserGroup();
    }

    /**
     * Internal function to return a UserGroup object from a row.
     * @param mixed $row
     * @return UserGroup
     */
    public function _returnFromRow($row) {
        $userGroup = $this->newDataObject();

        $userGroup->setId((int) ($row['user_group_id'] ?? 0));
        $userGroup->setRoleId((int) ($row['role_id'] ?? 0));
        $userGroup->setContextId((int) ($row['context_id'] ?? 0));
        $userGroup->setPath((string) ($row['path'] ?? ''));
        $userGroup->setDefault((bool) ($row['is_default'] ?? false));

        $this->getDataObjectSettings('user_group_settings', 'user_group_id', (int) ($row['user_group_id'] ?? 0), $userGroup);

        HookRegistry::dispatch('PKPUserGroupDAO::_returnFromRow', [$userGroup, &$row]);

        return $userGroup;
    }

    /**
     * Insert a user group.
     * @param mixed $userGroup UserGroup
     * @return int Inserted ID
     */
    public function insertUserGroup($userGroup) {
        $this->update(
            'INSERT INTO user_groups (role_id, path, context_id, is_default) VALUES (?, ?, ?, ?)',
            [
                (int) $userGroup->getRoleId(),
                (string) $userGroup->getPath(),
                (int) $userGroup->getContextId(),
                (int) $userGroup->getDefault()
            ]
        );

        $userGroup->setId((int) $this->getInsertUserGroupId());
        $this->updateLocaleFields($userGroup);
        return (int) $userGroup->getId();
    }

    /**
     * Delete a user group by its id
     * will also delete related settings and all the assignments to this group
     * @param mixed $contextId
     * @param mixed $userGroupId
     * @return boolean
     */
    public function deleteById($contextId, $userGroupId) {
        $safeUserGroupId = (int) $userGroupId;
        $safeContextId = (int) $contextId;

        $ret1 = $this->userGroupAssignmentDao->deleteAssignmentsByUserGroupId($safeUserGroupId);
        $ret2 = $this->update('DELETE FROM user_group_settings WHERE user_group_id = ?', [$safeUserGroupId]);
        $ret3 = $this->update('DELETE FROM user_groups WHERE user_group_id = ?', [$safeUserGroupId]);
        $ret4 = $this->removeAllStagesFromGroup($safeContextId, $safeUserGroupId);

        return (bool) ($ret1 && $ret2 && $ret3 && $ret4);
    }

    /**
     * Delete a user group.
     * will also delete related settings and all the assignments to this group
     * @param mixed $userGroup UserGroup
     * @return boolean
     */
    public function deleteUserGroup($userGroup) {
        return $this->deleteById((int) $userGroup->getContextId(), (int) $userGroup->getId());
    }

    /**
     * Delete a user group by its context id
     * @param mixed $contextId
     * @return boolean
     */
    public function deleteByContextId($contextId) {
        $safeContextId = (int) $contextId;
        $result = $this->retrieve('SELECT user_group_id FROM user_groups WHERE context_id = ?', [$safeContextId]);

        $returner = true;
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userGroupId = (int) ($row['user_group_id'] ?? 0);

                $ret1 = $this->update('DELETE FROM user_group_stage WHERE user_group_id = ?', [$userGroupId]);
                $ret2 = $this->update('DELETE FROM user_group_settings WHERE user_group_id = ?', [$userGroupId]);
                $ret3 = $this->update('DELETE FROM user_groups WHERE user_group_id = ?', [$userGroupId]);

                $returner = $returner && $ret1 && $ret2 && $ret3;
                $result->MoveNext();
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Get the ID of the last inserted user group.
     * @return int
     */
    public function getInsertUserGroupId() {
        return (int) $this->getInsertId('user_groups', 'user_group_id');
    }

    /**
     * Get field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['name', 'abbrev']);
    }

    /**
     * Update the localized data for this object
     * @param mixed $userGroup UserGroup
     */
    public function updateLocaleFields(&$userGroup) {
        $this->updateDataObjectSettings('user_group_settings', $userGroup, [
            'user_group_id' => (int) $userGroup->getId()
        ]);
    }

    /**
     * Get an individual user group
     * @param mixed $userGroupId
     * @param mixed $contextId
     * @return UserGroup|null
     */
    public function getById($userGroupId, $contextId = null) {
        $params = [(int) $userGroupId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }
        
        $result = $this->retrieve(
            'SELECT user_group_id, context_id, role_id, path, is_default FROM user_groups WHERE user_group_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
            $params
        );

        $returner = null;
        // [LUMERA FIX] Modern ADODB check
        if ($result && !$result->EOF) {
            $returner = $this->_returnFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get a single default user group with a particular roleId
     * @param mixed $contextId
     * @param mixed $roleId
     * @return UserGroup|null
     */
    public function getDefaultByRoleId($contextId, $roleId) {
        $returner = null;
        $allDefaults = $this->getByRoleId((int) $contextId, (int) $roleId, true);
        if ($allDefaults && !$allDefaults->eof()) {
            $returner = $allDefaults->next();
        }
        return $returner;
    }

    /**
     * Get all user groups belonging to a role
     * @param mixed $contextId
     * @param mixed $roleId
     * @param boolean $default
     * @return DAOResultFactory
     */
    public function getByRoleId($contextId, $roleId, $default = false) {
        $params = [(int) $contextId, (int) $roleId];
        if ($default) {
            $params[] = 1;
        }
        
        $result = $this->retrieve(
            'SELECT * FROM user_groups WHERE context_id = ? AND role_id = ?' . ($default ? ' AND is_default = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Get an array of user group ids belonging to a given role
     * @param mixed $roleId
     * @param mixed $contextId
     * @return array
     */
    public function getUserGroupIdsByRoleId($roleId, $contextId = null) {
        $sql = 'SELECT user_group_id FROM user_groups WHERE role_id = ?';
        $params = [(int) $roleId];

        if ($contextId) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve($sql, $params);

        $userGroupIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userGroupIds[] = (int) ($row['user_group_id'] ?? 0);
                $result->MoveNext();
            }
            $result->Close();
        }
        
        return $userGroupIds;
    }

    /**
     * Check if a user is in a particular user group
     * @param mixed $userId
     * @param mixed $userGroupId
     * @return boolean
     */
    public function userInGroup($userId, $userGroupId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE uug.user_id = ? AND ug.user_group_id = ?',
            [(int) $userId, (int) $userGroupId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['count']) && ((int) $row['count']) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Check if a user is in any user group
     * @param mixed $userId
     * @param mixed $contextId
     * @return boolean
     */
    public function userInAnyGroup($userId, $contextId = null) {
        $params = [(int) $userId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE uug.user_id = ?' . ($contextId ? ' AND ug.context_id = ?' : ''),
            $params
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['count']) && ((int) $row['count']) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve user groups to which a user is assigned.
     * @param mixed $userId
     * @param mixed $contextId
     * @return DAOResultFactory
     */
    public function getByUserId($userId, $contextId = null) {
        $params = [(int) $userId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }
        
        $result = $this->retrieve(
            'SELECT ug.* FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE uug.user_id = ?' . ($contextId ? ' AND ug.context_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Validation check to see if user group exists for a given context
     * @param mixed $contextId
     * @param mixed $userGroupId
     * @return bool
     */
    public function contextHasGroup($contextId, $userGroupId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM user_groups ug WHERE ug.user_group_id = ? AND ug.context_id = ?',
            [(int) $userGroupId, (int) $contextId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['count']) && ((int) $row['count']) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve user groups for a given context (all contexts if null)
     * @param mixed $contextId
     * @return DAOResultFactory
     */
    public function getByContextId($contextId = null) {
        $params = [];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }
        
        $result = $this->retrieve(
            'SELECT ug.* FROM user_groups ug' . ($contextId !== null ? ' WHERE ug.context_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Retrieve the number of users associated with the specified context.
     * @param mixed $contextId
     * @param mixed $userGroupId
     * @param mixed $roleId
     * @return int
     */
    public function getContextUsersCount($contextId, $userGroupId = null, $roleId = null) {
        $params = [(int) $contextId];
        if ($userGroupId !== null) {
            $params[] = (int) $userGroupId;
        }
        if ($roleId !== null) {
            $params[] = (int) $roleId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(DISTINCT(uug.user_id)) AS user_count FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE context_id = ?' . ($userGroupId !== null ? ' AND ug.user_group_id = ?' : '') . ($roleId !== null ? ' AND ug.role_id = ?' : ''),
            $params
        );

        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) ($row['user_count'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * return an Iterator of User objects given the search parameters
     * @param mixed $contextId
     * @param mixed $searchType
     * @param mixed $search
     * @param mixed $searchMatch
     * @param mixed $dbResultRange
     * @return DAOResultFactory|null
     */
    public function getUsersByContextId($contextId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
        return $this->getUsersById(null, $contextId, $searchType, $search, $searchMatch, $dbResultRange);
    }

    /**
     * Find users that don't have a given role
     * @param mixed $roleId
     * @param mixed $contextId
     * @param mixed $search
     * @return DAOResultFactory
     */
    public function getUsersNotInRole($roleId, $contextId = null, $search = null) {
        $params = [(int) $roleId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }
        if ($search !== null) {
            $safeSearch = '%' . (string) $search . '%';
            $params = array_merge($params, array_fill(0, 5, $safeSearch));
        }

        $result = $this->retrieve(
            'SELECT DISTINCT u.* FROM users u, user_groups ug, user_user_groups uug WHERE ug.user_group_id = uug.user_group_id AND u.user_id = uug.user_id AND ug.role_id <> ?' .
            ($contextId !== null ? ' AND ug.context_id = ?' : '') .
            ($search !== null ? ' AND (u.first_name LIKE ? OR u.middle_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)' : ''),
            $params
        );

        return new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
    }

    /**
     * return an Iterator of User objects given the search parameters
     * @param mixed $userGroupId
     * @param mixed $contextId
     * @param mixed $searchType
     * @param mixed $search
     * @param mixed $searchMatch
     * @param mixed $dbResultRange
     * @return DAOResultFactory|null
     */
    public function getUsersById($userGroupId = null, $contextId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
        $paramArray = [];

        if ($userGroupId !== null) {
            $paramArray[] = (int) $userGroupId;
        }
        if ($contextId !== null) {
            $paramArray[] = (int) $contextId;
        }

        if ($contextId === null && $userGroupId === null) {
            return null;
        }

        $searchSql = $this->_getSearchSql($searchType, $search, $searchMatch, $paramArray);

        $sql = 'SELECT DISTINCT u.*
            FROM users AS u
            LEFT JOIN user_settings us ON (us.user_id = u.user_id AND us.setting_name = "affiliation")
            LEFT JOIN user_interests ui ON (u.user_id = ui.user_id)
            LEFT JOIN controlled_vocab_entry_settings cves ON (ui.controlled_vocab_entry_id = cves.controlled_vocab_entry_id)
            LEFT JOIN user_user_groups uug ON (uug.user_id = u.user_id)
            LEFT JOIN user_groups ug ON (ug.user_group_id = uug.user_group_id) WHERE';

        $sql .= ($userGroupId !== null ? ' ug.user_group_id = ? ' . ($contextId !== null ? 'AND ' : '') : ' ');
        $sql .= ($contextId !== null ? ' ug.context_id = ? ' : ' ') . $searchSql;

        $result = $this->retrieveRange($sql, $paramArray, $dbResultRange);

        return new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
    }

    /**
     * Retrieve those users with no group assignments in any press.
     * @param mixed $filter
     * @param boolean $allowDisabled
     * @param mixed $dbResultRange
     * @return DAOResultFactory
     */
    public function getUsersWithNoUserGroupAssignments($filter = null, $allowDisabled = true, $dbResultRange = null) {
        $sql = 'SELECT DISTINCT u.*
            FROM users AS u
            LEFT JOIN user_settings us ON (us.user_id = u.user_id AND us.setting_name = "affiliation")
            LEFT JOIN user_interests ui ON (u.user_id = ui.user_id)
            LEFT JOIN controlled_vocab_entry_settings cves ON (ui.controlled_vocab_entry_id = cves.controlled_vocab_entry_id)
            LEFT JOIN user_user_groups uug ON u.user_id=uug.user_id WHERE uug.user_group_id IS NULL ';

        $sql .= ($allowDisabled ? '' : ' AND u.disabled = 0');

        $searchSql = '';
        $paramArray = [];

        if ($filter !== null && is_array($filter)) {
            $searchType = $filter['searchType'] ?? null;
            $search = $filter['search'] ?? null;
            $searchMatch = $filter['searchMatch'] ?? null;

            $searchSql = $this->_getSearchSql($searchType, $search, $searchMatch, $paramArray);
            $sql .= $searchSql;
        }

        $result = $this->retrieveRange($sql, $paramArray, $dbResultRange);
        return new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
    }

    //
    // UserGroupAssignment related
    //
    /**
     * Delete all user group assignments for a given userId
     * @param mixed $userId
     * @param mixed $userGroupId
     */
    public function deleteAssignmentsByUserId($userId, $userGroupId = null) {
        $this->userGroupAssignmentDao->deleteByUserId((int) $userId, $userGroupId !== null ? (int) $userGroupId : null);
    }

    /**
     * Delete all assignments to a given user group
     * @param mixed $userGroupId
     */
    public function deleteAssignmentsByUserGroupId($userGroupId) {
        $this->userGroupAssignmentDao->deleteAssignmentsByUserGroupId((int) $userGroupId);
    }

    /**
     * Remove all user group assignments for a given user in a context
     * @param mixed $contextId
     * @param mixed $userId
     */
    public function deleteAssignmentsByContextId($contextId, $userId = null) {
        $this->userGroupAssignmentDao->deleteAssignmentsByContextId((int) $contextId, $userId !== null ? (int) $userId : null);
    }

    /**
     * Assign a given user to a given user group
     * @param mixed $userId
     * @param mixed $groupId
     * @return int|bool
     */
    public function assignUserToGroup($userId, $groupId) {
        $assignment = $this->userGroupAssignmentDao->newDataObject();
        $assignment->setUserId((int) $userId);
        $assignment->setUserGroupId((int) $groupId);
        return $this->userGroupAssignmentDao->insertAssignment($assignment);
    }

    /**
     * remove a given user from a given user group
     * @param mixed $userId
     * @param mixed $groupId
     * @param mixed $contextId
     */
    public function removeUserFromGroup($userId, $groupId, $contextId) {
        $assignments = $this->userGroupAssignmentDao->getByUserId((int) $userId, (int) $contextId);
        if ($assignments) {
            while ($assignment = $assignments->next()) {
                if ((int) $assignment->getUserGroupId() === (int) $groupId) {
                    $this->userGroupAssignmentDao->deleteAssignment($assignment);
                }
            }
        }
    }

    /**
     * Delete all stage assignments in a user group.
     * @param mixed $contextId
     * @param mixed $userGroupId
     * @return bool Returns true on success or if stage features are not supported in this version.
     */
    public function removeAllStagesFromGroup($contextId, $userGroupId) {
        if (!method_exists($this, 'getAssignedStagesByUserGroupId')) {
            return true; 
        }
        $assignedStages = $this->getAssignedStagesByUserGroupId((int) $contextId, (int) $userGroupId);
        if (!empty($assignedStages)) {
            foreach ($assignedStages as $stageId => $stageLocaleKey) {
                if (method_exists($this, 'removeGroupFromStage')) {
                    $this->removeGroupFromStage((int) $contextId, (int) $userGroupId, (int) $stageId);
                } else {
                    error_log("[LUMERA WARNING] removeGroupFromStage not found for stage: $stageId");
                }
            }
        }

        return true;
    }

    //
    // Extra settings (not handled by rest of Dao)
    //
    /**
     * Method for update a userGroup setting
     * @param mixed $userGroupId
     * @param mixed $name
     * @param mixed $value
     * @param mixed $type data type of the setting. If omitted, type will be guessed
     * @param boolean $isLocalized
     */
    public function updateSetting($userGroupId, $name, $value, $type = null, $isLocalized = false) {
        $keyFields = ['setting_name', 'locale', 'user_group_id'];
        $safeUserGroupId = (int) $userGroupId;
        $safeName = (string) $name;

        if (!$isLocalized) {
            $value = $this->convertToDB($value, $type);
            $this->replace('user_group_settings',
                [
                    'user_group_id' => $safeUserGroupId,
                    'setting_name' => $safeName,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'locale' => ''
                ],
                $keyFields
            );
        } else {
            if (is_array($value)) {
                foreach ($value as $locale => $localeValue) {
                    $this->update('DELETE FROM user_group_settings WHERE user_group_id = ? AND setting_name = ? AND locale = ?', [$safeUserGroupId, $safeName, (string) $locale]);
                    if (empty($localeValue)) {
                        continue;
                    }
                    $type = null;
                    $this->update('INSERT INTO user_group_settings (user_group_id, setting_name, setting_value, setting_type, locale) VALUES (?, ?, ?, ?, ?)',
                        [
                            $safeUserGroupId, $safeName, $this->convertToDB($localeValue, $type), $type, (string) $locale
                        ]
                    );
                }
            }
        }
    }

    /**
     * Retrieve a context setting value.
     * @param mixed $userGroupId
     * @param mixed $name
     * @param mixed $locale
     * @return mixed
     */
    public function getSetting($userGroupId, $name, $locale = null) {
        $params = [(int) $userGroupId, (string) $name];
        if ($locale) {
            $params[] = (string) $locale;
        }
        
        $result = $this->retrieve(
            'SELECT setting_name, setting_value, setting_type, locale FROM user_group_settings WHERE user_group_id = ? AND setting_name = ?' . ($locale ? ' AND locale = ?' : ''),
            $params
        );

        $recordCount = $result ? $result->RecordCount() : 0;
        $returner = false;
        
        if ($recordCount == 1 && $result) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->convertFromDB($row['setting_value'] ?? null, $row['setting_type'] ?? null);
        } elseif ($recordCount > 1 && $result) {
            $returner = [];
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner[$row['locale'] ?? ''] = $this->convertFromDB($row['setting_value'] ?? null, $row['setting_type'] ?? null);
                $result->MoveNext();
            }
            $result->Close();
        }
        return $returner;
    }

    //
    // Install/Defaults with settings
    //
    /**
     * Load the XML file and move the settings to the DB
     * @param mixed $contextId
     * @param mixed $filename
     * @return bool
     */
    public function installSettings($contextId, $filename) {
        $xmlParser = new PKPXMLParser();
        $tree = $xmlParser->parse((string) $filename);

        if (!$tree) {
            $xmlParser->destroy();
            return false;
        }

        $stageProduction = defined('WORKFLOW_STAGE_ID_PRODUCTION') ? WORKFLOW_STAGE_ID_PRODUCTION : 4;
        $stageSubmission = defined('WORKFLOW_STAGE_ID_SUBMISSION') ? WORKFLOW_STAGE_ID_SUBMISSION : 1;

        foreach ($tree->getChildren() as $setting) {
            $roleId = (int) hexdec((string) $setting->getAttribute('roleId'));
            $nameKey = (string) $setting->getAttribute('name');
            $abbrevKey = (string) $setting->getAttribute('abbrev');
            $defaultStages = explode(',', (string) $setting->getAttribute('stages'));
            
            $userGroup = $this->newDataObject();
            $role = new Role();
            $userGroup->setRoleId($roleId);
            
            $path = method_exists($role, 'getPath') ? (string) $role->getPath() : 'default';
            $userGroup->setPath($path);
            
            $userGroup->setContextId((int) $contextId);
            $userGroup->setDefault(true);

            $userGroupId = (int) $this->insertUserGroup($userGroup);

            foreach ($defaultStages as $stageId) {
                $stageId = (int) trim((string) $stageId);
                if (!empty($stageId) && $stageId <= $stageProduction && $stageId >= $stageSubmission) {
                    if (method_exists($this, 'assignGroupToStage')) {
                        $this->assignGroupToStage((int) $contextId, $userGroupId, $stageId);
                    }
                }
            }

            $this->updateSetting($userGroup->getId(), 'nameLocaleKey', $nameKey);
            $this->updateSetting($userGroup->getId(), 'abbrevLocaleKey', $abbrevKey);
        }

        $this->installLocale(AppLocale::getLocale(), (int) $contextId);

        $xmlParser->destroy();
        
        return true;
    }

    /**
     * use the locale keys stored in the settings table to install the locale settings
     * @param mixed $locale
     * @param mixed $contextId
     */
    public function installLocale($locale, $contextId = null) {
        $userGroups = $this->getByContextId($contextId);
        if ($userGroups) {
            while (!$userGroups->eof()) {
                $userGroup = $userGroups->next();
                if ($userGroup) {
                    $nameKey = (string) $this->getSetting((int) $userGroup->getId(), 'nameLocaleKey');
                    $this->updateSetting((int) $userGroup->getId(), 'name', [(string) $locale => __($nameKey, null, (string) $locale)], 'string', (string) $locale);

                    $abbrevKey = (string) $this->getSetting((int) $userGroup->getId(), 'abbrevLocaleKey');
                    $this->updateSetting((int) $userGroup->getId(), 'abbrev', [(string) $locale => __($abbrevKey, null, (string) $locale)], 'string', (string) $locale);
                }
            }
        }
    }

    /**
     * Remove all settings associated with a locale
     * @param mixed $locale
     */
    public function deleteSettingsByLocale($locale) {
        return $this->update('DELETE FROM user_group_settings WHERE locale = ?', [(string) $locale]);
    }

    /**
     * Private function to assemble the SQL for searching users.
     * @param mixed $searchType the field to search on.
     * @param mixed $search the keywords to search for.
     * @param mixed $searchMatch where to match (is, contains, startsWith).
     * @param array $paramArray SQL parameter array reference
     */
    public function _getSearchSql($searchType, $search, $searchMatch, &$paramArray) {
        $searchTypeMap = [
            USER_FIELD_FIRSTNAME => 'u.first_name',
            USER_FIELD_LASTNAME => 'u.last_name',
            USER_FIELD_USERNAME => 'u.username',
            USER_FIELD_EMAIL => 'u.email',
            USER_FIELD_AFFILIATION => 'us.setting_value'
        ];

        $searchSql = '';

        if (!empty($search)) {
            $safeSearch = (string) $search;
            if (!isset($searchTypeMap[$searchType])) {
                $concatFields = ' ( LOWER(CONCAT(' . join(', ', $searchTypeMap) . ')) LIKE ? OR LOWER(cves.setting_value) LIKE ? ) ';
                $safeSearch = strtolower($safeSearch);
                $words = preg_split('{\s+}', $safeSearch);
                $searchFieldMap = [];

                foreach ($words as $word) {
                    $searchFieldMap[] = $concatFields;
                    $term = '%' . $word . '%';
                    $paramArray[] = $term;
                    $paramArray[] = $term;
                }

                $searchSql .= ' AND (  ' . join(' AND ', $searchFieldMap) . '  ) ';
            } else {
                $fieldName = $searchTypeMap[$searchType];
                switch ($searchMatch) {
                    case 'is':
                        $searchSql = "AND LOWER($fieldName) = LOWER(?)";
                        $paramArray[] = $safeSearch;
                        break;
                    case 'contains':
                        $searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
                        $paramArray[] = '%' . $safeSearch . '%';
                        break;
                    case 'startsWith':
                        $searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
                        $paramArray[] = $safeSearch . '%';
                        break;
                }
            }
        } else {
            switch ($searchType) {
                case USER_FIELD_USERID:
                    $searchSql = 'AND u.user_id = ?';
                    break;
                case USER_FIELD_INITIAL:
                    $searchSql = 'AND LOWER(u.last_name) LIKE LOWER(?)';
                    break;
            }
        }

        $searchSql .= ' ORDER BY u.last_name, u.first_name';

        return $searchSql;
    }
    
}
?>