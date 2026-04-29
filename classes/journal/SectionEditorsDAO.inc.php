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
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Visibility, Ref Removal)
 * - Explicit SQL JOINS
 * - Strict Integer Casting
 */

class SectionEditorsDAO extends DAO {
    
    /**
     * Insert a new section editor.
     * @param int $journalId
     * @param int $sectionId
     * @param int $userId
     * @param boolean $canReview
     * @param boolean $canEdit
     * @return boolean
     */
    public function insertEditor($journalId, $sectionId, $userId, $canReview, $canEdit) {
        return $this->update(
            'INSERT INTO section_editors
                (journal_id, section_id, user_id, can_review, can_edit)
                VALUES
                (?, ?, ?, ?, ?)',
            array(
                (int) $journalId,
                (int) $sectionId,
                (int) $userId,
                $canReview ? 1 : 0,
                $canEdit ? 1 : 0
            )
        );
    }

    /**
     * Delete a section editor.
     * @param int $journalId
     * @param int $sectionId
     * @param int $userId
     * @return boolean
     */
    public function deleteEditor($journalId, $sectionId, $userId) {
        return $this->update(
            'DELETE FROM section_editors WHERE journal_id = ? AND section_id = ? AND user_id = ?',
            array(
                (int) $journalId,
                (int) $sectionId,
                (int) $userId
            )
        );
    }

    /**
     * Retrieve a list of all section editors assigned to the specified section.
     * @param int $journalId
     * @param int $sectionId
     * @return array matching Users
     */
    public function getEditorsBySectionId($journalId, $sectionId) {
        $users = array();

        $userDao = DAORegistry::getDAO('UserDAO');

        // Modernized SQL: Explicit JOIN
        $result = $this->retrieve(
            'SELECT u.*, e.can_review AS can_review, e.can_edit AS can_edit 
             FROM users u 
             JOIN section_editors e ON (u.user_id = e.user_id) 
             WHERE e.journal_id = ? AND e.section_id = ? 
             ORDER BY u.last_name, u.first_name',
            array((int) $journalId, (int) $sectionId)
        );

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $users[] = array(
                'user' => $userDao->_returnUserFromRow($row),
                'canReview' => $row['can_review'],
                'canEdit' => $row['can_edit']
            );
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $users;
    }

    /**
     * Retrieve a list of all section editors not assigned to the specified section.
     * @param int $journalId
     * @param int $sectionId
     * @return array matching Users
     */
    public function getEditorsNotInSection($journalId, $sectionId) {
        $users = array();

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
            array((int) $sectionId, (int) $journalId, ROLE_ID_SECTION_EDITOR)
        );

        while (!$result->EOF) {
            $users[] = $userDao->_returnUserFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $users;
    }

    /**
     * Delete all section editors for a specified section in a journal.
     * @param int $sectionId
     * @param int|null $journalId
     * @return boolean
     */
    public function deleteEditorsBySectionId($sectionId, $journalId = null) {
        if (isset($journalId)) {
            return $this->update(
                'DELETE FROM section_editors WHERE journal_id = ? AND section_id = ?',
                array((int) $journalId, (int) $sectionId)
            );
        } else {
            return $this->update(
                'DELETE FROM section_editors WHERE section_id = ?',
                (int) $sectionId
            );
        }
    }

    /**
     * Delete all section editors for a specified journal.
     * @param int $journalId
     * @return boolean
     */
    public function deleteEditorsByJournalId($journalId) {
        return $this->update(
            'DELETE FROM section_editors WHERE journal_id = ?', 
            (int) $journalId
        );
    }

    /**
     * Delete all section assignments for the specified user.
     * @param int $userId
     * @param int|null $journalId optional
     * @param int|null $sectionId optional
     * @return boolean
     */
    public function deleteEditorsByUserId($userId, $journalId = null, $sectionId = null) {
        // Logic simplified for clarity and strict typing
        $params = array((int) $userId);
        $sql = 'DELETE FROM section_editors WHERE user_id = ?';

        if (isset($journalId)) {
            $sql .= ' AND journal_id = ?';
            $params[] = (int) $journalId;
        }

        if (isset($sectionId)) {
            $sql .= ' AND section_id = ?';
            $params[] = (int) $sectionId;
        }

        return $this->update($sql, $params);
    }

    /**
     * Check if a user is assigned to a specified section.
     * @param int $journalId
     * @param int $sectionId
     * @param int $userId
     * @return boolean
     */
    public function editorExists($journalId, $sectionId, $userId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM section_editors WHERE journal_id = ? AND section_id = ? AND user_id = ?', 
            array((int) $journalId, (int) $sectionId, (int) $userId)
        );
        
        $returner = (isset($result->fields[0]) && $result->fields[0] == 1) ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }
}

?>