<?php
declare(strict_types=1);

/**
 * @file classes/user/UserSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserSettingsDAO
 * @ingroup user
 * @see User
 *
 * @brief Operations for retrieving and modifying user settings.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.user.PKPUserSettingsDAO');

class UserSettingsDAO extends PKPUserSettingsDAO {
    
    /**
     * Retrieve a user setting value.
     * @param int $userId
     * @param string $name
     * @param int|null $assocType (Legacy usage: treated as $journalId if $assocId is null)
     * @param int|null $assocId
     * @return mixed
     */
    public function getSetting($userId, $name, $assocType = null, $assocId = null) {
        // [WIZDAM FIX] Logic adjustment for OJS 2.x/3.x legacy compatibility.
        // If $assocId is null, it means the caller is likely using the old signature:
        // getSetting($userId, $name, $journalId).
        // In this case, $assocType holds the $journalId (or null), and we must
        // force the internal ASSOC_TYPE_JOURNAL to match the original class behavior.
        if ($assocId === null) {
            $journalId = $assocType; 
            $assocType = ASSOC_TYPE_JOURNAL;
            $assocId = $journalId;
        }
        
        return parent::getSetting((int) $userId, $name, (int) $assocType, (int) $assocId);
    }

    /**
     * Retrieve all users by setting name and value.
     * @param string $name
     * @param mixed $value
     * @param string|null $type
     * @param int|null $assocType (Legacy: $journalId)
     * @param int|null $assocId
     * @return DAOResultFactory matching Users
     */
    public function getUsersBySetting($name, $value, $type = null, $assocType = null, $assocId = null) {
        // [WIZDAM FIX] Logic adjustment for legacy signature:
        // getUsersBySetting($name, $value, $type, $journalId)
        if ($assocId === null) {
            $journalId = $assocType;
            $assocType = ASSOC_TYPE_JOURNAL;
            $assocId = $journalId;
        }

        return parent::getUsersBySetting($name, $value, $type, (int) $assocType, (int) $assocId);
    }

    /**
     * Retrieve all settings for a user for a journal.
     * @param int $userId
     * @param int|null $journalId
     * @return array 
     */
    public function getSettingsByJournal($userId, $journalId = null) {
        return parent::getSettingsByAssoc((int) $userId, ASSOC_TYPE_JOURNAL, (int) $journalId);
    }

    /**
     * Add/update a user setting.
     * @param int $userId
     * @param string $name
     * @param mixed $value
     * @param string|null $type
     * @param int|null $assocType (Legacy: $journalId)
     * @param int|null $assocId
     */
    public function updateSetting($userId, $name, $value, $type = null, $assocType = null, $assocId = null) {
        // [WIZDAM FIX] Logic adjustment for legacy signature:
        // updateSetting($userId, $name, $value, $type, $journalId)
        if ($assocId === null) {
            $journalId = $assocType;
            $assocType = ASSOC_TYPE_JOURNAL;
            $assocId = $journalId;
        }

        return parent::updateSetting((int) $userId, $name, $value, $type, (int) $assocType, (int) $assocId);
    }

    /**
     * Delete a user setting.
     * @param int $userId
     * @param string $name
     * @param int|null $assocType (Legacy: $journalId)
     * @param int|null $assocId
     */
    public function deleteSetting($userId, $name, $assocType = null, $assocId = null) {
        // [WIZDAM FIX] Logic adjustment for legacy signature:
        // deleteSetting($userId, $name, $journalId)
        if ($assocId === null) {
            $journalId = $assocType;
            $assocType = ASSOC_TYPE_JOURNAL;
            $assocId = $journalId;
        }

        return parent::deleteSetting((int) $userId, $name, (int) $assocType, (int) $assocId);
    }
}

?>