<?php
declare(strict_types=1);

/**
 * @file classes/journal/JournalStatisticsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalStatisticsDAO
 * @ingroup journal
 *
 * @brief Operations for retrieving journal statistics.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Visibility, Types, Date Handling)
 * - Performance Optimization (Hash Maps vs in_array)
 * - Safe Math (Division by Zero protection)
 */

define('REPORT_TYPE_JOURNAL',    0x00001);
define('REPORT_TYPE_EDITOR',     0x00002);
define('REPORT_TYPE_REVIEWER',   0x00003);
define('REPORT_TYPE_SECTION',    0x00004);

class JournalStatisticsDAO extends DAO {
    
    /**
     * Determine the first date the journal was active.
     * (This is an approximation but needs to run quickly.)
     * @param int $journalId
     * @return int|null Date in seconds since the UNIX epoch, or null
     */
    public function getFirstActivityDate($journalId) {
        $result = $this->retrieve(
            'SELECT LEAST(a.date_submitted, COALESCE(pa.date_published, NOW()), COALESCE(i.date_published, NOW())) AS first_date
            FROM articles a
                LEFT JOIN published_articles pa ON (a.article_id = pa.article_id)
                LEFT JOIN issues i ON (pa.issue_id = i.issue_id)
                LEFT JOIN articles a2 ON (a2.article_id < a.article_id AND a2.date_submitted IS NOT NULL AND a2.journal_id = ?)
            WHERE a2.article_id IS NULL AND
                a.date_submitted IS NOT NULL AND
                a.journal_id = ?',
            array(
                (int) $journalId,
                (int) $journalId
            )
        );

        $row = $result->getRowAssoc(false);
        $firstActivityDate = $this->datetimeFromDB($row['first_date']);
        $result->Close();

        // An earlier user registration can override the earliest article activity date
        $result = $this->retrieve(
            'SELECT MIN(u.date_registered) AS first_date FROM users u JOIN roles r ON (u.user_id = r.user_id) WHERE r.journal_id = ?',
            array((int) $journalId)
        );
        
        $row = $result->getRowAssoc(false);
        $firstUserDate = $this->datetimeFromDB($row['first_date']);
        
        // PHP 8 Safety: Handle nulls in strtotime comparison
        $tsActivity = $firstActivityDate ? strtotime($firstActivityDate) : null;
        $tsUser = $firstUserDate ? strtotime($firstUserDate) : null;

        if (!$tsActivity || ($tsUser && $tsActivity && $tsUser < $tsActivity)) {
            $firstActivityDate = $firstUserDate;
        }

        if (!$firstActivityDate) return null;
        
        return strtotime($firstActivityDate);
    }

    /**
     * Get statistics about articles in the system.
     * @param int $journalId
     * @param array|null $sectionIds
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
     */
    public function getArticleStatistics($journalId, $sectionIds = null, $dateStart = null, $dateEnd = null) {
        // Bring in status constants
        if (!class_exists('Article')) {
            import('classes.article.Article');
        }

        $params = array((int) $journalId);
        $sectionSql = '';
        
        if (!empty($sectionIds) && is_array($sectionIds)) {
            $sectionSql = ' AND (a.section_id = ?';
            $params[] = (int) array_shift($sectionIds);
            foreach ($sectionIds as $sectionId) {
                $sectionSql .= ' OR a.section_id = ?';
                $params[] = (int) $sectionId;
            }
            $sectionSql .= ')';
        }

        $sql = 'SELECT a.article_id,
                a.date_submitted,
                pa.date_published,
                pa.published_article_id,
                d.decision,
                a.status
            FROM articles a
                LEFT JOIN published_articles pa ON (a.article_id = pa.article_id)
                LEFT JOIN edit_decisions d ON (d.article_id = a.article_id)
            WHERE a.journal_id = ?' .
            ($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
            ($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
            $sectionSql .
            ' ORDER BY a.article_id, d.date_decided DESC';

        $result = $this->retrieve($sql, $params);

        $returner = array(
            'numSubmissions' => 0,
            'numReviewedSubmissions' => 0,
            'numPublishedSubmissions' => 0,
            'submissionsAccept' => 0,
            'submissionsDecline' => 0,
            'submissionsAcceptPercent' => 0,
            'submissionsDeclinePercent' => 0,
            'daysToPublication' => 0
        );

        // Performance: Use Hash Map (Keys) instead of in_array for O(1) lookup
        $articleIds = array();

        $totalTimeToPublication = 0;
        $timeToPublicationCount = 0;

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);

            // For each article, pick the most recent editor decision only
            if (!isset($articleIds[$row['article_id']])) {
                $articleIds[$row['article_id']] = true; // Mark as seen
                $returner['numSubmissions']++;

                if (!empty($row['published_article_id']) && $row['status'] == STATUS_PUBLISHED) {
                    $returner['numPublishedSubmissions']++;
                }

                if (!empty($row['date_submitted']) && !empty($row['date_published']) && $row['status'] == STATUS_PUBLISHED) {
                    $timeSubmitted = strtotime($this->datetimeFromDB($row['date_submitted']));
                    $timePublished = strtotime($this->datetimeFromDB($row['date_published']));
                    if ($timePublished > $timeSubmitted) {
                        $totalTimeToPublication += ($timePublished - $timeSubmitted);
                        $timeToPublicationCount++;
                    }
                }

                import('classes.submission.common.Action');
                switch ($row['decision']) {
                    case SUBMISSION_EDITOR_DECISION_ACCEPT:
                        $returner['submissionsAccept']++;
                        $returner['numReviewedSubmissions']++;
                        break;
                    case SUBMISSION_EDITOR_DECISION_DECLINE:
                        $returner['submissionsDecline']++;
                        $returner['numReviewedSubmissions']++;
                        break;
                }
            }

            $result->moveNext();
        }

        $result->Close();
        unset($result);

        // Calculate percentages where necessary (Division by Zero Protection)
        if ($returner['numReviewedSubmissions'] != 0) {
            $returner['submissionsAcceptPercent'] = round($returner['submissionsAccept'] * 100 / $returner['numReviewedSubmissions']);
            $returner['submissionsDeclinePercent'] = round($returner['submissionsDecline'] * 100 / $returner['numReviewedSubmissions']);
        }

        if ($timeToPublicationCount != 0) {
            // Keep one sig fig
            $returner['daysToPublication'] = round($totalTimeToPublication / $timeToPublicationCount / 60 / 60 / 24);
        }

        return $returner;
    }

    /**
     * Get statistics about users in the system.
     * @param int $journalId
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
     */
    public function getUserStatistics($journalId, $dateStart = null, $dateEnd = null) {
        $roleDao = DAORegistry::getDAO('RoleDAO');

        // Get count of total users for this journal
        $result = $this->retrieve(
            'SELECT COUNT(DISTINCT r.user_id) FROM roles r, users u WHERE r.user_id = u.user_id AND r.journal_id = ?' .
            ($dateStart !== null ? ' AND u.date_registered >= ' . $this->datetimeToDB($dateStart) : '') .
            ($dateEnd !== null ? ' AND u.date_registered <= ' . $this->datetimeToDB($dateEnd) : ''),
            (int) $journalId
        );

        $returner = array(
            'totalUsersCount' => isset($result->fields[0]) ? $result->fields[0] : 0
        );

        $result->Close();
        unset($result);

        // Get user counts for each role.
        $result = $this->retrieve(
            'SELECT r.role_id, COUNT(r.user_id) AS role_count 
             FROM roles r 
             LEFT JOIN users u ON (r.user_id = u.user_id) 
             WHERE r.journal_id = ?' .
            ($dateStart !== null ? ' AND u.date_registered >= ' . $this->datetimeToDB($dateStart) : '') .
            ($dateEnd !== null ? ' AND u.date_registered <= ' . $this->datetimeToDB($dateEnd) : '') .
            ' GROUP BY r.role_id',
            (int) $journalId
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner[$roleDao->getRolePath($row['role_id'])] = $row['role_count'];
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Get statistics about subscriptions.
     * @param int $journalId
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
     */
    public function getSubscriptionStatistics($journalId, $dateStart = null, $dateEnd = null) {
        $result = $this->retrieve(
            'SELECT st.type_id,
                sts.setting_value AS type_name,
                count(s.subscription_id) AS type_count
            FROM subscription_types st
                LEFT JOIN journals j ON (j.journal_id = st.journal_id)
                LEFT JOIN subscription_type_settings sts ON (st.type_id = sts.type_id AND sts.setting_name = ? AND sts.locale = j.primary_locale),
                subscriptions s
            WHERE st.journal_id = ?
                AND s.type_id = st.type_id' .
            ($dateStart !== null ? ' AND s.date_start >= ' . $this->datetimeToDB($dateStart) : '') .
            ($dateEnd !== null ? ' AND s.date_start <= ' . $this->datetimeToDB($dateEnd) : '') .
            ' GROUP BY st.type_id, sts.setting_value',
            array('name', (int) $journalId)
        );

        $returner = array();

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner[$row['type_id']] = array(
                'name' => $row['type_name'],
                'count' => $row['type_count']
            );
            $result->moveNext();
        }
        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Get statistics about issues in the system.
     * @param int $journalId
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
     */
    public function getIssueStatistics($journalId, $dateStart = null, $dateEnd = null) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count, published FROM issues WHERE journal_id = ?' .
            ($dateStart !== null ? ' AND date_published >= ' . $this->datetimeToDB($dateStart) : '') .
            ($dateEnd !== null ? ' AND date_published <= ' . $this->datetimeToDB($dateEnd) : '') .
            ' GROUP BY published',
            (int) $journalId
        );

        $returner = array(
            'numPublishedIssues' => 0,
            'numUnpublishedIssues' => 0
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);

            if ($row['published']) {
                $returner['numPublishedIssues'] = $row['count'];
            } else {
                $returner['numUnpublishedIssues'] = $row['count'];
            }
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        $returner['numIssues'] = $returner['numPublishedIssues'] + $returner['numUnpublishedIssues'];

        return $returner;
    }

    /**
     * Get statistics about reviewers in the system.
     * @param int $journalId
     * @param array $sectionIds
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
     */
    public function getReviewerStatistics($journalId, $sectionIds, $dateStart = null, $dateEnd = null) {
        $params = array((int) $journalId);
        $sectionSql = '';
        
        if (!empty($sectionIds) && is_array($sectionIds)) {
            $sectionSql = ' AND (a.section_id = ?';
            $params[] = (int) array_shift($sectionIds);
            foreach ($sectionIds as $sectionId) {
                $sectionSql .= ' OR a.section_id = ?';
                $params[] = (int) $sectionId;
            }
            $sectionSql .= ')';
        }

        // Optimized SQL: Replaced implicit join with explicit joins
        $sql = 'SELECT a.article_id,
                af.date_uploaded AS date_rv_uploaded,
                r.review_id,
                u.date_registered,
                r.reviewer_id,
                r.quality,
                r.date_assigned,
                r.date_completed
            FROM articles a
            JOIN article_files af ON (af.article_id = a.article_id AND af.file_id = a.review_file_id)
            JOIN review_assignments r ON (r.submission_id = a.article_id)
            LEFT JOIN users u ON (u.user_id = r.reviewer_id)
            WHERE a.journal_id = ?
                AND af.revision = 1' .
            ($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
            ($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
            $sectionSql;
            
        $result = $this->retrieve($sql, $params);

        $returner = array(
            'reviewsCount' => 0,
            'reviewerScore' => 0,
            'daysPerReview' => 0,
            'reviewerAddedCount' => 0,
            'reviewerCount' => 0,
            'reviewedSubmissionsCount' => 0
        );

        $scoredReviewsCount = 0;
        $totalScore = 0;
        $completedReviewsCount = 0;
        $totalElapsedTime = 0;
        
        // Use Hash Map for O(1) lookups
        $reviewerList = array();
        $articleIds = array();

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner['reviewsCount']++;
            
            if (!empty($row['quality'])) {
                $scoredReviewsCount++;
                $totalScore += $row['quality'];
            }

            // Optimization: Hash map assignment instead of array_push
            $articleIds[$row['article_id']] = true;

            if (!empty($row['reviewer_id']) && !isset($reviewerList[$row['reviewer_id']])) {
                $returner['reviewerCount']++;
                
                $dateRegisteredStr = $this->datetimeFromDB($row['date_registered']);
                $dateRegistered = $dateRegisteredStr ? strtotime($dateRegisteredStr) : 0;
                
                // Safe date comparison
                $startCheck = ($dateStart === null || $dateRegistered >= $dateStart);
                $endCheck = ($dateEnd === null || $dateRegistered <= $dateEnd);

                if ($startCheck && $endCheck) {
                    $returner['reviewerAddedCount']++;
                }
                $reviewerList[$row['reviewer_id']] = true;
            }

            if (!empty($row['date_assigned']) && !empty($row['date_completed'])) {
                $timeReviewVersionUploaded = strtotime($this->datetimeFromDB($row['date_rv_uploaded']));
                $timeCompleted = strtotime($this->datetimeFromDB($row['date_completed']));
                
                if ($timeCompleted > $timeReviewVersionUploaded) {
                    $completedReviewsCount++;
                    $totalElapsedTime += ($timeCompleted - $timeReviewVersionUploaded);
                }
            }
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        // Calculate Averages with Div/0 Protection
        if ($scoredReviewsCount > 0) {
            // To one decimal place
            $returner['reviewerScore'] = round($totalScore * 10 / $scoredReviewsCount) / 10;
        }
        
        if ($completedReviewsCount > 0) {
            $seconds = $totalElapsedTime / $completedReviewsCount;
            $returner['daysPerReview'] = $seconds / 60 / 60 / 24;
        }

        $returner['reviewedSubmissionsCount'] = count($articleIds);

        return $returner;
    }
}
?>