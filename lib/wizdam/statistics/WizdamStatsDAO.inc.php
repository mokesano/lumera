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

if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);

class WizdamStatsDAO extends DAO {

    /**
     * [HELPER] Mengambil satu nilai skalar dari hasil query.
     * Berfungsi sebagai wrapper untuk menghindari error tipe data pada ADORecordSet::$fields.
     * @param string $sql Query SQL yang akan dieksekusi.
     * @param array $params Parameter binding untuk query (default: []).
     * @param string|null $key Nama kolom spesifik yang ingin diambil. Jika null, mengambil kolom pertama (default: null).
     * @return mixed|null Nilai skalar dari kolom yang dituju, atau null jika query kosong/gagal.
     */
    protected function fetchScalar(string $sql, array $params = [], ?string $key = null) {
        $result = $this->retrieve($sql, $params);
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            $result->Close();
            if (is_array($fields)) {
                if ($key !== null && isset($fields[$key])) return $fields[$key];
                return reset($fields);
            }
        }
        if ($result) $result->Close();
        return null;
    }

    /**
     * [HELPER] Mengambil baris data saat ini dari result set sebagai array asosiatif/numerik.
     * @param ADORecordSet|object|null $result Objek result set dari ADOdb.
     * @return array|null Array berisi data baris, atau null jika EOF/gagal.
     */
    protected function fetchRow($result): ?array {
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            if (is_array($fields)) return $fields;
        }
        return null;
    }

    /**
     * Memeriksa keberadaan tabel dan kolom statistik yang diperlukan di database.
     * @return array Array asosiatif berisi status boolean untuk: 
     * 'metricsTableExists', 'metricsColumns' (array), 
     * 'articleStatsExists', 'galleyStatsExists', dan 'editDecisionsExists'.
     */
    public function checkDatabaseStructure(): array {
        $info = [
            'metricsTableExists' => false, 'metricsColumns' => [],
            'articleStatsExists' => false, 'galleyStatsExists' => false, 'editDecisionsExists' => false
        ];
        try {
            $r = $this->retrieve("SHOW TABLES LIKE 'metrics'");
            $info['metricsTableExists'] = ($r->RecordCount() > 0); $r->Close();
            if ($info['metricsTableExists']) {
                $r = $this->retrieve("SHOW COLUMNS FROM metrics");
                while ($row = $this->fetchRow($r)) { $info['metricsColumns'][] = (string)$row[0]; $r->MoveNext(); }
                $r->Close();
            }
            $r1 = $this->retrieve("SHOW TABLES LIKE 'article_view_stats'"); $info['articleStatsExists'] = ($r1->RecordCount() > 0); $r1->Close();
            $r2 = $this->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'"); $info['galleyStatsExists'] = ($r2->RecordCount() > 0); $r2->Close();
            $r3 = $this->retrieve("SHOW TABLES LIKE 'edit_decisions'"); $info['editDecisionsExists'] = ($r3->RecordCount() > 0); $r3->Close();
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStatsDAO DB Check Error: " . $e->getMessage());
        }
        return $info;
    }

    /**
     * Mengambil total views dan downloads untuk sebuah jurnal dari tabel metrics.
     * @param int $journalId ID jurnal yang akan diambil statistiknya.
     * @param bool $metricsTableExists Status keberadaan tabel metrics.
     * @return array Array asosiatif dengan kunci 'views' dan 'downloads' (integer).
     */
    public function getCoreViewsDownloads(int $journalId, bool $metricsTableExists): array {
        if (!$metricsTableExists) return ['views' => 0, 'downloads' => 0];
        $views = $this->fetchScalar("SELECT SUM(metric) AS total FROM metrics WHERE context_id = ? AND assoc_type = ?", [$journalId, ASSOC_TYPE_ARTICLE], 'total');
        $downloads = $this->fetchScalar("SELECT SUM(metric) AS total FROM metrics WHERE context_id = ? AND assoc_type = ?", [$journalId, ASSOC_TYPE_GALLEY], 'total');
        return ['views' => (int)($views ?? 0), 'downloads' => (int)($downloads ?? 0)];
    }

    /**
     * Menentukan nama kolom tanggal yang tersedia di tabel metrics untuk query tahunan.
     * @param array $metricsColumns Daftar nama kolom dari tabel metrics.
     * @return string Nama kolom tanggal ('day' atau 'load_time'), atau string kosong jika tidak ada.
     */
    public function getDateColumn(array $metricsColumns): string {
        if (in_array('day', $metricsColumns, true)) return 'day';
        if (in_array('load_time', $metricsColumns, true)) return 'load_time';
        return '';
    }

    /**
     * Mengambil agregasi views dan downloads per tahun dari tabel metrics.
     * @param int $journalId ID jurnal.
     * @param string $dateColumn Nama kolom tanggal yang valid ('day' atau 'load_time').
     * @return array Array asosiatif multidimensi dengan format [tahun => ['views' => int, 'downloads' => int]].
     */
    public function getYearlyViewsDownloads(int $journalId, string $dateColumn): array {
        $data = [];
        if (empty($dateColumn)) return $data;
        $r = $this->retrieve("SELECT YEAR($dateColumn) as year, SUM(metric) as views FROM metrics WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL GROUP BY YEAR($dateColumn)", [$journalId, ASSOC_TYPE_ARTICLE]);
        while ($row = $this->fetchRow($r)) { $data[(int)$row['year']]['views'] = (int)$row['views']; $r->MoveNext(); } if ($r) $r->Close();
        
        $r = $this->retrieve("SELECT YEAR($dateColumn) as year, SUM(metric) as downloads FROM metrics WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL GROUP BY YEAR($dateColumn)", [$journalId, ASSOC_TYPE_GALLEY]);
        while ($row = $this->fetchRow($r)) { $y = (int)$row['year']; if(!isset($data[$y])) $data[$y]=[]; $data[$y]['downloads'] = (int)$row['downloads']; $r->MoveNext(); } if ($r) $r->Close();
        return $data;
    }

    /**
     * Mengambil jumlah submisi artikel per tahun.
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif dengan format [tahun => ['submissions' => int]].
     */
    public function getYearlySubmissions(int $journalId): array {
        $data = [];
        $r = $this->retrieve("SELECT YEAR(date_submitted) as year, COUNT(*) as submissions FROM articles WHERE journal_id = ? GROUP BY YEAR(date_submitted)", [$journalId]);
        while ($row = $this->fetchRow($r)) { $data[(int)$row['year']]['submissions'] = (int)$row['submissions']; $r->MoveNext(); } if ($r) $r->Close();
        return $data;
    }

    /**
     * Mengambil jumlah artikel yang dipublikasikan per tahun.
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif dengan format [tahun => ['published' => int]].
     */
    public function getYearlyPublished(int $journalId): array {
        $data = [];
        $r = $this->retrieve("SELECT YEAR(pa.date_published) as year, COUNT(*) as published FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? GROUP BY YEAR(pa.date_published)", [$journalId]);
        while ($row = $this->fetchRow($r)) { $data[(int)$row['year']]['published'] = (int)$row['published']; $r->MoveNext(); } if ($r) $r->Close();
        return $data;
    }

    /**
     * Menghitung total artikel yang di-review, diterima (accepted), dan ditolak (declined).
     * @param int $journalId ID jurnal.
     * @param int $currentYear Tahun saat ini (data di bawah tahun ini yang dihitung).
     * @return array Array asosiatif dengan kunci 'reviewed', 'accepted', dan 'declined' (integer).
     */
    public function getAcceptDeclineTotals(int $journalId, int $currentYear): array {
        $rev = $this->fetchScalar("SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status IN (2, 3, 4)", [$journalId, $currentYear], 'total');
        $acc = $this->fetchScalar("SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 3", [$journalId, $currentYear], 'total');
        $dec = $this->fetchScalar("SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 4", [$journalId, $currentYear], 'total');
        return ['reviewed' => (int)($rev??0), 'accepted' => (int)($acc??0), 'declined' => (int)($dec??0)];
    }

    /**
     * Mengambil data mentah durasi review (dalam hari) untuk semua waktu (All-Time).
     * @param int $journalId ID jurnal.
     * @param int $currentYear Tahun saat ini (mengecualikan tahun berjalan).
     * @return array Array numerik berisi durasi review dalam hari (float).
     */
    // Timeline All-Time
    public function getDaysToReviewAllTime(int $journalId, int $currentYear): array {
        $days = [];
        // [BUGFIX] Tambahkan JOIN articles untuk filter journal_id yang hilang di kode asli!
        $r = $this->retrieve("SELECT DATEDIFF(ra.date_completed, ra.date_notified) AS days FROM review_assignments ra JOIN articles a ON ra.submission_id = a.article_id WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(ra.date_notified) < ?", [$journalId, $currentYear]);
        while ($row = $this->fetchRow($r)) { $d = (float)$row['days']; if ($d > 0) $days[] = $d; $r->MoveNext(); } if ($r) $r->Close();
        return $days;
    }

    /**
     * Mengambil data mentah durasi dari submisi hingga publikasi (dalam hari) untuk semua waktu.
     * @param int $journalId ID jurnal.
     * @param int $currentYear Tahun saat ini.
     * @return array Array numerik berisi durasi dalam hari (float).
     */
    public function getDaysToPublicationAllTime(int $journalId, int $currentYear): array {
        $days = [];
        $r = $this->retrieve("SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND pa.date_published IS NOT NULL", [$journalId, $currentYear]);
        while ($row = $this->fetchRow($r)) { $d = (float)$row['days']; if ($d > 0) $days[] = $d; $r->MoveNext(); } if ($r) $r->Close();
        return $days;
    }

    /**
     * Mengambil data mentah durasi dari submisi hingga keputusan pertama (dalam hari).
     * @param int $journalId ID jurnal.
     * @param int $currentYear Tahun saat ini.
     * @param bool $hasEditDecisions Status keberadaan tabel edit_decisions.
     * @return array Array numerik berisi durasi dalam hari (float).
     */
    public function getFirstDecisionDaysAllTime(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $days = [];
        if ($hasEditDecisions) {
            $r = $this->retrieve("SELECT a.date_submitted, MIN(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.date_decided IS NOT NULL GROUP BY a.article_id", [$journalId, $currentYear]);
            while ($row = $this->fetchRow($r)) { $s=strtotime((string)$row['date_submitted']); $d=strtotime((string)$row['d']); if($s&&$d){$diff=round(($d-$s)/86400); if($diff>0)$days[]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        } else {
            $r = $this->retrieve("SELECT a.date_submitted, a.date_status_modified FROM articles a WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3", [$journalId, $currentYear]);
            while ($row = $this->fetchRow($r)) { $s=strtotime((string)$row['date_submitted']); $d=strtotime((string)$row['date_status_modified']); if($s&&$d){$diff=round(($d-$s)/86400); if($diff>0)$days[]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        }
        return $days;
    }

    /**
     * Mengambil data mentah durasi dari submisi hingga penerimaan (acceptance) (dalam hari).
     * @param int $journalId ID jurnal.
     * @param int $currentYear Tahun saat ini.
     * @param bool $hasEditDecisions Status keberadaan tabel edit_decisions.
     * @return array Array numerik berisi durasi dalam hari (float).
     */
    public function getSubmissionToAcceptanceDaysAllTime(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $days = [];
        if ($hasEditDecisions) {
            $r = $this->retrieve("SELECT a.date_submitted, MAX(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.decision = 1 GROUP BY a.article_id", [$journalId, $currentYear]);
            while ($row = $this->fetchRow($r)) { $s=strtotime((string)$row['date_submitted']); $d=strtotime((string)$row['d']); if($s&&$d){$diff=round(($d-$s)/86400); if($diff>0)$days[]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        } else {
            $r = $this->retrieve("SELECT a.date_submitted, a.date_status_modified FROM articles a WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3", [$journalId, $currentYear]);
            while ($row = $this->fetchRow($r)) { $s=strtotime((string)$row['date_submitted']); $d=strtotime((string)$row['date_status_modified']); if($s&&$d){$diff=round(($d-$s)/86400); if($diff>0)$days[]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        }
        return $days;
    }

    /**
     * Mengambil data mentah durasi dari penerimaan (acceptance) hingga publikasi (dalam hari).
     * @param int $journalId ID jurnal.
     * @param int $currentYear Tahun saat ini.
     * @param bool $hasEditDecisions Status keberadaan tabel edit_decisions.
     * @return array Array numerik berisi durasi dalam hari (float).
     */
    public function getAcceptanceToPublicationDaysAllTime(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $days = [];
        if ($hasEditDecisions) {
            $r = $this->retrieve("SELECT MAX(ed.date_decided) as d, pa.date_published FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.decision = 1 AND pa.date_published IS NOT NULL GROUP BY a.article_id", [$journalId, $currentYear]);
            while ($row = $this->fetchRow($r)) { $d=strtotime((string)$row['d']); $p=strtotime((string)$row['date_published']); if($d&&$p){$diff=round(($p-$d)/86400); if($diff>0)$days[]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        } else {
            $r = $this->retrieve("SELECT a.date_status_modified, pa.date_published FROM articles a JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3 AND pa.date_published IS NOT NULL", [$journalId, $currentYear]);
            while ($row = $this->fetchRow($r)) { $d=strtotime((string)$row['date_status_modified']); $p=strtotime((string)$row['date_published']); if($d&&$p){$diff=round(($p-$d)/86400); if($diff>0)$days[]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        }
        return $days;
    }

    /**
     * Mengambil data mentah timeline tahunan untuk berbagai metrik editorial.
     * @param int $journalId ID jurnal.
     * @param int $year Tahun spesifik yang akan diambil datanya.
     * @param bool $hasEditDecisions Status keberadaan tabel edit_decisions.
     * @return array Array asosiatif berisi array numerik durasi untuk: 'review', 'publication', 'firstDecision', 'acceptance', 'acceptanceToPub'.
     */
    public function getYearlyTimelineData(int $journalId, int $year, bool $hasEditDecisions): array {
        $data = ['review' => [], 'publication' => [], 'firstDecision' => [], 'acceptance' => [], 'acceptanceToPub' => []];
        
        $r = $this->retrieve("SELECT DATEDIFF(ra.date_completed, ra.date_notified) AS days FROM review_assignments ra JOIN articles a ON (ra.submission_id = a.article_id) WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(ra.date_completed) = ?", [$journalId, $year]);
        while ($row = $this->fetchRow($r)) { $d=(float)$row['days']; if($d>0)$data['review'][]=$d; $r->MoveNext(); } if($r)$r->Close();

        $r = $this->retrieve("SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND YEAR(pa.date_published) = ? AND pa.date_published IS NOT NULL", [$journalId, $year]);
        while ($row = $this->fetchRow($r)) { $d=(float)$row['days']; if($d>0)$data['publication'][]=$d; $r->MoveNext(); } if($r)$r->Close();

        if ($hasEditDecisions) {
            $r = $this->retrieve("SELECT a.date_submitted, MIN(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(ed.date_decided) = ? GROUP BY a.article_id", [$journalId, $year]);
            while ($row = $this->fetchRow($r)) { $s=strtotime((string)$row['date_submitted']); $d=strtotime((string)$row['d']); if($s&&$d){$diff=round(($d-$s)/86400); if($diff>0)$data['firstDecision'][]=$diff;} $r->MoveNext(); } if($r)$r->Close();

            $r = $this->retrieve("SELECT a.date_submitted, MAX(ed.date_decided) as d FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(ed.date_decided) = ? AND ed.decision = 1 GROUP BY a.article_id", [$journalId, $year]);
            while ($row = $this->fetchRow($r)) { $s=strtotime((string)$row['date_submitted']); $d=strtotime((string)$row['d']); if($s&&$d){$diff=round(($d-$s)/86400); if($diff>0)$data['acceptance'][]=$diff;} $r->MoveNext(); } if($r)$r->Close();

            $r = $this->retrieve("SELECT MAX(ed.date_decided) as d, pa.date_published FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(pa.date_published) = ? AND ed.decision = 1 GROUP BY a.article_id", [$journalId, $year]);
            while ($row = $this->fetchRow($r)) { $d=strtotime((string)$row['d']); $p=strtotime((string)$row['date_published']); if($d&&$p){$diff=round(($p-$d)/86400); if($diff>0)$data['acceptanceToPub'][]=$diff;} $r->MoveNext(); } if($r)$r->Close();
        }
        return $data;
    }

    /**
     * Menghitung total artikel yang dipublikasikan dan total terbitan (issues) yang aktif.
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif dengan kunci 'articles' dan 'issues' (integer).
     */
    public function getTotalArticlesAndIssues(int $journalId): array {
        $a = $this->fetchScalar("SELECT COUNT(pa.article_id) AS total FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id WHERE a.journal_id = ?", [$journalId], 'total');
        $i = $this->fetchScalar("SELECT COUNT(*) AS total FROM issues WHERE journal_id = ? AND published = 1", [$journalId], 'total');
        return ['articles' => (int)($a??0), 'issues' => (int)($i??0)];
    }

    /**
     * Mengambil data tahun publikasi terakhir dan jumlah artikel pada tahun tersebut.
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif dengan kunci 'year' (string/int) dan 'count' (integer).
     */
    public function getLastPublicationData(int $journalId): array {
        $r = $this->retrieve("SELECT YEAR(date_published) as y, COUNT(*) as c FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND pa.date_published IS NOT NULL GROUP BY YEAR(date_published) ORDER BY y DESC LIMIT 1", [$journalId]);
        $row = $this->fetchRow($r); if($r)$r->Close();
        return $row ? ['year' => $row['y'], 'count' => (int)$row['c']] : ['year' => '', 'count' => 0];
    }

    /**
     * Mendeteksi tahun terawal artikel disubmit ke jurnal (untuk rentang grafik).
     * @param int $journalId ID jurnal.
     * @param int $fallback Tahun fallback jika tidak ada data.
     * @return int Tahun terawal submisi.
     */
    public function getStartYear(int $journalId, int $fallback): int {
        $y = $this->fetchScalar("SELECT MIN(YEAR(date_submitted)) AS y FROM articles WHERE journal_id = ?", [$journalId], 'y');
        return $y ? (int)$y : $fallback;
    }

    /**
     * Mengambil metrik dasar untuk keperluan pembuatan hash deteksi perubahan data (Smart Cache).
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif berisi metrik: 'total_articles', 'total_published', 'total_views', 'last_article_mod'.
     */
    public function getHashMetrics(int $journalId): array {
        $m = [];
        $m['total_articles'] = (int)($this->fetchScalar("SELECT COUNT(*) as t FROM articles WHERE journal_id = ?", [$journalId], 't') ?? 0);
        $m['total_published'] = (int)($this->fetchScalar("SELECT COUNT(*) as t FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ?", [$journalId], 't') ?? 0);
        $v = $this->fetchScalar("SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')", [ASSOC_TYPE_ARTICLE, $journalId], 't');
        if (empty($v)) $v = $this->fetchScalar("SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ?", [ASSOC_TYPE_ARTICLE, $journalId], 't');
        $m['total_views'] = (int)($v ?? 0);
        $m['last_article_mod'] = $this->fetchScalar("SELECT MAX(last_modified) as t FROM articles WHERE journal_id = ?", [$journalId], 't') ?? '';
        return $m;
    }

    /**
     * Mengambil statistik views, downloads, dan authors unik untuk satu jurnal (digunakan untuk agregasi Site-Wide).
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif dengan kunci 'views', 'downloads', dan 'authors' (integer).
     */
    public function getSiteJournalStats(int $journalId): array {
        $v = $this->fetchScalar("SELECT SUM(metric) AS t FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')", [ASSOC_TYPE_ARTICLE, $journalId], 't');
        if (empty($v)) $v = $this->fetchScalar("SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ?", [ASSOC_TYPE_ARTICLE, $journalId], 't');
        
        $d = $this->fetchScalar("SELECT SUM(metric) AS t FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')", [ASSOC_TYPE_GALLEY, $journalId], 't');
        if (empty($d)) $d = $this->fetchScalar("SELECT SUM(metric) as t FROM metrics WHERE assoc_type = ? AND context_id = ?", [ASSOC_TYPE_GALLEY, $journalId], 't');
        
        $a = $this->fetchScalar("SELECT COUNT(DISTINCT a.email) AS t FROM authors a JOIN articles art ON a.submission_id = art.article_id WHERE art.journal_id = ? AND art.status = 3", [$journalId], 't');
        
        return ['views' => (int)($v??0), 'downloads' => (int)($d??0), 'authors' => (int)($a??0)];
    }
}
?>