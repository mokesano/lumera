<?php
declare(strict_types=1);

/**
 * @defgroup classes_statistics
 */

/**
 * @file classes/statistics/MetricsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetricsDAO
 * @ingroup classes_statistics
 *
 * @brief Operations for retrieving and adding statistics data.
 */

class MetricsDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [WIZDAM SENTINEL]
     * Mendapatkan "Tanda Tangan" (Signature) Kebenaran dari Database.
     * Menggunakan MAX(load_id) sebagai penanda versi data.
     * Query sangat ringan (Index Scan) dan akurat mendeteksi perubahan data.
     * 
     * @param int|null $contextId
     * @return string Hash Signature
     */
    private function _getStorageSignature($contextId = null) {
        $params = [];
        $sql = "SELECT MAX(load_id) as last_load_id FROM metrics";
        if ($contextId) {
            $sql .= " WHERE context_id = ?";
            $params[] = (int) $contextId;
        }
        $result = $this->retrieve($sql, $params);
        
        // [WIZDAM] FIX: Type narrowing untuk $result->fields
        /** @var array|bool $fields */
        $fields = $result->fields;
        $signature = isset($fields['last_load_id']) ? (string) $fields['last_load_id'] : '0';
        
        $result->Close();
        return md5('ctx_' . ($contextId ? $contextId : 'all') . '_ver_' . $signature);
    }

    /**
     * [SEMANTIC NAMING - STRICT MODE]
     * Menghasilkan nama file flat sesuai request: wm_hash_tipe_id.json.gz
     * 
     * @param array $filters
     * @param string $requestHash
     * @return string
     */
    private function _getSemanticCachePath(array $filters, string $requestHash): string {
        import('lib.pkp.classes.cache.CacheManager');
        $baseDir = CacheManager::getFileCachePath() . DIRECTORY_SEPARATOR . 't_cache';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        // Ambil 17 karakter hash untuk identitas unik query
        $shortHash = substr($requestHash, 0, 17); 
        
        // Default
        $type = 'report';
        $id = 'general';

        // 1. Deteksi ARTIKEL
        if (isset($filters[STATISTICS_DIMENSION_SUBMISSION_ID])) {
            $val = $filters[STATISTICS_DIMENSION_SUBMISSION_ID];
            $idStr = is_array($val) ? implode('-', array_slice($val, 0, 3)) : (string) $val;
            $type = 'article';
            $id = $idStr;
        }
        // 2. Deteksi ISSUE
        elseif (isset($filters[STATISTICS_DIMENSION_ISSUE_ID])) {
            $val = $filters[STATISTICS_DIMENSION_ISSUE_ID];
            $idStr = is_array($val) ? implode('-', $val) : (string) $val;
            $type = 'issue';
            $id = $idStr;
        }
        // 3. Deteksi JURNAL
        elseif (isset($filters[STATISTICS_DIMENSION_CONTEXT_ID])) {
            $val = $filters[STATISTICS_DIMENSION_CONTEXT_ID];
            $idStr = is_array($val) ? implode('-', $val) : (string) $val;
            $type = 'journal';
            $id = $idStr;
        }

        // Sanitasi ID (buang karakter aneh agar aman di nama file)
        $id = preg_replace('/[^a-zA-Z0-9\-]/', '', $id);
        
        // FORMAT FINAL: wm_[HASH]_[TIPE]_[ID].json.gz
        return $baseDir . DIRECTORY_SEPARATOR . "wm_{$shortHash}_{$type}_{$id}.json.gz";
    }

    /**
     * Resolve sentinel values (STATISTICS_YESTERDAY, STATISTICS_CURRENT_MONTH) 
     * to their actual date values.
     * 
     * @param mixed $value
     * @return mixed
     */
    private function _resolveSentinelValue($value) {
        if ($value === STATISTICS_YESTERDAY) {
            return date('Ymd', strtotime('-1 day'));
        }
        if ($value === STATISTICS_CURRENT_MONTH) {
            return date('Ym');
        }
        return $value;
    }

    /**
     * Retrieve a range of aggregate, filtered, ordered metric values.
     * 
     * @param string|array $metricType
     * @param string|array $columns
     * @param array $filters
     * @param array $orderBy
     * @param DBResultRange|null $range
     * @param bool $nonAdditive
     * @return array|null
     */
    public function getMetrics($metricType, $columns = [], $filters = [], $orderBy = [], $range = null, $nonAdditive = true) {
        
        // 1. TENTUKAN KONTEKS & TANDA TANGAN (SIGNATURE)
        $contextId = isset($filters[STATISTICS_DIMENSION_CONTEXT_ID]) ? $filters[STATISTICS_DIMENSION_CONTEXT_ID] : null;
        $currentDbHash = $this->_getStorageSignature($contextId);

        // 2. IDENTIFIKASI REQUEST (FINGERPRINT)
        $requestParams = [
            'mt' => $metricType,
            'cols' => $columns,
            'fil' => $filters,
            'ord' => $orderBy,
            'rng' => ($range instanceof DBResultRange) ? $range->getPage() . '-' . $range->getCount() : 'all',
            'add' => $nonAdditive
        ];
        $requestHash = md5(serialize($requestParams));
        
        // [STRICT SEMANTIC PATH]
        $cacheFile = $this->_getSemanticCachePath($filters, $requestHash);

        // 3. INSPEKSI CACHE (SMART DETECTION)
        if (file_exists($cacheFile)) {
            $gzContent = @file_get_contents($cacheFile);
            if ($gzContent) {
                $payload = json_decode(@gzdecode($gzContent), true);
                
                // Cek Signature DB (Sentinel)
                if (is_array($payload) && isset($payload['meta']['data_signature']) && $payload['meta']['data_signature'] === $currentDbHash) {
                    return $payload['data']; // HIT!
                }
            }
        }

        // 4. QUERY BUILDER (LEGACY LOGIC)
        if (is_scalar($metricType)) $metricType = [$metricType];
        if (is_scalar($columns)) $columns = [$columns];
        if (!is_array($filters) || !is_array($orderBy)) return [];

        // Validasi parameter...
        if (empty($metricType)) return [];
        foreach ($metricType as $metricTypeElement) {
            if (!is_string($metricTypeElement)) return [];
        }
        
        $validColumns = [
            STATISTICS_DIMENSION_CONTEXT_ID, STATISTICS_DIMENSION_ISSUE_ID, 
            STATISTICS_DIMENSION_SUBMISSION_ID, STATISTICS_DIMENSION_COUNTRY, 
            STATISTICS_DIMENSION_REGION, STATISTICS_DIMENSION_CITY, 
            STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_ASSOC_ID, 
            STATISTICS_DIMENSION_MONTH, STATISTICS_DIMENSION_DAY, 
            STATISTICS_DIMENSION_FILE_TYPE, STATISTICS_DIMENSION_METRIC_TYPE
        ];
        $validColumns[] = STATISTICS_METRIC;

        if (count(array_diff($columns, $validColumns)) > 0) return [];
        foreach (array_keys($filters) as $filterColumn) {
            if (!in_array($filterColumn, $validColumns)) return [];
        }

        $validDirections = [STATISTICS_ORDER_ASC, STATISTICS_ORDER_DESC];
        foreach ($orderBy as $orderColumn => $direction) {
            if (!in_array($orderColumn, $validColumns)) return [];
            if (!in_array($direction, $validDirections)) return [];
        }

        if ($nonAdditive && count($metricType) !== 1) {
            if (!in_array(STATISTICS_DIMENSION_METRIC_TYPE, $columns)) $columns[] = STATISTICS_DIMENSION_METRIC_TYPE;
        }
        $filters[STATISTICS_DIMENSION_METRIC_TYPE] = $metricType;

        // Build SQL
        $selectClause = empty($columns) ? 'SELECT SUM(metric) AS metric' : "SELECT " . implode(', ', $columns) . ", SUM(metric) AS metric";
        $groupByClause = empty($columns) ? '' : "GROUP BY " . implode(', ', $columns);

        $params = []; 
        $whereClause = ''; 
        $havingClause = ''; 
        
        foreach ($filters as $column => $values) {
            $clauseFragment = '';
            if (is_array($values) && isset($values['from'])) {
                $clauseFragment = "($column BETWEEN ? AND ?)"; 
                $params[] = $values['from']; 
                $params[] = $values['to'];
            } else {
                if (is_array($values) && count($values) === 1) $values = array_pop($values);
                if (is_scalar($values)) {
                    $clauseFragment = "$column = ?"; 
                    $params[] = $values;
                } else {
                    $ph = implode(', ', array_fill(0, count($values), '?'));
                    $clauseFragment = "$column IN ($ph)"; 
                    foreach ($values as $val) $params[] = $val;
                }
            }
            if ($column === STATISTICS_METRIC) {
                $havingClause = ($havingClause ? $havingClause . ' AND ' : 'HAVING ') . $clauseFragment;
            } else {
                $whereClause = ($whereClause ? $whereClause . ' AND ' : 'WHERE ') . $clauseFragment;
            }
        }
        
        $params = array_map([$this, '_resolveSentinelValue'], $params);

        $orderByClause = '';
        if (count($orderBy) > 0) {
            $parts = []; 
            foreach ($orderBy as $c => $d) $parts[] = "$c $d"; 
            $orderByClause = 'ORDER BY ' . implode(', ', $parts);
        }

        $sql = "$selectClause FROM metrics $whereClause $groupByClause $havingClause $orderByClause";

        // 5. EKSEKUSI & PROYEKSI
        try {
            if ($range instanceof DBResultRange) {
                if ($range->getCount() > STATISTICS_MAX_ROWS) $range->setCount(STATISTICS_MAX_ROWS);
                $result = $this->retrieveRange($sql, $params, $range);
            } else {
                // [WIZDAM] FIX: retrieveLimit() parameter kedua harus array
                $result = $this->retrieveLimit($sql, $params, STATISTICS_MAX_ROWS);
            }

            $rawData = (!$result || $result->EOF) ? [] : $result->GetAll();

            // Structure Envelope Wizdam
            $envelope = [
                'meta' => [
                    'version'        => '3.0-wizdam-semantic-flat',
                    'generated_at'   => time(),
                    'data_signature' => $currentDbHash,
                    'request_hash'   => $requestHash,
                    'file_name'      => basename($cacheFile),
                    'metric_source'  => 'MetricsDAO'
                ],
                'data' => $rawData
            ];

            // Simpan Atomic GZ
            file_put_contents($cacheFile, gzencode(json_encode($envelope), 9), LOCK_EX);

            return $rawData;

        } catch (Throwable $e) {
            error_log('MetricsDAO::getMetrics gagal: ' . $e->getMessage() . ' | SQL: ' . ($sql ?? '(sql belum terbentuk)'));

            if (file_exists($cacheFile)) {
                $content = @file_get_contents($cacheFile);
                if ($content) {
                    $pl = json_decode(@gzdecode($content), true);
                    if (is_array($pl) && isset($pl['data'])) {
                        error_log('MetricsDAO::getMetrics: memakai cache lama (stale) setelah query gagal, file: ' . $cacheFile);
                        return $pl['data'];
                    }
                }
            }
            return [];
        }
    }

    /**
     * Ambil total metrik untuk banyak assoc_id sekaligus dalam SATU query
     * (dipecah otomatis per batch 500 ID). Dipakai untuk menghindari pola N+1
     * saat menampilkan daftar hasil (pencarian, browse, dsb).
     *
     * @param int $assocType ASSOC_TYPE_ARTICLE atau ASSOC_TYPE_GALLEY
     * @param array $assocIds Daftar ID yang tampil di halaman
     * @param int|null $contextId ID jurnal (null = lintas jurnal/situs)
     * @return array [$assocId => $totalViews]
     */
    public function getMetricsForAssocIds(int $assocType, array $assocIds, ?int $contextId = null): array {
        $assocIds = array_values(array_unique(array_map('intval', $assocIds)));
        if (empty($assocIds)) return [];

        $counts = array_fill_keys($assocIds, 0);

        foreach (array_chunk($assocIds, 500) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $params = [$assocType];
            $sql = "SELECT assoc_id, SUM(metric) AS metric
                    FROM metrics
                    WHERE assoc_type = ?
                      AND assoc_id IN ($placeholders)";
            foreach ($chunk as $id) { $params[] = $id; }

            if ($contextId !== null) {
                $sql .= " AND context_id = ?";
                $params[] = $contextId;
            }
            $sql .= " GROUP BY assoc_id";

            $result = $this->retrieve($sql, $params);
            if ($result) {
                while (!$result->EOF) {
                    $row = $result->GetRowAssoc(false);
                    $counts[(int) $row['assoc_id']] = (int) $row['metric'];
                    $result->MoveNext();
                }
                $result->Close();
            }
        }

        return $counts;
    }

    /**
     * Get all load ids that are associated with records filtered by the passed arguments.
     * 
     * @param int $assocType
     * @param int $assocId
     * @param string $metricType
     * @return array
     */
    public function getLoadId($assocType, $assocId, $metricType) {
        $params = [(int) $assocType, (int) $assocId, (string) $metricType];
        $result = $this->retrieve(
            'SELECT load_id FROM metrics WHERE assoc_type = ? AND assoc_id = ? AND metric_type = ? GROUP BY load_id', 
            $params
        );

        $loadIds = [];
        while (!$result->EOF) {
            $row = $result->FetchRow();
            if (is_array($row) && isset($row['load_id'])) {
                $loadIds[] = $row['load_id'];
            }
            // FetchRow() sudah otomatis move next
        }
        
        $result->Close();
        unset($result);

        return $loadIds;
    }

    /**
     * Check for the presence of any record that has the passed metric type.
     * 
     * @param string $metricType
     * @return bool
     */
    public function hasRecord($metricType) {
        $result = $this->retrieve(
            'SELECT load_id FROM metrics WHERE metric_type = ? LIMIT 1', 
            [(string) $metricType]
        );
        
        $row = $result && !$result->EOF ? $result->GetRowAssoc(false) : null;
        $returner = is_array($row) && !empty($row);
        
        $result->Close();
        unset($result);
        
        return $returner;
    }

    /**
     * Purge a load batch before re-loading it.
     * 
     * @param string $loadId
     * @return bool
     */
    public function purgeLoadBatch($loadId) {
        return $this->update('DELETE FROM metrics WHERE load_id = ?', [(string) $loadId]); 
    }

    /**
     * Purge all records associated with the passed metric type until the passed date.
     * 
     * @param string $metricType
     * @param string $toDate
     * @return bool
     */
    public function purgeRecords($metricType, $toDate) {
        return $this->update(
            'DELETE FROM metrics WHERE metric_type = ? AND day IS NOT NULL AND day <= ?', 
            [(string) $metricType, (string) $toDate]
        );
    }

    /**
     * Insert an entry into metrics table.
     * 
     * @param array $record
     * @param string|null $errorMsg
     * @return bool
     */
    public function insertRecord($record, &$errorMsg) { 
        $recordToStore = [];
        $requiredDimensions = ['load_id', 'assoc_type', 'assoc_id', 'metric_type'];
        
        foreach ($requiredDimensions as $requiredDimension) {
            if (!isset($record[$requiredDimension])) {
                $errorMsg = 'Cannot load record: missing dimension "' . $requiredDimension . '".';
                return false;
            }
            $recordToStore[$requiredDimension] = $record[$requiredDimension];
        }
        
        $recordToStore['assoc_type'] = (int) $recordToStore['assoc_type'];
        $recordToStore['assoc_id'] = (int) $recordToStore['assoc_id'];

        // Foreign key lookup
        $isArticleFile = false;
        $articleId = null;
        $issueId = null;
        $journalId = null;
        
        switch ($recordToStore['assoc_type']) {
            case ASSOC_TYPE_GALLEY:
            case ASSOC_TYPE_SUPP_FILE:
                if ($recordToStore['assoc_type'] == ASSOC_TYPE_GALLEY) {
                    /** @var ArticleGalleyDAO $galleyDao */
                    $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); 
                    $articleFile = $galleyDao ? $galleyDao->getGalley($recordToStore['assoc_id']) : null;
                    if (!($articleFile instanceof ArticleGalley)) {
                        $errorMsg = 'Cannot load record: invalid galley id.';
                        return false;
                    }
                } else {
                    /** @var SuppFileDAO $suppFileDao */
                    $suppFileDao = DAORegistry::getDAO('SuppFileDAO'); 
                    $articleFile = $suppFileDao ? $suppFileDao->getSuppFile($recordToStore['assoc_id']) : null;
                    if (!($articleFile instanceof SuppFile)) {
                        $errorMsg = 'Cannot load record: invalid supplementary file id.';
                        return false;
                    }
                }
                $articleId = $articleFile->getArticleId();
                $isArticleFile = true;
                // Fall through to retrieve article

            case ASSOC_TYPE_ARTICLE:
                if (!$isArticleFile) $articleId = $recordToStore['assoc_id'];
                /** @var PublishedArticleDAO $publishedArticleDao */
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $article = $publishedArticleDao ? $publishedArticleDao->getPublishedArticleByArticleId((int) $articleId, null, true) : null;
                if ($article instanceof PublishedArticle) {
                    $issueId = $article->getIssueId();
                } else {
                    $issueId = null;
                    /** @var ArticleDAO $articleDao */
                    $articleDao = DAORegistry::getDAO('ArticleDAO');
                    $article = $articleDao ? $articleDao->getArticle((int) $articleId, null, true) : null;
                }
                if (!($article instanceof Article)) {
                    $errorMsg = 'Cannot load record: invalid article id.';
                    return false;
                }
                $journalId = $article->getJournalId();
                break;

            case ASSOC_TYPE_ISSUE_GALLEY:
                $articleId = null;
                /** @var IssueGalleyDAO $issueGalleyDao */
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                $issueGalley = $issueGalleyDao ? $issueGalleyDao->getGalley($recordToStore['assoc_id']) : null;
                if (!($issueGalley instanceof IssueGalley)) {
                    $errorMsg = 'Cannot load record: invalid issue galley id.';
                    return false;
                }
                $issueId = $issueGalley->getIssueId();
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO'); 
                $issue = $issueDao ? $issueDao->getIssueById($issueId, null, true) : null;
                if (!($issue instanceof Issue)) {
                    $errorMsg = 'Cannot load record: issue galley without issue.';
                    return false;
                }
                $journalId = $issue->getJournalId();
                break;

            case ASSOC_TYPE_ISSUE:
                $articleId = null;
                $issueId = $recordToStore['assoc_id'];
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao ? $issueDao->getIssueByPubId('publisher-id', $issueId, null, true) : null;
                if (!$issue) {
                    $issue = $issueDao ? $issueDao->getIssueById($issueId, null, true) : null;
                }
                if (!($issue instanceof Issue)) {
                    $errorMsg = 'Cannot load record: invalid issue id.';
                    return false;
                }
                $journalId = $issue->getJournalId();
                break;
                
            case ASSOC_TYPE_JOURNAL:
                $articleId = $issueId = null;
                /** @var JournalDAO $journalDao */
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journal = $journalDao ? $journalDao->getById($recordToStore['assoc_id']) : null;
                if (!$journal) {
                    $errorMsg = 'Cannot load record: invalid journal id.';
                    return false;
                }
                $journalId = $recordToStore['assoc_id'];
                break;
                
            default:
                $errorMsg = 'Cannot load record: invalid association type.';
                return false;
        }
        
        $recordToStore['context_id'] = (int) $journalId;
        $recordToStore['issue_id'] = $issueId !== null ? (int) $issueId : null;
        $recordToStore['submission_id'] = $articleId !== null ? (int) $articleId : null;

        // Time dimension validation
        if (isset($record['day'])) {
            if (!PKPString::regexp_match('/[0-9]{8}/', $record['day'])) {
                $errorMsg = 'Cannot load record: invalid date.';
                return false;
            }
            $recordToStore['day'] = $record['day'];
            $recordToStore['month'] = substr($record['day'], 0, 6);
            if (isset($record['month']) && $recordToStore['month'] != $record['month']) {
                $errorMsg = 'Cannot load record: invalid month.';
                return false;
            }
        } elseif (isset($record['month'])) {
            if (!PKPString::regexp_match('/[0-9]{6}/', $record['month'])) {
                $errorMsg = 'Cannot load record: invalid month.';
                return false;
            }
            $recordToStore['month'] = $record['month'];
        } else {
            $errorMsg = 'Cannot load record: Missing time dimension.';
            return false;
        }

        // Optional fields
        if (isset($record['file_type']) && $record['file_type']) $recordToStore['file_type'] = (int) $record['file_type'];
        if (isset($record['country_id'])) $recordToStore['country_id'] = (string) $record['country_id'];
        if (isset($record['region'])) $recordToStore['region'] = (string) $record['region'];
        if (isset($record['city'])) $recordToStore['city'] = (string) $record['city'];

        // Metric validation
        if (!isset($record['metric'])) {
            $errorMsg = 'Cannot load record: metric is missing.';
            return false;
        }
        if (!is_numeric($record['metric'])) {
            $errorMsg = 'Cannot load record: invalid metric.';
            return false;
        }
        $recordToStore['metric'] = (int) $record['metric'];

        // Execute Insert
        $fields = implode(', ', array_keys($recordToStore));
        $placeholders = implode(', ', array_pad([], count($recordToStore), '?'));
        $params = array_values($recordToStore);
        return $this->update("INSERT INTO metrics ($fields) VALUES ($placeholders)", $params);
    }

}
?>