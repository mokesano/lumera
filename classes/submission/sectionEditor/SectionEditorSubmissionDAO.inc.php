<?php
declare(strict_types=1);

/**
 * @file classes/submission/sectionEditor/SectionEditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * With contributions from:
 * - 2014 Instituto Nacional de Investigacion y Tecnologia Agraria y Alimentaria
 *
 * @class SectionEditorSubmissionDAO
 * @ingroup submission
 * @see SectionEditorSubmission
 *
 * @brief Operations for retrieving and modifying SectionEditorSubmission objects.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & HookRegistry::dispatch
 */

import('classes.submission.sectionEditor.SectionEditorSubmission');

// Bring in editor decision constants
import('classes.submission.author.AuthorSubmission');
import('classes.submission.common.Action');
import('classes.submission.reviewer.ReviewerSubmission');

class SectionEditorSubmissionDAO extends DAO {
    // [WIZDAM] Properties initialized to null for cPanel linter compatibility
    public $articleDao = null;
    public $authorDao = null;
    public $userDao = null;
    public $editAssignmentDao = null;
    public $reviewAssignmentDao = null;
    public $copyeditorSubmissionDao = null;
    public $articleFileDao = null;
    public $suppFileDao = null;
    public $signoffDao = null;
    public $galleyDao = null;
    public $articleEmailLogDao = null;
    public $articleCommentDao = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->authorDao = DAORegistry::getDAO('AuthorDAO');
        $this->userDao = DAORegistry::getDAO('UserDAO');
        $this->editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $this->reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $this->copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
        $this->articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $this->suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $this->signoffDao = DAORegistry::getDAO('SignoffDAO');
        $this->galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $this->articleEmailLogDao = DAORegistry::getDAO('ArticleEmailLogDAO');
        $this->articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SectionEditorSubmissionDAO() {
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
     * Retrieve a section editor submission by article ID.
     * @param int $articleId
     * @return SectionEditorSubmission|null
     */
    public function getSectionEditorSubmission($articleId) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        $result = $this->retrieve(
            'SELECT	a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
                r2.review_revision
            FROM	articles a
                LEFT JOIN sections s ON (s.section_id = a.section_id)
                LEFT JOIN review_rounds r2 ON (a.article_id = r2.submission_id AND a.current_round = r2.round)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE	a.article_id = ?',
            [
                'title',
                $primaryLocale,
                'title',
                $locale,
                'abbrev',
                $primaryLocale,
                'abbrev',
                $locale,
                (int) $articleId
            ]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->_returnSectionEditorSubmissionFromRow($row);
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Internal function to return a SectionEditorSubmission object from a row.
     * @param array $row
     * @return SectionEditorSubmission
     */
    public function _returnSectionEditorSubmissionFromRow($row) {
        $sectionEditorSubmission = new SectionEditorSubmission();

        // Article attributes
        $this->articleDao->_articleFromRow($sectionEditorSubmission, $row);

        // Editor Assignment
        $editAssignments = $this->editAssignmentDao->getEditAssignmentsByArticleId($row['article_id']);
        $sectionEditorSubmission->setEditAssignments($editAssignments->toArray());

        // Editor Decisions
        for ($i = 1; $i <= $row['current_round']; $i++) {
            $sectionEditorSubmission->setDecisions($this->getEditorDecisions($row['article_id'], $i), $i);
        }

        // Comments
        $sectionEditorSubmission->setMostRecentEditorDecisionComment($this->articleCommentDao->getMostRecentArticleComment($row['article_id'], COMMENT_TYPE_EDITOR_DECISION, $row['article_id']));
        $sectionEditorSubmission->setMostRecentCopyeditComment($this->articleCommentDao->getMostRecentArticleComment($row['article_id'], COMMENT_TYPE_COPYEDIT, $row['article_id']));
        $sectionEditorSubmission->setMostRecentLayoutComment($this->articleCommentDao->getMostRecentArticleComment($row['article_id'], COMMENT_TYPE_LAYOUT, $row['article_id']));
        $sectionEditorSubmission->setMostRecentProofreadComment($this->articleCommentDao->getMostRecentArticleComment($row['article_id'], COMMENT_TYPE_PROOFREAD, $row['article_id']));

        // Files
        $sectionEditorSubmission->setSubmissionFile($this->articleFileDao->getArticleFile($row['submission_file_id']));
        $sectionEditorSubmission->setRevisedFile($this->articleFileDao->getArticleFile($row['revised_file_id']));
        $sectionEditorSubmission->setReviewFile($this->articleFileDao->getArticleFile($row['review_file_id']));
        $sectionEditorSubmission->setSuppFiles($this->suppFileDao->getSuppFilesByArticle($row['article_id']));
        $sectionEditorSubmission->setEditorFile($this->articleFileDao->getArticleFile($row['editor_file_id']));


        for ($i = 1; $i <= $row['current_round']; $i++) {
            $sectionEditorSubmission->setEditorFileRevisions($this->articleFileDao->getArticleFileRevisions($row['editor_file_id'], $i), $i);
            $sectionEditorSubmission->setAuthorFileRevisions($this->articleFileDao->getArticleFileRevisions($row['revised_file_id'], $i), $i);
        }

        // Review Rounds
        $sectionEditorSubmission->setReviewRevision($row['review_revision']);

        // Review Assignments
        for ($i = 1; $i <= $row['current_round']; $i++) {
            $sectionEditorSubmission->setReviewAssignments($this->reviewAssignmentDao->getBySubmissionId($row['article_id'], $i), $i);
        }

        // Layout Editing
        $sectionEditorSubmission->setGalleys($this->galleyDao->getGalleysByArticle($row['article_id']));

        // [WIZDAM] HookRegistry::dispatch - Keep & for hooks
        HookRegistry::dispatch('SectionEditorSubmissionDAO::_returnSectionEditorSubmissionFromRow', [&$sectionEditorSubmission, &$row]);

        return $sectionEditorSubmission;
    }

    /**
     * Update an existing section editor submission.
     * @param SectionEditorSubmission $sectionEditorSubmission
     */
    public function updateSectionEditorSubmission($sectionEditorSubmission) {
        // [WIZDAM] Type Guard
        if (!($sectionEditorSubmission instanceof SectionEditorSubmission)) {
            return;
        }

        // update edit assignment
        $editAssignments = $sectionEditorSubmission->getEditAssignments();
        foreach ($editAssignments as $editAssignment) {
            if ($editAssignment->getEditId() > 0) {
                $this->editAssignmentDao->updateEditAssignment($editAssignment);
            } else {
                $this->editAssignmentDao->insertEditAssignment($editAssignment);
            }
        }

        // Update editor decisions
        for ($i = 1; $i <= $sectionEditorSubmission->getCurrentRound(); $i++) {
            $editorDecisions = $sectionEditorSubmission->getDecisions($i);
            if (is_array($editorDecisions)) {
                foreach ($editorDecisions as $key => $editorDecision) {
                    if ($editorDecision['editDecisionId'] == null) {
                        $this->update(
                            sprintf('INSERT INTO edit_decisions
                                (article_id, round, editor_id, decision, date_decided)
                                VALUES (?, ?, ?, ?, %s)',
                                $this->datetimeToDB($editorDecision['dateDecided'])),
                            [
                                (int) $sectionEditorSubmission->getId(),
                                (int) $sectionEditorSubmission->getCurrentRound(),
                                (int) $editorDecision['editorId'],
                                $editorDecision['decision']
                            ]
                        );
                        $editorDecisions[$key]['editDecisionId'] = $this->getInsertId('edit_decisions', 'edit_decision_id');
                    }
                }
            }
            unset($editorDecisions);
        }
        if ($this->reviewRoundExists($sectionEditorSubmission->getId(), $sectionEditorSubmission->getCurrentRound())) {
            $this->update(
                'UPDATE review_rounds
                    SET
                        review_revision = ?
                    WHERE submission_id = ? AND round = ?',
                [
                    (int) $sectionEditorSubmission->getReviewRevision(),
                    (int) $sectionEditorSubmission->getId(),
                    (int) $sectionEditorSubmission->getCurrentRound()
                ]
            );
        } elseif ($sectionEditorSubmission->getReviewRevision() != null) {
            $this->createReviewRound(
                $sectionEditorSubmission->getId(),
                $sectionEditorSubmission->getCurrentRound() === null ? 1 : $sectionEditorSubmission->getCurrentRound(),
                $sectionEditorSubmission->getReviewRevision()
            );
        }

        // Update copyeditor assignment
        $copyeditSignoff = $this->signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
        if ($copyeditSignoff) {
            $copyeditorSubmission = $this->copyeditorSubmissionDao->getCopyeditorSubmission($sectionEditorSubmission->getId());
        } else {
            $copyeditorSubmission = new CopyeditorSubmission();
        }

        // Only update the fields that an editor can modify.
        $copyeditorSubmission->setId($sectionEditorSubmission->getId());
        $copyeditorSubmission->setDateStatusModified($sectionEditorSubmission->getDateStatusModified());
        $copyeditorSubmission->setLastModified($sectionEditorSubmission->getLastModified());

        // update review assignments
        foreach ($sectionEditorSubmission->getReviewAssignments() as $roundReviewAssignments) {
            foreach ($roundReviewAssignments as $reviewAssignment) {
                if ($reviewAssignment->getId() > 0) {
                    $this->reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
                } else {
                    $this->reviewAssignmentDao->insertObject($reviewAssignment);
                }
            }
        }

        // Remove deleted review assignments
        $removedReviewAssignments = $sectionEditorSubmission->getRemovedReviewAssignments();
        for ($i=0, $count=count($removedReviewAssignments); $i < $count; $i++) {
            $this->reviewAssignmentDao->deleteById($removedReviewAssignments[$i]);
        }

        // Update article
        if ($sectionEditorSubmission->getId()) {

            $article = $this->articleDao->getArticle($sectionEditorSubmission->getId());

            // Only update fields that can actually be edited.
            $article->setSectionId($sectionEditorSubmission->getSectionId());
            $article->setCurrentRound($sectionEditorSubmission->getCurrentRound());
            $article->setReviewFileId($sectionEditorSubmission->getReviewFileId());
            $article->setEditorFileId($sectionEditorSubmission->getEditorFileId());
            $article->setStatus($sectionEditorSubmission->getStatus());
            $article->setDateStatusModified($sectionEditorSubmission->getDateStatusModified());
            $article->setLastModified($sectionEditorSubmission->getLastModified());
            $article->setCommentsStatus($sectionEditorSubmission->getCommentsStatus());

            $this->articleDao->updateArticle($article);
        }
    }

    /**
     * Create review round
     * @param int $articleId
     * @param int $round
     * @param int $reviewRevision
     */
    public function createReviewRound($articleId, $round, $reviewRevision) {
        $this->update(
            'INSERT INTO review_rounds
                (submission_id, round, review_revision)
                VALUES
                (?, ?, ?)',
            [(int) $articleId, (int) $round, (int) $reviewRevision]
        );
    }

    /**
     * Get all section editor submissions for a section editor.
     * @param int $sectionEditorId
     * @param int $journalId
     * @param boolean $status true if active, false if completed.
     * @return array SectionEditorSubmission
     */
    public function getSectionEditorSubmissions($sectionEditorId, $journalId, $status = true) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();

        $sectionEditorSubmissions = [];

        $result = $this->retrieve(
            'SELECT	a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
                r2.review_revision
            FROM	articles a
                LEFT JOIN edit_assignments e ON (e.article_id = a.article_id)
                LEFT JOIN sections s ON (s.section_id = a.section_id)
                LEFT JOIN review_rounds r2 ON (a.article_id = r2.submission_id AND a.current_round = r2.round)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE	a.journal_id = ?
                AND e.editor_id = ?
                AND a.status = ?',
            [
                'title',
                $primaryLocale,
                'title',
                $locale,
                'abbrev',
                $primaryLocale,
                'abbrev',
                $locale,
                (int) $journalId,
                (int) $sectionEditorId,
                $status
            ]
        );

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $sectionEditorSubmissions[] = $this->_returnSectionEditorSubmissionFromRow($row);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $sectionEditorSubmissions;
    }

    /**
     * Retrieve unfiltered section editor submissions
     * @param int $sectionEditorId
     * @param int $journalId
     * @param int $sectionId
     * @param int|null $searchField
     * @param string|null $searchMatch
     * @param string|null $search
     * @param int|null $dateField
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param string $additionalWhereSql
     * @param object|null $rangeInfo
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory|ItemIterator
     */
    public function _getUnfilteredSectionEditorSubmissions($sectionEditorId, $journalId, $sectionId = 0, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $additionalWhereSql = '', $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();

        $params = [
            ASSOC_TYPE_ARTICLE,
            'SIGNOFF_COPYEDITING_FINAL',
            ASSOC_TYPE_ARTICLE,
            'SIGNOFF_PROOFREADING_PROOFREADER',
            ASSOC_TYPE_ARTICLE,
            'SIGNOFF_LAYOUT',
            'title', // Section title
            $primaryLocale,
            'title',
            $locale,
            'abbrev', // Section abbrev
            $primaryLocale,
            'abbrev',
            $locale,
            'cleanTitle', // Article title
            'cleanTitle',
            $locale,
            'title', // Article title
            'title',
            $locale,
            (int) $journalId,
            (int) $sectionEditorId
        ];

        $searchSql = '';

        if (!empty($search)) switch ($searchField) {
            case SUBMISSION_FIELD_ID:
                switch ($searchMatch) {
                    case 'is':
                        $params[] = (int) $search;
                        $searchSql = ' AND a.article_id = ?';
                        break;
                    case 'contains':
                        $search = '%' . $search . '%';
                        $params[] = $search;
                        $searchSql = ' AND CONCAT(a.article_id) LIKE ?';
                        break;
                    case 'startsWith':
                        $search = $search . '%';
                        $params[] = $search;
                        $searchSql = 'AND CONCAT(a.article_id) LIKE ?';
                        break;
                }
                break;
            case SUBMISSION_FIELD_TITLE:
                if ($searchMatch === 'is') {
                    $searchSql = ' AND LOWER(atl.setting_value) = LOWER(?)';
                } elseif ($searchMatch === 'contains') {
                    $searchSql = ' AND LOWER(atl.setting_value) LIKE LOWER(?)';
                    $search = '%' . $search . '%';
                } else { // $searchMatch === 'startsWith'
                    $searchSql = ' AND LOWER(atl.setting_value) LIKE LOWER(?)';
                    $search = '%' . $search . '%';
                }
                $params[] = $search;
                break;
            case SUBMISSION_FIELD_AUTHOR:
                $searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'aa.', $params);
                break;
            case SUBMISSION_FIELD_EDITOR:
                $searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'ed.', $params);
                break;
        }

        if (!empty($dateFrom) || !empty($dateTo)) switch($dateField) {
            case SUBMISSION_FIELD_DATE_SUBMITTED:
                if (!empty($dateFrom)) {
                    $searchSql .= ' AND a.date_submitted >= ' . $this->datetimeToDB($dateFrom);
                }
                if (!empty($dateTo)) {
                    $searchSql .= ' AND a.date_submitted <= ' . $this->datetimeToDB($dateTo);
                }
                break;
            case SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE:
                if (!empty($dateFrom)) {
                    $searchSql .= ' AND c.date_final_completed >= ' . $this->datetimeToDB($dateFrom);
                }
                if (!empty($dateTo)) {
                    $searchSql .= ' AND c.date_final_completed <= ' . $this->datetimeToDB($dateTo);
                }
                break;
            case SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE:
                if (!empty($dateFrom)) {
                    $searchSql .= ' AND l.date_completed >= ' . $this->datetimeToDB($dateFrom);
                }
                if (!empty($dateTo)) {
                    $searchSql .= ' AND l.date_completed <= ' . $this->datetimeToDB($dateTo);
                }
                break;
            case SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE:
                if (!empty($dateFrom)) {
                    $searchSql .= ' AND p.date_proofreader_completed >= ' . $this->datetimeToDB($dateFrom);
                }
                if (!empty($dateTo)) {
                    $searchSql .= ' AND p.date_proofreader_completed <= ' . $this->datetimeToDB($dateTo);
                }
                break;
        }

        $sql = 'SELECT DISTINCT
                a.*,
                scf.date_completed as copyedit_completed,
                spr.date_completed as proofread_completed,
                sle.date_completed as layout_completed,
                SUBSTRING(COALESCE(actl.setting_value, actpl.setting_value) FROM 1 FOR 255) AS submission_clean_title,
                aap.last_name AS author_name,
                e.can_review AS can_review,
                e.can_edit AS can_edit,
                SUBSTRING(COALESCE(stl.setting_value, stpl.setting_value) FROM 1 FOR 255) AS section_title,
                SUBSTRING(COALESCE(sal.setting_value, sapl.setting_value) FROM 1 FOR 255) AS section_abbrev,
                r2.review_revision
            FROM	articles a
                LEFT JOIN authors aa ON (aa.submission_id = a.article_id)
                LEFT JOIN authors aap ON (aap.submission_id = a.article_id AND aap.primary_contact = 1)
                LEFT JOIN edit_assignments e ON (e.article_id = a.article_id)
                LEFT JOIN users ed ON (e.editor_id = ed.user_id)
                LEFT JOIN sections s ON (s.section_id = a.section_id)
                LEFT JOIN signoffs scf ON (a.article_id = scf.assoc_id AND scf.assoc_type = ? AND scf.symbolic = ?)
                LEFT JOIN users ce ON (scf.user_id = ce.user_id)
                LEFT JOIN signoffs spr ON (a.article_id = spr.assoc_id AND spr.assoc_type = ? AND spr.symbolic = ?)
                LEFT JOIN users pe ON (pe.user_id = spr.user_id)
                LEFT JOIN review_rounds r2 ON (a.article_id = r2.submission_id and a.current_round = r2.round)
                LEFT JOIN signoffs sle ON (a.article_id = sle.assoc_id AND sle.assoc_type = ? AND sle.symbolic = ?) LEFT JOIN users le ON (le.user_id = sle.user_id)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
                LEFT JOIN article_settings actpl ON (actpl.article_id = a.article_id AND actpl.setting_name = ? AND actpl.locale = a.locale)
                LEFT JOIN article_settings actl ON (a.article_id = actl.article_id AND actl.setting_name = ? AND actl.locale = ?)
                LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = ? AND atpl.locale = a.locale)
                LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name = ? AND atl.locale = ?)
                LEFT JOIN edit_decisions edec ON (a.article_id = edec.article_id)
                LEFT JOIN edit_decisions edec2 ON (a.article_id = edec2.article_id AND edec.edit_decision_id < edec2.edit_decision_id)
            WHERE	a.journal_id = ?
                AND e.editor_id = ?
                AND a.submission_progress = 0' . (!empty($additionalWhereSql) ? " AND ($additionalWhereSql)" : '') . '
                AND edec2.edit_decision_id IS NULL';

        if ($sectionId) {
            $params[] = (int) $sectionId;
            $searchSql .= ' AND a.section_id = ?';
        }

        $result = $this->retrieveRange($sql . ' ' . $searchSql . ($sortBy ? (' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
            $params,
            $rangeInfo
        );

        // [WIZDAM FIX] Avoid creating a Factory if retrieveRange already returned one
        if ($result instanceof DAOResultFactory) {
            $result->setFunction([$this, '_returnSectionEditorSubmissionFromRow']);
            return $result;
        }

        return new DAOResultFactory($result, $this, '_returnSectionEditorSubmissionFromRow');
    }

    /**
     * FIXME Move this into somewhere common (SubmissionDAO?) as this is used in several classes.
     * @param string $search
     * @param string $searchMatch
     * @param string $prefix
     * @param array $params (Accumulator, passed by reference)
     * @return string
     */
    public function _generateUserNameSearchSQL($search, $searchMatch, $prefix, &$params) {
        $first_last = $this->concat($prefix.'first_name', '\' \'', $prefix.'last_name');
        $first_middle_last = $this->concat($prefix.'first_name', '\' \'', $prefix.'middle_name', '\' \'', $prefix.'last_name');
        $last_comma_first = $this->concat($prefix.'last_name', '\', \'', $prefix.'first_name');
        $last_comma_first_middle = $this->concat($prefix.'last_name', '\', \'', $prefix.'first_name', '\' \'', $prefix.'middle_name');
        if ($searchMatch === 'is') {
            $searchSql = " AND (LOWER({$prefix}last_name) = LOWER(?) OR LOWER($first_last) = LOWER(?) OR LOWER($first_middle_last) = LOWER(?) OR LOWER($last_comma_first) = LOWER(?) OR LOWER($last_comma_first_middle) = LOWER(?))";
        } elseif ($searchMatch === 'contains') {
            $searchSql = " AND (LOWER({$prefix}last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
            $search = '%' . $search . '%';
        } else { // $searchMatch === 'startsWith'
            $searchSql = " AND (LOWER({$prefix}last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
            $search = $search . '%';
        }
        
        // [WIZDAM] Explicit push
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        
        return $searchSql;
    }

    /**
     * Get all submissions in review for a journal.
     * [WIZDAM EDITION] Double Wrapping Fix
     * 
     * @param int $sectionEditorId
     * @param int $journalId
     * @param int $sectionId
     * @param int|null $searchField Symbolic SUBMISSION_FIELD_... identifier
     * @param string|null $searchMatch "is" or "contains" or "startsWith"
     * @param string|null $search String to look in $searchField for
     * @param int|null $dateField Symbolic SUBMISSION_FIELD_DATE_... identifier
     * @param string|null $dateFrom String date to search from
     * @param string|null $dateTo String date to search to
     * @param object|null $rangeInfo
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory|ItemIterator
     */
    public function getSectionEditorSubmissionsInReview($sectionEditorId, $journalId, $sectionId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $result = $this->_getUnfilteredSectionEditorSubmissions(
            $sectionEditorId, $journalId, $sectionId,
            $searchField, $searchMatch, $search,
            $dateField, $dateFrom, $dateTo,
            'a.status = ' . STATUS_QUEUED . ' AND e.can_review = 1 AND (edec.decision IS NULL OR edec.decision <> ' . SUBMISSION_EDITOR_DECISION_ACCEPT . ')',
            $rangeInfo, $sortBy, $sortDirection
        );

        // [WIZDAM FIX] If result is already a Factory, return it directly.
        if ($result instanceof DAOResultFactory) {
            return $result;
        }

        return new DAOResultFactory($result, $this, '_returnSectionEditorSubmissionFromRow');
    }

    /**
     * Get all submissions in editing for a journal.
     * [WIZDAM EDITION] Double Wrapping Fix
     * 
     * @param int $sectionEditorId
     * @param int $journalId
     * @param int $sectionId
     * @param int|null $searchField Symbolic SUBMISSION_FIELD_... identifier
     * @param string|null $searchMatch "is" or "contains" or "startsWith"
     * @param string|null $search String to look in $searchField for
     * @param int|null $dateField Symbolic SUBMISSION_FIELD_DATE_... identifier
     * @param string|null $dateFrom String date to search from
     * @param string|null $dateTo String date to search to
     * @param object|null $rangeInfo
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory|ItemIterator
     */
    public function getSectionEditorSubmissionsInEditing($sectionEditorId, $journalId, $sectionId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $result = $this->_getUnfilteredSectionEditorSubmissions(
            $sectionEditorId, $journalId, $sectionId,
            $searchField, $searchMatch, $search,
            $dateField, $dateFrom, $dateTo,
            'a.status = ' . STATUS_QUEUED . ' AND e.can_edit = 1 AND edec.decision = ' . SUBMISSION_EDITOR_DECISION_ACCEPT,
            $rangeInfo, $sortBy, $sortDirection
        );

        // [WIZDAM FIX] If result is already a Factory, return it directly.
        if ($result instanceof DAOResultFactory) {
            return $result;
        }

        return new DAOResultFactory($result, $this, '_returnSectionEditorSubmissionFromRow');
    }

    /**
     * Get all submissions in archives for a journal.
     * [WIZDAM EDITION] Double Wrapping Fix
     * 
     * @param int $sectionEditorId
     * @param int $journalId
     * @param int $sectionId
     * @param int|null $searchField Symbolic SUBMISSION_FIELD_... identifier
     * @param string|null $searchMatch "is" or "contains" or "startsWith"
     * @param string|null $search String to look in $searchField for
     * @param int|null $dateField Symbolic SUBMISSION_FIELD_DATE_... identifier
     * @param string|null $dateFrom String date to search from
     * @param string|null $dateTo String date to search to
     * @param object|null $rangeInfo
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory|ItemIterator
     */
    public function getSectionEditorSubmissionsArchives($sectionEditorId, $journalId, $sectionId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $result = $this->_getUnfilteredSectionEditorSubmissions(
            $sectionEditorId, $journalId, $sectionId,
            $searchField, $searchMatch, $search,
            $dateField, $dateFrom, $dateTo,
            'a.status <> ' . STATUS_QUEUED,
            $rangeInfo, $sortBy, $sortDirection
        );

        // [WIZDAM FIX] If result is already a Factory, return it directly.
        if ($result instanceof DAOResultFactory) {
            return $result;
        }

        return new DAOResultFactory($result, $this, '_returnSectionEditorSubmissionFromRow');
    }

    /**
     * Function used for counting purposes for right nav bar
     * @param int $sectionEditorId
     * @param int $journalId
     * @return array
     */
    public function getSectionEditorSubmissionsCount($sectionEditorId, $journalId) {
        $submissionsCount = [];
        for($i = 0; $i < 2; $i++) {
            $submissionsCount[$i] = 0;
        }

        // Fetch a count of submissions in review.
        // "d2" and "d" are used to fetch the single most recent editor decision.
        $result = $this->retrieve(
            'SELECT	COUNT(*) AS review_count
            FROM	articles a
                LEFT JOIN edit_assignments e ON (a.article_id = e.article_id)
                LEFT JOIN edit_assignments e2 ON (a.article_id = e2.article_id AND e.edit_id < e2.edit_id AND e2.editor_id=?)
                LEFT JOIN edit_decisions d ON (a.article_id = d.article_id)
                LEFT JOIN edit_decisions d2 ON (a.article_id = d2.article_id AND d.edit_decision_id < d2.edit_decision_id)
            WHERE	a.journal_id = ?
                AND e.editor_id = ?
                AND e2.edit_id IS NULL
                AND a.submission_progress = 0
                AND a.status = ' . STATUS_QUEUED . '
                AND d2.edit_decision_id IS NULL
                AND (d.decision IS NULL OR d.decision <> ' . SUBMISSION_EDITOR_DECISION_ACCEPT . ')',
            [(int) $sectionEditorId, (int) $journalId, (int) $sectionEditorId]
        );
        $submissionsCount[0] = $result->Fields('review_count');
        $result->Close();

        // Fetch a count of submissions in editing.
        // "d2" and "d" are used to fetch the single most recent editor decision.
        $result = $this->retrieve(
            'SELECT	COUNT(*) AS editing_count
            FROM	articles a
                LEFT JOIN edit_assignments e ON (a.article_id = e.article_id)
                LEFT JOIN edit_assignments e2 ON (a.article_id = e2.article_id AND e.edit_id < e2.edit_id AND e2.editor_id=?)
                LEFT JOIN edit_decisions d ON (a.article_id = d.article_id)
                LEFT JOIN edit_decisions d2 ON (a.article_id = d2.article_id AND d.edit_decision_id < d2.edit_decision_id)
            WHERE	a.journal_id = ?
                AND e.editor_id = ?
                AND e2.edit_id IS NULL
                AND a.submission_progress = 0
                AND a.status = ' . STATUS_QUEUED . '
                AND d2.edit_decision_id IS NULL
                AND d.decision = ' . SUBMISSION_EDITOR_DECISION_ACCEPT,
            [(int) $sectionEditorId, (int) $journalId, (int) $sectionEditorId]
        );
        $submissionsCount[1] = $result->Fields('editing_count');
        $result->Close();
        return $submissionsCount;
    }

    //
    // Miscellaneous
    //

    /**
     * Delete copyediting assignments by article.
     * @param int $articleId
     * @return boolean
     */
    public function deleteDecisionsByArticle($articleId) {
        return $this->update(
            'DELETE FROM edit_decisions WHERE article_id = ?',
            [(int) $articleId]
        );
    }

    /**
     * Delete review rounds article.
     * @param int $articleId
     * @return boolean
     */
    public function deleteReviewRoundsByArticle($articleId) {
        return $this->update(
            'DELETE FROM review_rounds WHERE submission_id = ?',
            [(int) $articleId]
        );
    }

    /**
     * Get the editor decisions for a review round of an article.
     * @param int $articleId
     * @param int|null $round
     * @return array
     */
    public function getEditorDecisions($articleId, $round = null) {
        $decisions = [];

        if ($round == null) {
            $result = $this->retrieve(
                'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE article_id = ? ORDER BY edit_decision_id ASC', 
                [(int) $articleId]
            );
        } else {
            $result = $this->retrieve(
                'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE article_id = ? AND round = ? ORDER BY edit_decision_id ASC',
                [(int) $articleId, (int) $round]
            );
        }

        while (!$result->EOF) {
            $decisions[] = [
                'editDecisionId' => $result->fields['edit_decision_id'],
                'editorId' => $result->fields['editor_id'],
                'decision' => $result->fields['decision'],
                'dateDecided' => $this->datetimeFromDB($result->fields['date_decided'])
            ];
            $result->moveNext();
        }
        $result->Close();
        unset($result);

        return $decisions;
    }

    /**
     * Get the highest review round.
     * @param int $articleId
     * @return int
     */
    public function getMaxReviewRound($articleId) {
        $result = $this->retrieve(
            'SELECT MAX(round) FROM review_rounds WHERE submission_id = ?', 
            [(int) $articleId]
        );
        $returner = isset($result->fields[0]) ? (int) $result->fields[0] : 0;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Check if a review round exists for a specified article.
     * @param int $articleId
     * @param int $round
     * @return boolean
     */
    public function reviewRoundExists($articleId, $round) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM review_rounds WHERE submission_id = ? AND round = ?', 
            [(int) $articleId, (int) $round]
        );
        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Check if a reviewer is assigned to a specified article.
     * @param int $articleId
     * @param int $reviewerId
     * @param int $round
     * @return boolean
     */
    public function reviewerExists($articleId, $reviewerId, $round) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM review_assignments WHERE submission_id = ? AND reviewer_id = ? AND round = ? AND cancelled = 0', 
            [(int) $articleId, (int) $reviewerId, (int) $round]
        );
        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve a list of all reviewers with respect to an article's current round.
     * @param int $journalId
     * @param int $articleId
     * @param int $round
     * @param int|null $searchType USER_FIELD_...
     * @param string|null $search
     * @param string|null $searchMatch "is" or "contains" or "startsWith"
     * @param object|null $rangeInfo RangeInfo optional
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory|ItemIterator
     */
    public function getReviewersForArticle($journalId, $articleId, $round, $searchType = null, $search = null, $searchMatch = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        // Convert the field being searched for to a DB element to select on
        $searchTypeMap = [
            USER_FIELD_FIRSTNAME => 'u.first_name',
            USER_FIELD_LASTNAME => 'u.last_name',
            USER_FIELD_USERNAME => 'u.username',
            USER_FIELD_EMAIL => 'u.email',
            USER_FIELD_INTERESTS => 'cves.setting_value'
        ];

        // Generate the SQL used to filter the results based on what the user is searching for
        $paramArray = [(int) $articleId, (int) $round];
        $joinInterests = false;
        if($searchType == USER_FIELD_INTERESTS) {
            $joinInterests = true;
        }

        // Push some extra default parameters to the SQL parameter array
        $paramArray[] = (int) $journalId;
        $paramArray[] = ROLE_ID_REVIEWER;

        $searchSql = '';
        if (isset($search) && isset($searchTypeMap[$searchType])) {
            $fieldName = $searchTypeMap[$searchType];
            switch ($searchMatch) {
                case 'is':
                    $searchSql = "AND LOWER($fieldName) = LOWER(?)";
                    $paramArray[] = $search;
                    break;
                case 'contains':
                    $searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
                    $paramArray[] = '%' . $search . '%';
                    break;
                case 'startsWith':
                    $searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
                    $paramArray[] = $search . '%';
                    break;
            }
        } elseif (isset($search)) switch ($searchType) {
            case USER_FIELD_USERID:
                $searchSql = 'AND user_id=?';
                $paramArray[] = $search;
                break;
            case USER_FIELD_INITIAL:
                $searchSql = 'AND (LOWER(last_name) LIKE LOWER(?) OR LOWER(username) LIKE LOWER(?))';
                $paramArray[] = $search . '%';
                $paramArray[] = $search . '%';
                break;
        }

        $interestJoinSql = ($joinInterests ? '
                    LEFT JOIN user_interests ui ON (ui.user_id = u.user_id)
                    LEFT JOIN controlled_vocab_entry_settings cves ON (cves.controlled_vocab_entry_id = ui.controlled_vocab_entry_id) ':'');

        $result = $this->retrieveRange(
            'SELECT DISTINCT
                u.user_id,
                u.last_name,
                ar.review_id,
                ar.declined,
                (SELECT AVG(ra.quality) FROM review_assignments ra WHERE ra.reviewer_id = u.user_id) AS average_quality,
                (SELECT COUNT(ac.review_id) FROM review_assignments ac WHERE ac.reviewer_id = u.user_id AND ac.date_completed IS NOT NULL AND ac.cancelled = 0) AS completed,
                (SELECT COUNT(ac.review_id) FROM review_assignments ac, articles a WHERE
                    ac.reviewer_id = u.user_id AND
                    ac.submission_id = a.article_id AND
                    ac.date_notified IS NOT NULL AND
                    ac.date_completed IS NULL AND
                    ac.cancelled = 0 AND
                    ac.declined = 0 AND
                    a.status <> '.STATUS_QUEUED.') AS incomplete,
                (SELECT MAX(ac.date_notified) FROM review_assignments ac WHERE ac.reviewer_id = u.user_id AND ac.date_completed IS NOT NULL) AS latest,
                (SELECT AVG(ac.date_completed-ac.date_notified) FROM review_assignments ac WHERE ac.reviewer_id = u.user_id AND ac.date_completed IS NOT NULL AND ac.cancelled = 0) AS average
             FROM users u
                LEFT JOIN review_assignments ra ON (ra.reviewer_id = u.user_id)
                LEFT JOIN review_assignments ar ON (ar.reviewer_id = u.user_id AND ar.cancelled = 0 AND ar.submission_id = ? AND ar.round = ?)
                LEFT JOIN roles r ON (r.user_id = u.user_id)
                LEFT JOIN articles a ON (ra.submission_id = a.article_id)
                '.$interestJoinSql.'
                WHERE u.user_id = r.user_id AND
                r.journal_id = ? AND
                r.role_id = ? ' . $searchSql . 'GROUP BY u.user_id, u.last_name, ar.review_id, ar.declined' .
            ($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
            $paramArray, $rangeInfo
        );

        // [WIZDAM FIX] Double wrapping guard
        if ($result instanceof DAOResultFactory) {
            $result->setFunction([$this, '_returnReviewerUserFromRow']);
            return $result;
        }

        return new DAOResultFactory($result, $this, '_returnReviewerUserFromRow');
    }

    /**
     * Internal function to return a Reviewer User object from a row.
     * @param array $row
     * @return User
     */
    public function _returnReviewerUserFromRow($row) {
        $user = $this->userDao->getById($row['user_id']);
        $user->review_id = $row['review_id'];
        $user->declined = $row['declined'];

        HookRegistry::dispatch('SectionEditorSubmissionDAO::_returnReviewerUserFromRow', [&$user, &$row]);

        return $user;
    }

    /**
     * Retrieve a list of all reviewers not assigned to the specified article.
     * @param int $journalId
     * @param int $articleId
     * @return array matching Users
     */
    public function getReviewersNotAssignedToArticle($journalId, $articleId) {
        $users = [];

        $result = $this->retrieve(
            'SELECT	u.*
            FROM	users u
                LEFT JOIN roles r ON (r.user_id = u.user_id)
                LEFT JOIN review_assignments a ON (a.reviewer_id = u.user_id AND a.article_id = ?)
            WHERE	r.journal_id = ? AND
                r.role_id = ? AND
                a.article_id IS NULL
            ORDER BY last_name, first_name',
            [(int) $articleId, (int) $journalId, RoleDAO::getRoleIdFromPath('reviewer')]
        );

        while (!$result->EOF) {
            $users[] = $this->userDao->_returnUserFromRowWithData($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $users;
    }

    /**
     * Check if a copyeditor is assigned to a specified article.
     * @param int $articleId
     * @param int $copyeditorId
     * @return boolean
     */
    public function copyeditorExists($articleId, $copyeditorId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM signoffs WHERE assoc_id = ? AND assoc_type = ? AND user_id = ? AND symbolic = ?', 
            [(int) $articleId, ASSOC_TYPE_ARTICLE, (int) $copyeditorId, 'SIGNOFF_COPYEDITING_INITIAL']
        );
        return isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;
    }

    /**
     * Get the assignment counts and last assigned date for all layout editors of the given journal.
     * @param int $journalId
     * @param int $layoutEditorId Optional layout editor ID
     * @return array
     */
    public function getLayoutEditorStatistics($journalId, $layoutEditorId = null) {
        $statistics = [];

        // WARNING: This is reused for the next two queries
        $params = [
            (int) $journalId,
            'SIGNOFF_LAYOUT',
            'SIGNOFF_PROOFREADING_LAYOUT',
            ASSOC_TYPE_ARTICLE,
            ASSOC_TYPE_ARTICLE
        ];
        if ($layoutEditorId) $params[] = (int) $layoutEditorId;

        // Get counts of completed submissions
        $result = $this->retrieve(
            'SELECT	sl.user_id AS editor_id,
                COUNT(sl.assoc_id) AS complete
            FROM	signoffs sl,
                articles a
                INNER JOIN signoffs sp ON (sp.assoc_id = a.article_id)
            WHERE	sl.assoc_id = a.article_id AND
                a.status <> ' . STATUS_QUEUED . ' AND
                sl.date_notified IS NOT NULL AND
                a.journal_id = ? AND
                sl.symbolic = ? AND
                sp.symbolic = ? AND
                sl.assoc_type = ? AND
                sp.assoc_type = ?
                ' . ($layoutEditorId ? ' AND sl.user_id = ?' : '') . '
            GROUP BY sl.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['complete'] = $row['complete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get counts of incomplete submissions
        $result = $this->retrieve(
            'SELECT	sl.user_id AS editor_id,
                COUNT(sl.assoc_id) AS incomplete
            FROM	signoffs sl,
                articles a
                INNER JOIN signoffs sp ON (sp.assoc_id = a.article_id)
            WHERE	sl.assoc_id = a.article_id AND
                a.status = ' . STATUS_QUEUED . ' AND
                sl.date_notified IS NOT NULL AND
                a.journal_id = ? AND
                sl.symbolic = ? AND
                sp.symbolic = ? AND
                sl.assoc_type = ? AND
                sp.assoc_type = ?
                ' . ($layoutEditorId ? ' AND sl.user_id = ?' : '') . '
            GROUP BY sl.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['incomplete'] = $row['incomplete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get last assignment date
        $params = [
            (int) $journalId,
            'SIGNOFF_LAYOUT',
            ASSOC_TYPE_ARTICLE
        ];
        if ($layoutEditorId) $params[] = (int) $layoutEditorId;

        $result = $this->retrieve(
            'SELECT	sl.user_id AS editor_id,
                MAX(sl.date_notified) AS last_assigned
            FROM	signoffs sl,
                articles a
            WHERE	sl.assoc_id = a.article_id AND
                a.journal_id = ? AND
                sl.symbolic = ? AND
                sl.assoc_type = ?
                ' . ($layoutEditorId ? ' AND sl.user_id = ?' : '') . '
            GROUP BY sl.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['last_assigned'] = $this->datetimeFromDB($row['last_assigned']);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $statistics;
    }

    /**
     * Get the last assigned and last completed dates for all reviewers of the given journal.
     * @param int $journalId
     * @return array
     */
    public function getReviewerStatistics($journalId) {
        $statistics = [];

        // Get latest review request date
        $result = $this->retrieve(
            'SELECT	r.reviewer_id, MAX(r.date_notified) AS last_notified
            FROM	review_assignments r,
                articles a
            WHERE	r.submission_id = a.article_id AND
                a.journal_id = ?
            GROUP BY r.reviewer_id',
            [(int) $journalId]
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = [];
            $statistics[$row['reviewer_id']]['last_notified'] = $this->datetimeFromDB($row['last_notified']);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get incomplete submission count
        $result = $this->retrieve(
            'SELECT r.reviewer_id, COUNT(*) AS incomplete
            FROM    review_assignments r,
                articles a
            WHERE   r.submission_id = a.article_id AND
                r.date_notified IS NOT NULL AND
                r.date_completed IS NULL AND
                r.cancelled = 0 AND
                r.declined = 0 AND
                r.date_completed IS NULL AND r.declined <> 1 AND (r.cancelled = 0 OR r.cancelled IS NULL) AND a.status = ' . STATUS_QUEUED . ' AND
                a.journal_id = ?
            GROUP BY r.reviewer_id',
            [(int) $journalId]
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = [];
            $statistics[$row['reviewer_id']]['incomplete'] = $row['incomplete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Calculate time taken for completed reviews
        $result = $this->retrieve(
            'SELECT	r.reviewer_id, r.date_notified, r.date_completed
            FROM	review_assignments r,
                articles a
            WHERE	r.submission_id = a.article_id AND
                r.date_notified IS NOT NULL AND
                r.date_completed IS NOT NULL AND
                r.declined = 0 AND
                a.journal_id = ?',
            [(int) $journalId]
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = [];

            $completed = strtotime($this->datetimeFromDB($row['date_completed']));
            $notified = strtotime($this->datetimeFromDB($row['date_notified']));
            if (isset($statistics[$row['reviewer_id']]['total_span'])) {
                $statistics[$row['reviewer_id']]['total_span'] += $completed - $notified;
                $statistics[$row['reviewer_id']]['completed_review_count'] += 1;
            } else {
                $statistics[$row['reviewer_id']]['total_span'] = $completed - $notified;
                $statistics[$row['reviewer_id']]['completed_review_count'] = 1;
            }

            // Calculate the average length of review in weeks.
            $statistics[$row['reviewer_id']]['average_span'] = (($statistics[$row['reviewer_id']]['total_span'] / $statistics[$row['reviewer_id']]['completed_review_count']) / 60 / 60 / 24 / 7);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $statistics;
    }

    /**
     * Get the assignment counts and last assigned date for all copyeditors of the given journal.
     * @param int $journalId
     * @param int $copyeditorId Optional copyeditor ID
     * @return array
     */
    public function getCopyeditorStatistics($journalId, $copyeditorId = null) {
        $statistics = [];

        // WARNING: This is reused for the next two queries.
        $params = [
            (int) $journalId,
            'SIGNOFF_COPYEDITING_INITIAL',
            ASSOC_TYPE_ARTICLE
        ];
        if ($copyeditorId) $params[] = (int) $copyeditorId;

        // Get counts of completed submissions
        $result = $this->retrieve(
            'SELECT	sc.user_id AS editor_id,
                COUNT(sc.assoc_id) AS complete
            FROM	signoffs sc,
                articles a
                LEFT JOIN published_articles pa ON (pa.article_id = a.article_id)
            WHERE	sc.assoc_id = a.article_id AND
                (pa.date_published IS NOT NULL AND a.status <> ' . STATUS_QUEUED . ') AND
                a.journal_id = ? AND
                sc.symbolic = ? AND
                sc.assoc_type = ?
                ' . ($copyeditorId ? ' AND sc.user_id = ?' : '') . '
            GROUP BY sc.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['complete'] = $row['complete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get counts of incomplete submissions
        $result = $this->retrieve(
            'SELECT	sc.user_id AS editor_id,
                COUNT(sc.assoc_id) AS incomplete
            FROM	signoffs sc,
                articles a
                LEFT JOIN published_articles pa ON (pa.article_id = a.article_id)
                LEFT JOIN issues i ON (i.issue_id = pa.issue_id)
            WHERE	sc.assoc_id = a.article_id AND
                NOT (pa.date_published IS NOT NULL AND a.status <> ' . STATUS_QUEUED . ') AND
                i.date_published IS NULL AND a.status = ' . STATUS_QUEUED . ' AND
                a.journal_id = ? AND
                sc.symbolic = ? AND
                sc.assoc_type = ?
                ' . ($copyeditorId ? ' AND sc.user_id = ?' : '') . '
            GROUP BY sc.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['incomplete'] = $row['incomplete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get last assignment date
        $params = [
            (int) $journalId,
            'SIGNOFF_COPYEDITING_INITIAL',
            ASSOC_TYPE_ARTICLE
        ];
        if ($copyeditorId) $params[] = (int) $copyeditorId;

        $result = $this->retrieve(
            'SELECT	sc.user_id AS editor_id,
                MAX(sc.date_notified) AS last_assigned
            FROM	signoffs sc,
                articles a
            WHERE	sc.assoc_id = a.article_id AND
                a.journal_id = ? AND
                sc.symbolic = ? AND
                sc.assoc_type = ?
                ' . ($copyeditorId ? ' AND sc.user_id = ?' : '') . '
            GROUP BY sc.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['last_assigned'] = $this->datetimeFromDB($row['last_assigned']);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $statistics;
    }

    /**
     * Get the assignment counts and last assigned date for all proofreaders of the given journal.
     * @param int $journalId
     * @param int $proofreaderId Optional proofreader ID
     * @return array
     */
    public function getProofreaderStatistics($journalId, $proofreaderId = null) {
        $statistics = [];

        // WARNING: This is used in the next three queries
        $params = [
            (int) $journalId,
            'SIGNOFF_PROOFREADING_PROOFREADER',
            ASSOC_TYPE_ARTICLE
        ];
        if ($proofreaderId) $params[] = (int) $proofreaderId;

        // Get counts of completed submissions
        $result = $this->retrieve(
            'SELECT	sp.user_id AS editor_id,
                COUNT(sp.assoc_id) AS complete
            FROM	signoffs sp,
                articles a
            WHERE	sp.assoc_id = a.article_id AND
                sp.date_completed IS NOT NULL AND
                a.journal_id = ? AND
                sp.symbolic = ? AND
                sp.assoc_type = ? AND
                a.status <> ' . STATUS_QUEUED . '
                ' . ($proofreaderId ? ' AND sp.user_id = ?' : '') . '
            GROUP BY sp.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['complete'] = $row['complete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get counts of incomplete submissions
        $result = $this->retrieve(
            'SELECT	sp.user_id AS editor_id,
                COUNT(sp.assoc_id) AS incomplete
            FROM	signoffs sp,
                articles a
            WHERE	sp.assoc_id = a.article_id AND
                sp.date_completed IS NULL AND
                sp.date_notified IS NOT NULL AND
                a.status = ' . STATUS_QUEUED . ' AND
                a.journal_id = ? AND
                sp.symbolic = ? AND
                sp.assoc_type = ?
                ' . ($proofreaderId ? ' AND sp.user_id = ?' : '') . '
            GROUP BY sp.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['incomplete'] = $row['incomplete'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        // Get last assignment date
        $result = $this->retrieve(
            'SELECT	sp.user_id AS editor_id,
                MAX(sp.date_notified) AS last_assigned
            FROM	signoffs sp,
                articles a
            WHERE	sp.assoc_id = a.article_id AND
                a.journal_id = ? AND
                sp.symbolic = ? AND
                sp.assoc_type = ?
                ' . ($proofreaderId ? ' AND sp.user_id = ?' : '') . '
            GROUP BY sp.user_id',
            $params
        );
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = [];
            $statistics[$row['editor_id']]['last_assigned'] = $this->datetimeFromDB($row['last_assigned']);
            $result->MoveNext();
        }
        $result->Close();
        unset($result);

        return $statistics;
    }

    /**
     * Map a column heading value to a database value for sorting
     * @param string $heading
     * @return string|null
     */
    public function getSortMapping($heading) {
        switch ($heading) {
            case 'id': return 'a.article_id';
            case 'submitDate': return 'a.date_submitted';
            case 'section': return 'section_abbrev';
            case 'authors': return 'author_name';
            case 'title': return 'submission_clean_title';
            case 'active': return 'incomplete';
            case 'subCopyedit': return 'copyedit_completed';
            case 'subLayout': return 'layout_completed';
            case 'subProof': return 'proofread_completed';
            case 'reviewerName': return 'u.last_name';
            case 'quality': return 'average_quality';
            case 'done': return 'completed';
            case 'latest': return 'latest';
            case 'active': return 'active';
            case 'average': return 'average';
            case 'name': return 'u.last_name';
            case 'status': return 'a.status';
            default: return null;
        }
    }
}
?>