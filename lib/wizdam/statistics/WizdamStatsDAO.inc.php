<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/statistics/WizdamStatsDAO.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Codecanau Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WizdamStatsDAO
 * @ingroup Statistics
 * 
 * @brief Data Access Object for Wizdam Statistics.
 */

import('classes.db.DAO');

if (!defined('ASSOC_TYPE_ARTICLE')) {
    define('ASSOC_TYPE_ARTICLE', 259);
}
if (!defined('ASSOC_TYPE_GALLEY')) {
    define('ASSOC_TYPE_GALLEY', 258);
}

class WizdamStatsDAO extends DAO {

    /**
     * [HELPER] Fetch a single scalar value from a query result.
     * Acts as a wrapper to avoid ADORecordSet::$fields type errors.
     * @param string $sql SQL query to execute.
     * @param array $params Parameter binding for the query (default: []).
     * @param string|null $key Specific column name to fetch. If null, fetches the first column (default: null).
     * @return mixed|null Scalar value from the target column, or null if query is empty/failed.
     */
    protected function fetchScalar(string $sql, array $params = [], ?string $key = null) {
        $result = $this->retrieve($sql, $params);
        $returner = null;

        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if ($key !== null && isset($row[$key])) {
                $returner = $row[$key];
            } else {
                $returner = reset($row);
            }
        }
        if ($result) {
            $result->Close();
        }
        
        return $returner;
    }

    /**
     * [HELPER] Fetch the current row from a result set as an associative array.
     * @param mixed $result Result set object from ADOdb.
     * @return array|null Array containing row data, or null if EOF/failed.
     */
    protected function fetchRow($result): ?array {
        if ($result && !$result->EOF) {
            return $result->GetRowAssoc(false);
        }
        return null;
    }

    /**
     * Check for the existence of required statistics tables and columns in the database.
     * @return array Associative array containing boolean status for: 
     * 'metricsTableExists', 'metricsColumns' (array), 
     * 'articleStatsExists', 'galleyStatsExists', and 'editDecisionsExists'.
     */
    public function checkDatabaseStructure(): array {
        $info = [
            'metricsTableExists' => false,
            'metricsColumns' => [],
            'articleStatsExists' => false,
            'galleyStatsExists' => false,
            'editDecisionsExists' => false
        ];
        
        try {
            $r = $this->retrieve("SHOW TABLES LIKE 'metrics'");
            if ($r && !$r->EOF) {
                $info['metricsTableExists'] = true;
            }
            if ($r) {
                $r->Close();
            }

            if ($info['metricsTableExists']) {
                $r = $this->retrieve("SHOW COLUMNS FROM metrics");
                if ($r) {
                    while (!$r->EOF) {
                        $row = $r->GetRowAssoc(false);
                        $fieldName = $row['Field'] ?? $row['field'] ?? '';
                        if (!empty($fieldName)) {
                            $info['metricsColumns'][] = (string) $fieldName;
                        }
                        $r->MoveNext();
                    }
                    $r->Close();
                }
            }

            $r1 = $this->retrieve("SHOW TABLES LIKE 'article_view_stats'");
            if ($r1 && !$r1->EOF) {
                $info['articleStatsExists'] = true;
            }
            if ($r1) {
                $r1->Close();
            }

            $r2 = $this->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'");
            if ($r2 && !$r2->EOF) {
                $info['galleyStatsExists'] = true;
            }
            if ($r2) {
                $r2->Close();
            }

            $r3 = $this->retrieve("SHOW TABLES LIKE 'edit_decisions'");
            if ($r3 && !$r3->EOF) {
                $info['editDecisionsExists'] = true;
            }
            if ($r3) {
                $r3->Close();
            }
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) {
                error_log("WizdamStatsDAO DB Check Error: " . $e->getMessage());
            }
        }
        
        return $info;
    }

    /**
     * Fetch total views and downloads for a journal from the metrics table.
     * @param int $journalId Journal ID to fetch statistics for.
     * @param bool $metricsTableExists Status of metrics table existence.
     * @return array Associative array with 'views' and 'downloads' keys (integers).
     */
    /**
     * [FIX] Sebelumnya kalau tabel `metrics` menghasilkan total 0/0 (baik
     * karena tabelnya memang tidak ada, ATAU ada tapi datanya kosong),
     * method ini langsung berhenti di situ. Kode asli (getJournalStats.php)
     * punya fallback: kalau totalnya 0/0, coba lagi dari
     * article_view_stats / article_galley_view_stats sebelum menyerah.
     * Tanpa fallback ini, instalasi yang datanya tersimpan di tabel lama
     * (bukan `metrics`) akan selalu menampilkan 0 views/downloads padahal
     * datanya sebenarnya ada.
     * @param int $journalId
     * @param bool $metricsTableExists
     * @param bool $articleStatsExists Dari checkDatabaseStructure()['articleStatsExists'].
     * @param bool $galleyStatsExists Dari checkDatabaseStructure()['galleyStatsExists'].
     * @return array{views: int, downloads: int}
     */
    public function getCoreViewsDownloads(int $journalId, bool $metricsTableExists, bool $articleStatsExists = false, bool $galleyStatsExists = false): array {
        $views = 0;
        $downloads = 0;

        if ($metricsTableExists) {
            $views = (int) ($this->fetchScalar(
                "SELECT SUM(metric) AS total FROM metrics WHERE context_id = ? AND assoc_type = ?",
                [(int) $journalId, ASSOC_TYPE_ARTICLE],
                'total'
            ) ?? 0);
            $downloads = (int) ($this->fetchScalar(
                "SELECT SUM(metric) AS total FROM metrics WHERE context_id = ? AND assoc_type = ?",
                [(int) $journalId, ASSOC_TYPE_GALLEY],
                'total'
            ) ?? 0);
        }

        // [FIX] Fallback -- sama persis dengan getJournalStats.php baris
        // ~294-324. Hanya dipicu kalau KEDUANYA masih 0 (bukan cuma salah
        // satu), supaya tidak menimpa angka yang sudah benar dari `metrics`.
        if ($views === 0 && $downloads === 0) {
            if ($articleStatsExists) {
                $views = (int) ($this->fetchScalar(
                    "SELECT SUM(avs.views) AS total FROM article_view_stats avs
                     JOIN articles a ON avs.article_id = a.article_id
                     WHERE a.journal_id = ?",
                    [(int) $journalId],
                    'total'
                ) ?? 0);
            }
            if ($galleyStatsExists) {
                $downloads = (int) ($this->fetchScalar(
                    "SELECT SUM(agvs.views) AS total FROM article_galley_view_stats agvs
                     JOIN article_galleys ag ON agvs.galley_id = ag.galley_id
                     JOIN articles a ON ag.article_id = a.article_id
                     WHERE a.journal_id = ?",
                    [(int) $journalId],
                    'total'
                ) ?? 0);
            }
        }

        return ['views' => $views, 'downloads' => $downloads];
    }

    /**
     * Determine the available date column name in the metrics table for yearly queries.
     *
     * [FIX] Sebelumnya HANYA cek 'day'/'load_time' -- kode asli
     * (getJournalStats.php) cek 4 kandidat kolom: 'day', 'load_time',
     * 'entry_time', 'date'. Kalau instalasi ini pakai skema metrics dengan
     * kolom 'entry_time'/'date', versi sebelumnya menganggap TIDAK ADA
     * kolom tanggal sama sekali -> breakdown views/downloads per tahun
     * kosong total, walau total keseluruhan (all-time) tetap benar.
     * @param array $metricsColumns List of column names from the metrics table.
     * @return string Valid date column name, atau string kosong kalau tidak ada satupun kandidat ditemukan.
     */
    public function getDateColumn(array $metricsColumns): string {
        foreach (['day', 'load_time', 'entry_time', 'date'] as $candidate) {
            if (in_array($candidate, $metricsColumns, true)) {
                return $candidate;
            }
        }
        return '';
    }

    /**
     * Fetch aggregated views and downloads per year from the metrics table.
     * @param int $journalId Journal ID.
     * @param string $dateColumn Valid date column name ('day' or 'load_time').
     * @return array Multi-dimensional associative array formatted as [year => ['views' => int, 'downloads' => int]].
     */
    public function getYearlyViewsDownloads(int $journalId, string $dateColumn): array {
        $data = [];
        if (empty($dateColumn)) {
            return $data;
        }
        
        $r = $this->retrieve(
            "SELECT YEAR($dateColumn) as year, SUM(metric) as views FROM metrics WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL GROUP BY YEAR($dateColumn)", 
            [(int) $journalId, ASSOC_TYPE_ARTICLE]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $year = (int) $row['year'];
                $data[$year]['views'] = (int) $row['views'];
                $r->MoveNext();
            }
            $r->Close();
        }
        
        $r = $this->retrieve(
            "SELECT YEAR($dateColumn) as year, SUM(metric) as downloads FROM metrics WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL GROUP BY YEAR($dateColumn)", 
            [(int) $journalId, ASSOC_TYPE_GALLEY]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $year = (int) $row['year'];
                if (!isset($data[$year])) {
                    $data[$year] = [];
                }
                $data[$year]['downloads'] = (int) $row['downloads'];
                $r->MoveNext();
            }
            $r->Close();
        }
        
        return $data;
    }

    /**
     * Fetch the number of article submissions per year.
     * @param int $journalId Journal ID.
     * @return array Associative array formatted as [year => ['submissions' => int]].
     */
    public function getYearlySubmissions(int $journalId): array {
        $data = [];
        $r = $this->retrieve(
            "SELECT YEAR(date_submitted) as year, COUNT(*) as submissions FROM articles WHERE journal_id = ? GROUP BY YEAR(date_submitted)", 
            [(int) $journalId]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $data[(int) $row['year']]['submissions'] = (int) $row['submissions'];
                $r->MoveNext();
            }
            $r->Close();
        }
        return $data;
    }

    /**
     * Fetch the number of published articles per year.
     * @param int $journalId Journal ID.
     * @return array Associative array formatted as [year => ['published' => int]].
     */
    public function getYearlyPublished(int $journalId): array {
        $data = [];
        $r = $this->retrieve(
            "SELECT YEAR(pa.date_published) as year, COUNT(*) as published FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? GROUP BY YEAR(pa.date_published)", 
            [(int) $journalId]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $data[(int) $row['year']]['published'] = (int) $row['published'];
                $r->MoveNext();
            }
            $r->Close();
        }
        return $data;
    }

    /**
     * Calculate total articles reviewed, accepted, and declined.
     * @param int $journalId Journal ID.
     * @param int $currentYear Current year (data below this year is calculated).
     * @return array Associative array with 'reviewed', 'accepted', and 'declined' keys (integers).
     */
    public function getAcceptDeclineTotals(int $journalId, int $currentYear): array {
        $rev = $this->fetchScalar(
            "SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status IN (2, 3, 4)", 
            [(int) $journalId, (int) $currentYear], 
            'total'
        );
        $acc = $this->fetchScalar(
            "SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 3", 
            [(int) $journalId, (int) $currentYear], 
            'total'
        );
        $dec = $this->fetchScalar(
            "SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 4", 
            [(int) $journalId, (int) $currentYear], 
            'total'
        );
        
        return [
            'reviewed' => (int) ($rev ?? 0), 
            'accepted' => (int) ($acc ?? 0), 
            'declined' => (int) ($dec ?? 0)
        ];
    }

    /**
     * Fetch raw review duration data (in days) for all time.
     * @param int $journalId Journal ID.
     * @param int $currentYear Current year (excludes the current year).
     * @return array Numeric array containing review durations in days (floats).
     */
    public function getDaysToReviewAllTime(int $journalId, int $currentYear): array {
        $days = [];
        $r = $this->retrieve(
            "SELECT DATEDIFF(ra.date_completed, ra.date_notified) AS days FROM review_assignments ra JOIN articles a ON ra.submission_id = a.article_id WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(ra.date_notified) < ?", 
            [(int) $journalId, (int) $currentYear]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $d = (float) $row['days'];
                if ($d > 0) {
                    $days[] = $d;
                }
                $r->MoveNext();
            }
            $r->Close();
        }
        return $days;
    }

    /**
     * Fetch raw submission-to-publication duration data (in days) for all time.
     * @param int $journalId Journal ID.
     * @param int $currentYear Current year.
     * @return array Numeric array containing durations in days (floats).
     */
    public function getDaysToPublicationAllTime(int $journalId, int $currentYear): array {
        $days = [];
        $r = $this->retrieve(
            "SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND pa.date_published IS NOT NULL", 
            [(int) $journalId, (int) $currentYear]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $d = (float) $row['days'];
                if ($d > 0) {
                    $days[] = $d;
                }
                $r->MoveNext();
            }
            $r->Close();
        }
        return $days;
    }

    /**
     * Fetch raw submission-to-first-decision duration data (in days).
     * @param int $journalId Journal ID.
     * @param int $currentYear Current year.
     * @param bool $hasEditDecisions Status of edit_decisions table existence.
     * @return array Numeric array containing durations in days (floats).
     */
    public function getFirstDecisionDaysAllTime(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $days = [];
        if ($hasEditDecisions) {
            $r = $this->retrieve(
                "SELECT a.date_submitted, MIN(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.date_decided IS NOT NULL GROUP BY a.article_id", 
                [(int) $journalId, (int) $currentYear]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['d']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            $days[] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        } else {
            $r = $this->retrieve(
                "SELECT a.date_submitted, a.date_status_modified FROM articles a WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3", 
                [(int) $journalId, (int) $currentYear]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['date_status_modified']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            $days[] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        }
        return $days;
    }

    /**
     * Fetch raw submission-to-acceptance duration data (in days).
     * @param int $journalId Journal ID.
     * @param int $currentYear Current year.
     * @param bool $hasEditDecisions Status of edit_decisions table existence.
     * @return array Numeric array containing durations in days (floats).
     */
    public function getSubmissionToAcceptanceDaysAllTime(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $days = [];
        if ($hasEditDecisions) {
            $r = $this->retrieve(
                "SELECT a.date_submitted, MAX(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.decision = 1 GROUP BY a.article_id", 
                [(int) $journalId, (int) $currentYear]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['d']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            $days[] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        } else {
            $r = $this->retrieve(
                "SELECT a.date_submitted, a.date_status_modified FROM articles a WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3", 
                [(int) $journalId, (int) $currentYear]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['date_status_modified']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            $days[] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        }
        return $days;
    }

    /**
     * Fetch raw acceptance-to-publication duration data (in days).
     * @param int $journalId Journal ID.
     * @param int $currentYear Current year.
     * @param bool $hasEditDecisions Status of edit_decisions table existence.
     * @return array Numeric array containing durations in days (floats).
     */
    public function getAcceptanceToPublicationDaysAllTime(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $days = [];
        if ($hasEditDecisions) {
            $r = $this->retrieve(
                "SELECT MAX(ed.date_decided) as d, pa.date_published FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.decision = 1 AND pa.date_published IS NOT NULL GROUP BY a.article_id", 
                [(int) $journalId, (int) $currentYear]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $d = strtotime((string) $row['d']);
                    $p = strtotime((string) $row['date_published']);
                    if ($d && $p) {
                        $diff = (int) round(($p - $d) / 86400);
                        if ($diff > 0) {
                            $days[] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        } else {
            $r = $this->retrieve(
                "SELECT a.date_status_modified, pa.date_published FROM articles a JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3 AND pa.date_published IS NOT NULL", 
                [(int) $journalId, (int) $currentYear]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $d = strtotime((string) $row['date_status_modified']);
                    $p = strtotime((string) $row['date_published']);
                    if ($d && $p) {
                        $diff = (int) round(($p - $d) / 86400);
                        if ($diff > 0) {
                            $days[] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        }
        return $days;
    }

    /**
     * Fetch raw yearly timeline data for various editorial metrics.
     *
     * [FIX] SEMUA metrik di bawah ini SEBELUMNYA dikelompokkan per tahun
     * berdasarkan tanggal EVENT-nya sendiri (date_completed/date_decided/
     * date_published) -- kode asli (getJournalStats.php) SELALU
     * mengelompokkan berdasarkan YEAR(a.date_submitted), tanpa kecuali.
     * Perbedaan ini membuat data "melompat" ke tahun yang salah (atau di
     * luar rentang tahun yang di-loop), yang tampak sebagai grafik nyaris
     * kosong. Disamakan persis ke YEAR(a.date_submitted).
     *
     * [FIX] Blok "else" (fallback tanpa tabel edit_decisions, pakai
     * a.status=3 + a.date_status_modified) SEBELUMNYA TIDAK ADA SAMA SEKALI
     * di sini -- kalau $hasEditDecisions false, firstDecision/acceptance/
     * acceptanceToPub selalu array kosong untuk semua tahun. Sekarang
     * ditambahkan, persis logic original.
     *
     * [CATATAN] Query 'review' di original TIDAK memfilter journal_id sama
     * sekali (site-wide, lintas semua jurnal) -- kemungkinan itu bug di
     * kode asli sendiri, mengingat platform ini multi-jurnal. Filter
     * journal_id TETAP dipertahankan di sini (bukan dihapus) supaya data
     * review time tidak tercampur antar jurnal -- satu-satunya deviasi
     * yang disengaja dari original, sisanya disamakan persis.
     *
     * @param int $journalId Journal ID.
     * @param int $year Specific year to fetch data for.
     * @param bool $hasEditDecisions Status of edit_decisions table existence.
     * @return array Associative array containing numeric duration arrays for: 'review', 'publication', 'firstDecision', 'acceptance', 'acceptanceToPub'.
     */
    public function getYearlyTimelineData(int $journalId, int $year, bool $hasEditDecisions): array {
        $data = [
            'review' => [], 
            'publication' => [], 
            'firstDecision' => [], 
            'acceptance' => [], 
            'acceptanceToPub' => []
        ];
        
        $r = $this->retrieve(
            "SELECT DATEDIFF(ra.date_completed, ra.date_notified) AS days FROM review_assignments ra JOIN articles a ON (ra.submission_id = a.article_id) WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(a.date_submitted) = ?", 
            [(int) $journalId, (int) $year]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $d = (float) $row['days'];
                if ($d > 0) {
                    $data['review'][] = $d;
                }
                $r->MoveNext();
            }
            $r->Close();
        }

        $r = $this->retrieve(
            "SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? AND pa.date_published IS NOT NULL", 
            [(int) $journalId, (int) $year]
        );
        if ($r) {
            while (!$r->EOF) {
                $row = $r->GetRowAssoc(false);
                $d = (float) $row['days'];
                if ($d > 0) {
                    $data['publication'][] = $d;
                }
                $r->MoveNext();
            }
            $r->Close();
        }

        if ($hasEditDecisions) {
            $r = $this->retrieve(
                "SELECT a.date_submitted, MIN(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? GROUP BY a.article_id", 
                [(int) $journalId, (int) $year]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['d']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            $data['firstDecision'][] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }

            $r = $this->retrieve(
                "SELECT a.date_submitted, MAX(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? AND ed.decision = 1 GROUP BY a.article_id", 
                [(int) $journalId, (int) $year]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['d']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            $data['acceptance'][] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }

            $r = $this->retrieve(
                "SELECT a.date_submitted, MAX(ed.date_decided) as d, pa.date_published FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? AND ed.decision = 1 GROUP BY a.article_id", 
                [(int) $journalId, (int) $year]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $d = strtotime((string) $row['d']);
                    $p = strtotime((string) $row['date_published']);
                    if ($d && $p) {
                        $diff = (int) round(($p - $d) / 86400);
                        if ($diff > 0) {
                            $data['acceptanceToPub'][] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        } else {
            // [FIX] Fallback yang SEBELUMNYA hilang total -- persis
            // getJournalStats.php: tanpa tabel edit_decisions, gunakan
            // a.status=3 (diterima) + a.date_status_modified sebagai
            // pengganti tanggal keputusan.
            $r = $this->retrieve(
                "SELECT a.date_submitted, a.date_status_modified FROM articles a WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? AND a.status = 3",
                [(int) $journalId, (int) $year]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $s = strtotime((string) $row['date_submitted']);
                    $d = strtotime((string) $row['date_status_modified']);
                    if ($s && $d) {
                        $diff = (int) round(($d - $s) / 86400);
                        if ($diff > 0) {
                            // Original memakai nilai yang sama untuk firstDecision & acceptance di jalur fallback ini.
                            $data['firstDecision'][] = (float) $diff;
                            $data['acceptance'][] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }

            $r = $this->retrieve(
                "SELECT a.date_status_modified, pa.date_published FROM articles a JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? AND a.status = 3 AND pa.date_published IS NOT NULL",
                [(int) $journalId, (int) $year]
            );
            if ($r) {
                while (!$r->EOF) {
                    $row = $r->GetRowAssoc(false);
                    $d = strtotime((string) $row['date_status_modified']);
                    $p = strtotime((string) $row['date_published']);
                    if ($d && $p) {
                        $diff = (int) round(($p - $d) / 86400);
                        if ($diff > 0) {
                            $data['acceptanceToPub'][] = (float) $diff;
                        }
                    }
                    $r->MoveNext();
                }
                $r->Close();
            }
        }
        
        return $data;
    }

    /**
     * Calculate total published articles and active issues.
     * @param int $journalId Journal ID.
     * @return array Associative array with 'articles' and 'issues' keys (integers).
     */
    public function getTotalArticlesAndIssues(int $journalId): array {
        $a = $this->fetchScalar(
            "SELECT COUNT(pa.article_id) AS total FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id WHERE a.journal_id = ?", 
            [(int) $journalId], 
            'total'
        );
        $i = $this->fetchScalar(
            "SELECT COUNT(*) AS total FROM issues WHERE journal_id = ? AND published = 1", 
            [(int) $journalId], 
            'total'
        );
        
        return [
            'articles' => (int) ($a ?? 0), 
            'issues' => (int) ($i ?? 0)
        ];
    }

    /**
     * Fetch the last publication year and the number of articles in that year.
     * @param int $journalId Journal ID.
     * @return array Associative array with 'year' (string/int) and 'count' (integer) keys.
     */
    public function getLastPublicationData(int $journalId): array {
        $r = $this->retrieve(
            "SELECT YEAR(date_published) as y, COUNT(*) as c FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND pa.date_published IS NOT NULL GROUP BY YEAR(date_published) ORDER BY y DESC LIMIT 1", 
            [(int) $journalId]
        );
        
        $row = null;
        if ($r && !$r->EOF) {
            $row = $r->GetRowAssoc(false);
        }
        if ($r) {
            $r->Close();
        }
        
        return $row ? [
            'year' => (int) $row['y'], 
            'count' => (int) $row['c']
        ] : [
            'year' => '', 
            'count' => 0
        ];
    }

    /**
     * Detect the earliest year an article was submitted to the journal (for chart ranges).
     * @param int $journalId Journal ID.
     * @param int $fallback Fallback year if no data exists.
     * @return int Earliest submission year.
     */
    public function getStartYear(int $journalId, int $fallback): int {
        $y = $this->fetchScalar(
            "SELECT MIN(YEAR(date_submitted)) AS y FROM articles WHERE journal_id = ?", 
            [(int) $journalId], 
            'y'
        );
        
        return $y ? (int) $y : $fallback;
    }

    /**
     * Fetch basic metrics for Smart Cache change-detection hash generation.
     * @param int $journalId Journal ID.
     * @return array Associative array containing metrics: 'total_articles', 'total_published', 'total_views', 'last_article_mod'.
     */
    public function getHashMetrics(int $journalId): array {
        $m = [];
        $m['total_articles'] = (int) ($this->fetchScalar("SELECT COUNT(*) as t FROM articles WHERE journal_id = ?", [(int) $journalId], 't') ?? 0);
        $m['total_published'] = (int) ($this->fetchScalar("SELECT COUNT(*) as t FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ?", [(int) $journalId], 't') ?? 0);
        
        $v = $this->fetchScalar(
            "SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')", 
            [ASSOC_TYPE_ARTICLE, (int) $journalId], 
            't'
        );
        if (empty($v)) {
            $v = $this->fetchScalar(
                "SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ?", 
                [ASSOC_TYPE_ARTICLE, (int) $journalId], 
                't'
            );
        }
        $m['total_views'] = (int) ($v ?? 0);
        $m['last_article_mod'] = (string) ($this->fetchScalar("SELECT MAX(last_modified) as t FROM articles WHERE journal_id = ?", [(int) $journalId], 't') ?? '');
        
        return $m;
    }

    /**
     * [PERF FIX] Versi batch dari getSiteJournalStats() -- ambil statistik
     * views/downloads/authors untuk BANYAK jurnal sekaligus dalam 3 query
     * total (bukan sampai 5 query x N jurnal). Dipakai oleh
     * WizdamStats::getSiteWideStats() untuk menghindari pola N+1.
     *
     * Catatan: versi batch ini tidak menyertakan fallback query
     * "tanpa filter metric_type" seperti getSiteJournalStats() (yang
     * dirancang untuk edge-case metric_type tak dikenal per-instalasi,
     * bukan per-jurnal) -- karena metric_type berlaku instalasi-wide,
     * bukan per-jurnal, fallback per-jurnal tidak diperlukan di sini.
     *
     * @param int[] $journalIds
     * @return array [$journalId => ['views'=>int, 'downloads'=>int, 'authors'=>int]]
     */
    public function getSiteJournalStatsBatch(array $journalIds): array {
        $journalIds = array_values(array_unique(array_map('intval', $journalIds)));
        $stats = [];
        foreach ($journalIds as $id) {
            $stats[$id] = ['views' => 0, 'downloads' => 0, 'authors' => 0];
        }
        if (empty($journalIds)) {
            return $stats;
        }

        // [BUGFIX] Views & downloads sekarang lewat helper yang menyertakan
        // fallback batch (tanpa filter metric_type) untuk journal yang masih 0
        // setelah filter spesifik -- mempertahankan perilaku getSiteJournalStats()
        // aslinya (baris 809 & 822) yang sempat hilang di versi batch pertama,
        // menyebabkan downloads tampil 0 saat metric_type di database tidak
        // persis cocok dengan filter spesifik.
        $this->_fillMetricStatsBatch(
            $stats, 'views', ASSOC_TYPE_ARTICLE,
            "(metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')"
        );
        $this->_fillMetricStatsBatch(
            $stats, 'downloads', ASSOC_TYPE_GALLEY,
            "(metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')"
        );

        $placeholders = implode(',', array_fill(0, count($journalIds), '?'));
        $authorsResult = $this->retrieve(
            "SELECT art.journal_id AS jid, COUNT(DISTINCT a.email) AS t
             FROM authors a JOIN articles art ON a.submission_id = art.article_id
             WHERE art.journal_id IN ($placeholders) AND art.status = 3",
            $journalIds
        );
        if ($authorsResult) {
            while (!$authorsResult->EOF) {
                $row = $authorsResult->GetRowAssoc(false);
                $stats[(int) $row['jid']]['authors'] = (int) $row['t'];
                $authorsResult->MoveNext();
            }
            $authorsResult->Close();
        }

        return $stats;
    }

    /**
     * [PERF FIX + BUGFIX] Helper batch untuk getSiteJournalStatsBatch(): isi
     * $stats[...][$field] lewat query batch dengan filter metric_type spesifik,
     * lalu jalankan SATU query batch fallback lagi (tanpa filter metric_type)
     * KHUSUS untuk journal yang masih 0 -- meniru pola fallback
     * getSiteJournalStats() aslinya, tapi tetap batched (bukan per-journal).
     *
     * @param array $stats Referensi array stats, dimodifikasi langsung.
     * @param string $field 'views' atau 'downloads'.
     * @param int $assocType ASSOC_TYPE_ARTICLE atau ASSOC_TYPE_GALLEY.
     * @param string $metricTypeFilter Klausa SQL filter metric_type (sudah termasuk kurung).
     */
    private function _fillMetricStatsBatch(array &$stats, string $field, int $assocType, string $metricTypeFilter): void {
        $journalIds = array_keys($stats);
        if (empty($journalIds)) return;

        $placeholders = implode(',', array_fill(0, count($journalIds), '?'));
        $result = $this->retrieve(
            "SELECT context_id, SUM(metric) AS t FROM metrics
             WHERE assoc_type = ? AND context_id IN ($placeholders) AND $metricTypeFilter
             GROUP BY context_id",
            array_merge([$assocType], $journalIds)
        );
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $stats[(int) $row['context_id']][$field] = (int) $row['t'];
                $result->MoveNext();
            }
            $result->Close();
        }

        $needFallback = [];
        foreach ($journalIds as $id) {
            if ($stats[$id][$field] === 0) {
                $needFallback[] = $id;
            }
        }
        if (empty($needFallback)) {
            return;
        }

        $fbPlaceholders = implode(',', array_fill(0, count($needFallback), '?'));
        $fbResult = $this->retrieve(
            "SELECT context_id, SUM(metric) AS t FROM metrics
             WHERE assoc_type = ? AND context_id IN ($fbPlaceholders)
             GROUP BY context_id",
            array_merge([$assocType], $needFallback)
        );
        if ($fbResult) {
            while (!$fbResult->EOF) {
                $row = $fbResult->GetRowAssoc(false);
                $stats[(int) $row['context_id']][$field] = (int) $row['t'];
                $fbResult->MoveNext();
            }
            $fbResult->Close();
        }
    }

    /**
     * Generate a fingerprint for the site's statistics.
     * 
     * [SMART CACHE] Ambil fingerprint MURAH untuk mendeteksi apakah data
     * agregat situs (views/downloads/authors semua jurnal) kemungkinan
     * berubah sejak terakhir dihitung -- dipakai WizdamStats::getSiteWideStats()
     * untuk cache berbasis perubahan data, bukan TTL waktu tetap.
     *
     * @return string Fingerprint string.
     */
    public function getSiteStatsFingerprint(): string {
        $maxLoadId = $this->fetchScalar("SELECT MAX(load_id) AS t FROM metrics", [], 't');

        $articleFingerprint = $this->fetchScalar(
            "SELECT MAX(date_status_modified) AS t FROM articles WHERE status = 3",
            [], 't'
        );
        $articleCount = $this->fetchScalar(
            "SELECT COUNT(*) AS t FROM articles WHERE status = 3",
            [], 't'
        );

        return md5(
            'loadid_' . ($maxLoadId ?? '0') .
            '_artmod_' . ($articleFingerprint ?? '') .
            '_artcount_' . ($articleCount ?? '0')
        );
    }

    /**
     * Fetch views, downloads, and unique authors statistics for a single journal (used for Site-Wide aggregation).
     * @param int $journalId Journal ID.
     * @return array Associative array with 'views', 'downloads', and 'authors' keys (integers).
     */
    public function getSiteJournalStats(int $journalId): array {
        $v = $this->fetchScalar(
            "SELECT SUM(metric) AS t FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')", 
            [ASSOC_TYPE_ARTICLE, (int) $journalId], 
            't'
        );
        if (empty($v)) {
            $v = $this->fetchScalar(
                "SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ?", 
                [ASSOC_TYPE_ARTICLE, (int) $journalId], 
                't'
            );
        }
        
        $d = $this->fetchScalar(
            "SELECT SUM(metric) AS t FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')", 
            [ASSOC_TYPE_GALLEY, (int) $journalId], 
            't'
        );
        if (empty($d)) {
            $d = $this->fetchScalar(
                "SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ?", 
                [ASSOC_TYPE_GALLEY, (int) $journalId], 
                't'
            );
        }
        
        $a = $this->fetchScalar(
            "SELECT COUNT(DISTINCT a.email) AS t FROM authors a JOIN articles art ON a.submission_id = art.article_id WHERE art.journal_id = ? AND art.status = 3", 
            [(int) $journalId], 
            't'
        );
        
        return [
            'views' => (int) ($v ?? 0), 
            'downloads' => (int) ($d ?? 0), 
            'authors' => (int) ($a ?? 0)
        ];
    }
    
}
?>