<?php
declare(strict_types=1);

/**
 * @file classes/journal/SectionEditorsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorsDAO
 * @ingroup journal
 *
 * @brief Class for DAO relating sections to editors.
 */

class SectionEditorsDAO extends DAO {
    
    /**
     * Insert a new section editor.
     * @param int $journalId
     * @param int $sectionId
     * @param int $userId
     * @param bool $canReview
     * @param bool $canEdit
     * @return bool
     */
    public function insertEditor($journalId, $sectionId, $userId, $canReview, $canEdit) {
        return (bool) $this->update(
            'INSERT INTO section_editors
                (journal_id, section_id, user_id, can_review, can_edit)
                VALUES
                (?, ?, ?, ?, ?)',
            [
                (int) $journalId,
                (int) $sectionId,
                (int) $userId,
                $canReview ? 1 : 0,
                $canEdit ? 1 : 0
            ]
        );
    }

    /**
     * Delete a section editor.
     * @param int $journalId
     * @param int $sectionId
     * @param int $userId
     * @return bool
     */
    public function deleteEditor($journalId, $sectionId, $userId) {
        return (bool) $this->update(
            'DELETE FROM section_editors WHERE journal_id = ? AND section_id = ? AND user_id = ?',
            [
                (int) $journalId,
                (int) $sectionId,
                (int) $userId
            ]
        );
    }

    /**
     * Enrich basic User objects with settings in a single query to prevent N+1 issues.
     * @param array $usersById
     * @return void
     */
    public function enrichUsersWithSettings(array $usersById): void {
        if (empty($usersById)) {
            return;
        }

        $userIds = array_keys($usersById);
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        $result = $this->retrieve(
            "SELECT * FROM user_settings WHERE user_id IN ($placeholders)",
            $userIds
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userId = (int) $row['user_id'];
                if (isset($usersById[$userId])) {
                    $usersById[$userId]->setData(
                        (string) $row['setting_name'],
                        $this->convertFromDB($row['setting_value'], $row['setting_type']),
                        empty($row['locale']) ? null : (string) $row['locale']
                    );
                }
                $result->MoveNext();
            }
            $result->Close();
        }
    }

    /**
     * Retrieve section editors for all sections of a journal in one query, grouped by section_id.
     * @param int $journalId
     * @return array
     */
    public function getEditorsGroupedByJournalId(int $journalId): array {
        $grouped = [];

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $result = $this->retrieve(
            'SELECT u.*, e.section_id AS section_id, e.can_review AS can_review, e.can_edit AS can_edit
             FROM users u
             JOIN section_editors e ON (u.user_id = e.user_id)
             WHERE e.journal_id = ?
             ORDER BY e.section_id, u.last_name, u.first_name',
            [(int) $journalId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $sectionId = (int) $row['section_id'];
                $grouped[$sectionId][] = [
                    'user' => $userDao->_returnUserFromRow($row),
                    'canReview' => (bool) $row['can_review'],
                    'canEdit' => (bool) $row['can_edit'],
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $grouped;
    }

    /**
     * Retrieve a list of all section editors assigned to the specified section.
     * @param int $journalId
     * @param int $sectionId
     * @return array
     */
    public function getEditorsBySectionId($journalId, $sectionId) {
        $users = [];

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $result = $this->retrieve(
            'SELECT u.*, e.can_review AS can_review, e.can_edit AS can_edit 
             FROM users u 
             JOIN section_editors e ON (u.user_id = e.user_id) 
             WHERE e.journal_id = ? AND e.section_id = ? 
             ORDER BY u.last_name, u.first_name',
            [(int) $journalId, (int) $sectionId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $users[] = [
                    'user' => $userDao->_returnUserFromRow($row),
                    'canReview' => (bool) $row['can_review'],
                    'canEdit' => (bool) $row['can_edit']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $users;
    }

    /**
     * [WIZDAM] Ambil daftar section_id yang ditugaskan ke SATU user
     * tertentu (arah kebalikan dari getEditorsBySectionId) -- dipakai
     * SectionEditorArticleTypeHandler untuk menentukan section mana
     * saja yang boleh dikonfigurasi Section Editor yang sedang login.
     * TIDAK ADA method serupa ini sebelumnya di DAO ini.
     * @param int $userId
     * @param int $journalId
     * @return int[]
     */
    public function getSectionIdsByUserId($userId, $journalId) {
        $result = $this->retrieve(
            'SELECT section_id FROM section_editors WHERE user_id = ? AND journal_id = ?',
            [(int) $userId, (int) $journalId]
        );
        $sectionIds = [];
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $sectionIds[] = (int) $row['section_id'];
            $result->MoveNext();
        }
        $result->Close();
        return $sectionIds;
    }

    /**
     * Retrieve a list of all section editors not assigned to the specified section.
     * @param int $journalId
     * @param int $sectionId
     * @return array
     */
    public function getEditorsNotInSection($journalId, $sectionId) {
        $users = [];

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $result = $this->retrieve(
            'SELECT u.*
            FROM users u
                LEFT JOIN roles r ON (r.user_id = u.user_id)
                LEFT JOIN section_editors e ON (e.user_id = u.user_id AND e.journal_id = r.journal_id AND e.section_id = ?)
            WHERE r.journal_id = ? AND
                r.role_id = ? AND
                e.section_id IS NULL
            ORDER BY u.last_name, u.first_name',
            [(int) $sectionId, (int) $journalId, (int) ROLE_ID_SECTION_EDITOR]
        );

        if ($result) {
            while (!$result->EOF) {
                $users[] = $userDao->_returnUserFromRow($result->GetRowAssoc(false));
                $result->MoveNext();
            }
            $result->Close();
        }

        return $users;
    }

    /**
     * Delete all section editors for a specified section in a journal.
     * @param int $sectionId
     * @param int|null $journalId
     * @return bool
     */
    public function deleteEditorsBySectionId($sectionId, $journalId = null) {
        if ($journalId !== null) {
            return (bool) $this->update(
                'DELETE FROM section_editors WHERE journal_id = ? AND section_id = ?',
                [(int) $journalId, (int) $sectionId]
            );
        }
        
        return (bool) $this->update(
            'DELETE FROM section_editors WHERE section_id = ?',
            [(int) $sectionId]
        );
    }

    /**
     * Delete all section editors for a specified journal.
     * @param int $journalId
     * @return bool
     */
    public function deleteEditorsByJournalId($journalId) {
        return (bool) $this->update(
            'DELETE FROM section_editors WHERE journal_id = ?', 
            [(int) $journalId]
        );
    }

    /**
     * Delete all section assignments for the specified user.
     * @param int $userId
     * @param int|null $journalId
     * @param int|null $sectionId
     * @return bool
     */
    public function deleteEditorsByUserId($userId, $journalId = null, $sectionId = null) {
        $params = [(int) $userId];
        $sql = 'DELETE FROM section_editors WHERE user_id = ?';

        if ($journalId !== null) {
            $sql .= ' AND journal_id = ?';
            $params[] = (int) $journalId;
        }

        if ($sectionId !== null) {
            $sql .= ' AND section_id = ?';
            $params[] = (int) $sectionId;
        }

        return (bool) $this->update($sql, $params);
    }

    /**
     * Check if a user is assigned to a specified section.
     * @param int $journalId
     * @param int $sectionId
     * @param int $userId
     * @return bool
     */
    public function editorExists($journalId, $sectionId, $userId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM section_editors WHERE journal_id = ? AND section_id = ? AND user_id = ?', 
            [(int) $journalId, (int) $sectionId, (int) $userId]
        );
        
        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['count'] > 0;
        }
        
        if ($result) {
            $result->Close();
        }

        return $returner;
    }
    
}
?>