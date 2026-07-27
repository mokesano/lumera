<?php
declare(strict_types=1);

/**
 * @file classes/submission/editAssignment/EditAssignmentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditAssignmentDAO
 * @ingroup submission
 * @see EditAssignment
 *
 * @brief Class for DAO relating editors to articles.
 */

import('classes.submission.editAssignment.EditAssignment');

class EditAssignmentDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditAssignmentDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Retrieve an edit assignment by id.
     * @param int $editId
     * @return EditAssignment|null
     */
    public function getEditAssignment($editId) {
        $result = $this->retrieve(
            'SELECT e.*, u.first_name, u.last_name, u.email, u.initials, r.role_id AS editor_role_id FROM articles a LEFT JOIN edit_assignments e ON (a.article_id = e.article_id) LEFT JOIN users u ON (e.editor_id = u.user_id) LEFT JOIN roles r ON (r.user_id = e.editor_id AND r.role_id = ' . ROLE_ID_EDITOR . ' AND r.journal_id = a.journal_id) WHERE e.edit_id = ? AND a.article_id = e.article_id',
            [(int) $editId]
        );

        $returner = null;
        if ($result->RecordCount() !== 0) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->_returnEditAssignmentFromRow($row);
        }

        $result->Close();
        return $returner;
    }

    /**
     * Retrieve edit assignments by article id.
     * @param int $articleId
     * @return DAOResultFactory
     */
    public function getEditAssignmentsByArticleId($articleId) {
        $result = $this->retrieve(
            'SELECT e.*, u.first_name, u.last_name, u.email, u.initials, r.role_id AS editor_role_id FROM articles a LEFT JOIN edit_assignments e ON (a.article_id = e.article_id) LEFT JOIN users u ON (e.editor_id = u.user_id) LEFT JOIN roles r ON (r.user_id = e.editor_id AND r.role_id = ' . ROLE_ID_EDITOR . ' AND r.journal_id = a.journal_id) WHERE e.article_id = ? AND a.article_id = e.article_id ORDER BY e.date_notified ASC',
            [(int) $articleId]
        );

        return new DAOResultFactory($result, $this, '_returnEditAssignmentFromRow');
    }

    /**
     * Retrieve those edit assignments that relate to full editors.
     * @param int $articleId
     * @return DAOResultFactory
     */
    public function getEditorAssignmentsByArticleId($articleId) {
        $result = $this->retrieve(
            'SELECT e.*, u.first_name, u.last_name, u.email, u.initials, r.role_id AS editor_role_id FROM articles a, edit_assignments e, users u, roles r WHERE r.user_id = e.editor_id AND r.role_id = ' . ROLE_ID_EDITOR . ' AND e.article_id = ? AND r.journal_id = a.journal_id AND a.article_id = e.article_id AND e.editor_id = u.user_id ORDER BY e.date_notified ASC',
            [(int) $articleId]
        );

        return new DAOResultFactory($result, $this, '_returnEditAssignmentFromRow');
    }

    /**
     * Retrieve those edit assignments that relate to section editors with review access.
     * @param int $articleId
     * @return DAOResultFactory
     */
    public function getReviewingSectionEditorAssignmentsByArticleId($articleId) {
        $result = $this->retrieve(
            'SELECT e.*, u.first_name, u.last_name, u.email, u.initials, r.role_id AS editor_role_id FROM articles a LEFT JOIN edit_assignments e ON (a.article_id = e.article_id) LEFT JOIN users u ON (e.editor_id = u.user_id) LEFT JOIN roles r ON (r.user_id = e.editor_id AND r.role_id = ' . ROLE_ID_EDITOR . ' AND r.journal_id = a.journal_id) WHERE e.article_id = ? AND a.article_id = e.article_id AND r.role_id IS NULL AND e.can_review = 1 ORDER BY e.date_notified ASC',
            [(int) $articleId]
        );

        return new DAOResultFactory($result, $this, '_returnEditAssignmentFromRow');
    }

    /**
     * Retrieve those edit assignments that relate to section editors with editing access.
     * @param int $articleId
     * @return DAOResultFactory
     */
    public function getEditingSectionEditorAssignmentsByArticleId($articleId) {
        $result = $this->retrieve(
            'SELECT e.*, u.first_name, u.last_name, u.email, u.initials, r.role_id AS editor_role_id FROM articles a LEFT JOIN edit_assignments e ON (a.article_id = e.article_id) LEFT JOIN users u ON (e.editor_id = u.user_id) LEFT JOIN roles r ON (r.user_id = e.editor_id AND r.role_id = ' . ROLE_ID_EDITOR . ' AND r.journal_id = a.journal_id) WHERE e.article_id = ? AND a.article_id = e.article_id AND r.role_id IS NULL AND e.can_edit = 1 ORDER BY e.date_notified ASC',
            [(int) $articleId]
        );

        return new DAOResultFactory($result, $this, '_returnEditAssignmentFromRow');
    }

    /**
     * Retrieve edit assignments by user id.
     * @param int $userId
     * @return DAOResultFactory
     */
    public function getEditAssignmentsByUserId($userId) {
        $result = $this->retrieve(
            'SELECT e.*, u.first_name, u.last_name, u.email, u.initials, r.role_id AS editor_role_id FROM articles a LEFT JOIN edit_assignments e ON (a.article_id = e.article_id) LEFT JOIN users u ON (e.editor_id = u.user_id) LEFT JOIN roles r ON (r.user_id = e.editor_id AND r.role_id = ' . ROLE_ID_EDITOR . ' AND r.journal_id = a.journal_id) WHERE e.editor_id = ? AND a.article_id = e.article_id ORDER BY e.date_notified ASC',
            [(int) $userId]
        );

        return new DAOResultFactory($result, $this, '_returnEditAssignmentFromRow');
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return EditAssignment
     */
    public function newDataObject() {
        return new EditAssignment();
    }

    /**
     * Internal function to return an edit assignment object from a row.
     * @param array $row
     * @return EditAssignment
     */
    public function _returnEditAssignmentFromRow($row) {
        $editAssignment = $this->newDataObject();
        $editAssignment->setEditId((int) $row['edit_id']);
        $editAssignment->setArticleId((int) $row['article_id']);
        $editAssignment->setEditorId((int) $row['editor_id']);
        $editAssignment->setCanReview((int) $row['can_review']);
        $editAssignment->setCanEdit((int) $row['can_edit']);
        $editAssignment->setEditorFullName(trim((string) $row['first_name'] . ' ' . (string) $row['last_name']));
        $editAssignment->setEditorFirstName((string) $row['first_name']);
        $editAssignment->setEditorLastName((string) $row['last_name']);
        $editAssignment->setEditorInitials((string) $row['initials']);
        $editAssignment->setEditorEmail((string) $row['email']);
        $editAssignment->setIsEditor(((int) $row['editor_role_id']) === ROLE_ID_EDITOR ? 1 : 0);
        $editAssignment->setDateUnderway($this->datetimeFromDB($row['date_underway']));
        $editAssignment->setDateNotified($this->datetimeFromDB($row['date_notified']));
        $editAssignment->setDateAssigned($this->datetimeFromDB($row['date_assigned']));

        HookRegistry::dispatch('EditAssignmentDAO::_returnEditAssignmentFromRow', [&$editAssignment, &$row]);

        return $editAssignment;
    }

    /**
     * Insert a new EditAssignment.
     * @param EditAssignment $editAssignment
     * @return int
     */
    public function insertEditAssignment($editAssignment) {
        if (!($editAssignment instanceof EditAssignment)) {
            return 0;
        }

        $this->update(
            sprintf('INSERT INTO edit_assignments
                (article_id, editor_id, can_edit, can_review, date_assigned, date_notified, date_underway)
                VALUES
                (?, ?, ?, ?, %s, %s, %s)',
                $this->datetimeToDB($editAssignment->getDateAssigned()),
                $this->datetimeToDB($editAssignment->getDateNotified()),
                $this->datetimeToDB($editAssignment->getDateUnderway())
            ),
            [
                (int) $editAssignment->getArticleId(),
                (int) $editAssignment->getEditorId(),
                $editAssignment->getCanEdit() ? 1 : 0,
                $editAssignment->getCanReview() ? 1 : 0
            ]
        );

        $editAssignment->setEditId((int) $this->getInsertEditId());
        return $editAssignment->getEditId();
    }

    /**
     * Update an existing edit assignment.
     * @param EditAssignment $editAssignment
     * @return bool
     */
    public function updateEditAssignment($editAssignment) {
        if (!($editAssignment instanceof EditAssignment)) {
            return false;
        }

        return $this->update(
            sprintf('UPDATE edit_assignments
                SET article_id = ?,
                    editor_id = ?,
                    can_review = ?,
                    can_edit = ?,
                    date_assigned = %s,                                
                    date_notified = %s,
                    date_underway = %s
                WHERE edit_id = ?',
                $this->datetimeToDB($editAssignment->getDateAssigned()),
                $this->datetimeToDB($editAssignment->getDateNotified()),
                $this->datetimeToDB($editAssignment->getDateUnderway())
            ),
            [
                (int) $editAssignment->getArticleId(),
                (int) $editAssignment->getEditorId(),
                $editAssignment->getCanReview() ? 1 : 0,
                $editAssignment->getCanEdit() ? 1 : 0,
                (int) $editAssignment->getEditId()
            ]
        );
    }

    /**
     * Delete edit assignment.
     * @param int $editId
     * @return bool
     */
    public function deleteEditAssignmentById($editId) {
        return $this->update(
            'DELETE FROM edit_assignments WHERE edit_id = ?',
            [(int) $editId]
        );
    }

    /**
     * Delete edit assignments by article.
     * @param int $articleId
     * @return bool
     */
    public function deleteEditAssignmentsByArticle($articleId) {
        return $this->update(
            'DELETE FROM edit_assignments WHERE article_id = ?',
            [(int) $articleId]
        );
    }

    /**
     * Get the ID of the last inserted edit assignment.
     * @return int
     */
    public function getInsertEditId() {
        return $this->getInsertId('edit_assignments', 'edit_id');
    }

    /**
     * Get the assignment counts and last assigned date for all editors in the given journal.
     * @param int $journalId
     * @return array
     */
    public function getEditorStatistics($journalId) {
        $statistics = [];

        // Get counts of completed submissions
        $result = $this->retrieve(
            'SELECT ea.editor_id, COUNT(ea.article_id) AS complete
            FROM edit_assignments ea, articles a
            WHERE ea.article_id = a.article_id AND a.journal_id = ? AND (
                a.status = ' . STATUS_ARCHIVED . ' OR
                a.status = ' . STATUS_PUBLISHED . ' OR
                a.status = ' . STATUS_DECLINED . '
            )
            GROUP BY ea.editor_id',
            [(int) $journalId]
        );

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $editorId = (int) $row['editor_id'];
            if (!isset($statistics[$editorId])) {
                $statistics[$editorId] = [];
            }
            $statistics[$editorId]['complete'] = (int) $row['complete'];
            $result->MoveNext();
        }
        $result->Close();

        // Get counts of incomplete submissions
        $result = $this->retrieve(
            'SELECT ea.editor_id, COUNT(ea.article_id) AS incomplete
            FROM edit_assignments ea, articles a
            WHERE ea.article_id = a.article_id AND a.journal_id = ? AND a.status = ' . STATUS_QUEUED . '
            GROUP BY ea.editor_id',
            [(int) $journalId]
        );

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $editorId = (int) $row['editor_id'];
            if (!isset($statistics[$editorId])) {
                $statistics[$editorId] = [];
            }
            $statistics[$editorId]['incomplete'] = (int) $row['incomplete'];
            $result->MoveNext();
        }
        $result->Close();

        return $statistics;
    }
    
    /**
     * [MOD FORK] Mengambil data User Editor lengkap untuk halaman artikel.
     * @param int|object $articleOrId
     * @return array
     */
    public function getEditorsWithDetails($articleOrId) {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $editorsData = [];
    
        $article = is_object($articleOrId) ? $articleOrId : $articleDao->getArticle((int) $articleOrId);
        if (!$article) {
            return $editorsData;
        }
    
        // MENENTUKAN FALLBACK BERDASARKAN HIERARKI ATOMIK
        $fallbackDate = $article->getRevisionDate()
            ?: $article->getAcceptedDate()
            ?: $article->getDatePublished()
            ?: $article->getDateStatusModified()
            ?: $article->getLastModified();
    
        $editAssignmentsIterator = $this->getEditAssignmentsByArticleId((int) $article->getId());
        
        while ($editAssignment = $editAssignmentsIterator->next()) {
            // Gunakan fallback jika date_notified kosong
            if (!$editAssignment->getDateNotified()) {
                $editAssignment->setData('dateNotified', $fallbackDate);
            }
    
            // Integritas: Tidak ada tanggal editorial = Tidak tampil
            if (!$editAssignment->getDateNotified()) {
                continue;
            }
    
            $editor = $userDao->getById((int) $editAssignment->getEditorId());
            if ($editor instanceof User) {
                $editAssignment->setData('editorUser', $editor);
                $editorsData[] = $editAssignment;
            }
        }
        return $editorsData;
    }

}
?>