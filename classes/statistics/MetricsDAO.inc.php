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
 *
 * [WIZDAM EDITION - ENTERPRISE PROTOCOL]
 * - Implements Sentinel Signature Caching (State-Based Invalidation)
 * - Atomic JSON.GZ Storage
 * - Fail-Fast Database Architecture
 * - PHP 8.x Strict Compatibility
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
        $params = array();
        $sql = "SELECT MAX(load_id) as last_load_id FROM metrics";
        if ($contextId) {
            $sql .= " WHERE context_id = ?";
            $params[] = (int) $contextId;
        }
        $result = $this->retrieve($sql, $params);
        $signature = isset($result->fields['last_load_id']) ? $result->fields['last_load_id'] : '0';
        $result->Close();
        return md5('ctx_' . ($contextId ? $contextId : 'all') . '_ver_' . $signature);
    }

    /**
     * [SEMANTIC NAMING - STRICT MODE]
     * Menghasilkan nama file flat sesuai request: wm_hash_tipe_id.json.gz
     */
    private function _getSemanticCachePath($filters, $requestHash) {
        // Lokasi folder tunggal (Flat Storage)
        $baseDir = 'cache/t_cache';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        // Ambil 8 karakter hash untuk identitas unik query
        $shortHash = substr($requestHash, 0, 17); 
        
        // Default
        $type = 'report';
        $id = 'general';

        // 1. Deteksi ARTIKEL
        if (isset($filters[STATISTICS_DIMENSION_SUBMISSION_ID])) {
            $val = $filters[STATISTICS_DIMENSION_SUBMISSION_ID];
            $idStr = is_array($val) ? implode('-', array_slice($val, 0, 3)) : $val;
            
            $type = 'article';
            $id = $idStr;
        }
        // 2. Deteksi ISSUE
        elseif (isset($filters[STATISTICS_DIMENSION_ISSUE_ID])) {
            $val = $filters[STATISTICS_DIMENSION_ISSUE_ID];
            $idStr = is_array($val) ? implode('-', $val) : $val;
            
            $type = 'issue';
            $id = $idStr;
        }
        // 3. Deteksi JURNAL
        elseif (isset($filters[STATISTICS_DIMENSION_CONTEXT_ID])) {
            $val = $filters[STATISTICS_DIMENSION_CONTEXT_ID];
            $idStr = is_array($val) ? implode('-', $val) : $val;
            
            $type = 'journal';
            $id = $idStr;
        }

        // Sanitasi ID (buang karakter aneh agar aman di nama file)
        $id = preg_replace('/[^a-zA-Z0-9\-]/', '', $id);
        
        // FORMAT FINAL: wm_[HASH]_[TIPE]_[ID].json.gz
        // Contoh: wm_b7f01c_artikel_1083.json.gz
        return $baseDir . DIRECTORY_SEPARATOR . "wm_{$shortHash}_{$type}_{$id}.json.gz";
    }

    /**
     * Retrieve a range of aggregate, filtered, ordered metric values.
     * * [WIZDAM INTELLIGENT BROKER]
     * Logic:
     * 1. Cek Signature DB.
     * 2. Cek Signature di dalam File Cache.
     * 3. Jika Cocok -> Return Cache (Tanpa Query DB).
     * 4. Jika Beda -> Query DB -> Update Cache -> Return Data.
     *
     * @param $metricType string|array metrics selection
     * @param $columns string|array column (aggregation level) selection
     * @param $filters array report-level filter selection
     * @param $orderBy array order criteria
     * @param $range null|DBResultRange paging specification
     * @param $nonAdditive boolean
     * @return null|array
     */
    public function getMetrics($metricType, $columns = array(), $filters = array(), $orderBy = array(), $range = null, $nonAdditive = true) {
        
        // 1. TENTUKAN KONTEKS & TANDA TANGAN (SIGNATURE)
        $contextId = isset($filters[STATISTICS_DIMENSION_CONTEXT_ID]) ? $filters[STATISTICS_DIMENSION_CONTEXT_ID] : null;
        $currentDbHash = $this->_getStorageSignature($contextId);

        // 2. IDENTIFIKASI REQUEST (FINGERPRINT)
        $requestParams = array(
            'mt' => $metricType,
            'cols' => $columns,
            'fil' => $filters,
            'ord' => $orderBy,
            'rng' => ($range instanceof DBResultRange) ? $range->getPage() . '-' . $range->getCount() : 'all',
            'add' => $nonAdditive
        );
        $requestHash = md5(serialize($requestParams));
        
        // [STRICT SEMANTIC PATH]
        $cacheFile = $this->_getSemanticCachePath($filters, $requestHash);

        // 3. INSPEKSI CACHE (SMART DETECTION)
        if (file_exists($cacheFile)) {
            $gzContent = @file_get_contents($cacheFile);
            if ($gzContent) {
                $payload = json_decode(@gzdecode($gzContent), true);
                
                // Cek Signature DB (Sentinel)
                if (isset($payload['meta']['data_signature']) && $payload['meta']['data_signature'] === $currentDbHash) {
                    return $payload['data']; // HIT!
                }
            }
        }

        // 4. QUERY BUILDER (LEGACY LOGIC)
        if (is_scalar($metricType)) $metricType = array($metricType);
        if (is_scalar($columns)) $columns = array($columns);
        if (!is_array($filters) || !is_array($orderBy)) return array();

        // Validasi parameter...
        foreach ($metricType as $metricTypeElement) {
            if (!is_string($metricTypeElement)) return array();
        }
        $validColumns = array(STATISTICS_DIMENSION_CONTEXT_ID, STATISTICS_DIMENSION_ISSUE_ID, STATISTICS_DIMENSION_SUBMISSION_ID, STATISTICS_DIMENSION_COUNTRY, STATISTICS_DIMENSION_REGION, STATISTICS_DIMENSION_CITY, STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_ASSOC_ID, STATISTICS_DIMENSION_MONTH, STATISTICS_DIMENSION_DAY, STATISTICS_DIMENSION_FILE_TYPE, STATISTICS_DIMENSION_METRIC_TYPE);
        $validColumns[] = STATISTICS_METRIC;

        if (count(array_diff($columns, $validColumns)) > 0) return array();
        if ($nonAdditive && count($metricType) !== 1) {
            if (!in_array(STATISTICS_DIMENSION_METRIC_TYPE, $columns)) $columns[] = STATISTICS_DIMENSION_METRIC_TYPE;
        }
        $filters[STATISTICS_DIMENSION_METRIC_TYPE] = $metricType;

        // Build SQL
        $selectClause = empty($columns) ? 'SELECT SUM(metric) AS metric' : "SELECT " . implode(', ', $columns) . ", SUM(metric) AS metric";
        $groupByClause = empty($columns) ? '' : "GROUP BY " . implode(', ', $columns);

        $params = array(); $whereClause = ''; $havingClause = ''; $isFirst = true;
        foreach ($filters as $column => $values) {
            $clauseFragment = '';
             if (is_array($values) && isset($values['from'])) {
                $clauseFragment = "($column BETWEEN ? AND ?)"; $params[] = $values['from']; $params[] = $values['to'];
            } else {
                if (is_array($values) && count($values) === 1) $values = array_pop($values);
                if (is_scalar($values)) {
                    $clauseFragment = "$column = ?"; $params[] = $values;
                } else {
                    $ph = implode(', ', array_fill(0, count($values), '?'));
                    $clauseFragment = "$column IN ($ph)"; foreach ($values as $val) $params[] = $val;
                }
            }
            if ($column === STATISTICS_METRIC) $havingClause = ($havingClause ? $havingClause . ' AND ' : 'HAVING ') . $clauseFragment;
            else $whereClause = ($whereClause ? $whereClause . ' AND ' : 'WHERE ') . $clauseFragment;
        }
        
        $currentTime = array(STATISTICS_YESTERDAY => date('Ymd', strtotime('-1 day')), STATISTICS_CURRENT_MONTH => date('Ym'));
        foreach ($currentTime as $c => $t) {
             foreach (array_keys($params, $c) as $k) $params[$k] = $t;
        }

        $orderByClause = '';
        if (count($orderBy) > 0) {
            $parts = []; foreach ($orderBy as $c => $d) $parts[] = "$c $d"; $orderByClause = 'ORDER BY ' . implode(', ', $parts);
        }

        $sql = "$selectClause FROM metrics $whereClause $groupByClause $havingClause $orderByClause";

        // 5. EKSEKUSI & PROYEKSI
        try {
            if ($range instanceof DBResultRange) {
                if ($range->getCount() > STATISTICS_MAX_ROWS) $range->setCount(STATISTICS_MAX_ROWS);
                $result = $this->retrieveRange($sql, $params, $range);
            } else {
                $result = $this->retrieveLimit($sql, $params, STATISTICS_MAX_ROWS);
            }

            $rawData = (!$result || $result->EOF) ? array() : $result->GetAll();

            // Structure Envelope Wizdam
            $envelope = array(
                'meta' => array(
                    'version'        => '3.0-wizdam-semantic-flat',
                    'generated_at'   => time(),
                    'data_signature' => $currentDbHash,
                    'request_hash'   => $requestHash,
                    'file_name'      => basename($cacheFile),
                    'metric_source'  => 'MetricsDAO'
                ),
                'data' => $rawData
            );

            // Simpan Atomic GZ
            file_put_contents($cacheFile, gzencode(json_encode($envelope), 9), LOCK_EX);

            return $rawData;

        } catch (Throwable $e) {
            if (file_exists($cacheFile)) {
                $content = @file_get_contents($cacheFile);
                if ($content) {
                    $pl = json_decode(@gzdecode($content), true);
                    return isset($pl['data']) ? $pl['data'] : array();
                }
            }
            return array();
        }
    }

    /**
     * Get all load ids that are associated with records filtered by the passed arguments.
     * [LEGACY METHOD PRESERVED]
     */
    public function getLoadId($assocType, $assocId, $metricType) {
        $params = array($assocType, $assocId, $metricType);
        $result = $this->retrieve('SELECT load_id FROM metrics WHERE assoc_type = ? AND assoc_id = ? AND metric_type = ? GROUP BY load_id', $params);

        $loadIds = array();
        while (!$result->EOF) {
            $row = $result->FetchRow();
            $loadIds[] = $row['load_id'];
        }

        return $loadIds;
    }

    /**
     * Check for the presence of any record that has the passed metric type.
     * [LEGACY METHOD PRESERVED]
     */
    public function hasRecord($metricType) {
        $result = $this->retrieve('SELECT load_id FROM metrics WHERE metric_type = ? LIMIT 1', array($metricType));
        $row = $result->GetRowAssoc();
        return ($row) ? true : false;
    }

    /**
     * Purge a load batch before re-loading it.
     * [LEGACY METHOD PRESERVED]
     */
    public function purgeLoadBatch($loadId) {
        $this->update('DELETE FROM metrics WHERE load_id = ?', $loadId); 
    }

    /**
     * Purge all records associated with the passed metric type until the passed date.
     * [LEGACY METHOD PRESERVED]
     */
    public function purgeRecords($metricType, $toDate) {
        $this->update('DELETE FROM metrics WHERE metric_type = ? AND day IS NOT NULL AND day <= ?', array($metricType, $toDate));
    }

    /**
     * Insert an entry into metrics table.
     * [WIZDAM FIX] Modernized with strict type checking and instanceof
     */
    public function insertRecord($record, $errorMsg) { 
        $recordToStore = array();
        $requiredDimensions = array('load_id', 'assoc_type', 'assoc_id', 'metric_type');
        foreach ($requiredDimensions as $requiredDimension) {
            if (!isset($record[$requiredDimension])) {
                $errorMsg = 'Cannot load record: missing dimension "' . $requiredDimension . '".';
                return false;
            }
            $recordToStore[$requiredDimension] = $record[$requiredDimension];
        }
        
        $recordToStore['assoc_type'] = (int)$recordToStore['assoc_type'];
        $recordToStore['assoc_id'] = (int)$recordToStore['assoc_id'];

        // Foreign key lookup
        $isArticleFile = false;
        switch($recordToStore['assoc_type']) {
            case ASSOC_TYPE_GALLEY:
            case ASSOC_TYPE_SUPP_FILE:
                if ($recordToStore['assoc_type'] == ASSOC_TYPE_GALLEY) {
                    $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); 
                    $articleFile = $galleyDao->getGalley($recordToStore['assoc_id']);
                    if (!($articleFile instanceof ArticleGalley)) {
                        $errorMsg = 'Cannot load record: invalid galley id.';
                        return false;
                    }
                } else {
                    $suppFileDao = DAORegistry::getDAO('SuppFileDAO'); 
                    $articleFile = $suppFileDao->getSuppFile($recordToStore['assoc_id']);
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
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId, null, true);
                if ($article instanceof PublishedArticle) {
                    $issueId = $article->getIssueId();
                } else {
                    $issueId = null;
                    $articleDao = DAORegistry::getDAO('ArticleDAO');
                    $article = $articleDao->getArticle($articleId, null, true);
                }
                if (!($article instanceof Article)) {
                    $errorMsg = 'Cannot load record: invalid article id.';
                    return false;
                }
                $journalId = $article->getJournalId();
                break;

            case ASSOC_TYPE_ISSUE_GALLEY:
                $articleId = null;
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                $issueGalley = $issueGalleyDao->getGalley($recordToStore['assoc_id']);
                if (!($issueGalley instanceof IssueGalley)) {
                    $errorMsg = 'Cannot load record: invalid issue galley id.';
                    return false;
                }
                $issueId = $issueGalley->getIssueId();
                $issueDao = DAORegistry::getDAO('IssueDAO'); 
                $issue = $issueDao->getIssueById($issueId, null, true);
                if (!($issue instanceof Issue)) {
                    $errorMsg = 'Cannot load record: issue galley without issue.';
                    return false;
                }
                $journalId = $issue->getJournalId();
                break;

            case ASSOC_TYPE_ISSUE:
                $articleId = null;
                $issueId = $recordToStore['assoc_id'];
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao->getIssueByPubId('publisher-id', $issueId, null, true);
                if (!$issue) {
                    $issue = $issueDao->getIssueById($issueId, null, true);
                }
                if (!($issue instanceof Issue)) {
                    $errorMsg = 'Cannot load record: invalid issue id.';
                    return false;
                }
                $journalId = $issue->getJournalId();
                break;
            case ASSOC_TYPE_JOURNAL:
                $articleId = $issueId = null;
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journal = $journalDao->getById($recordToStore['assoc_id']);
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
        
        $recordToStore['context_id'] = $journalId;
        $recordToStore['issue_id'] = $issueId;
        $recordToStore['submission_id'] = $articleId;

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
        if (isset($record['file_type']) && $record['file_type']) $recordToStore['file_type'] = (int)$record['file_type'];
        if (isset($record['country_id'])) $recordToStore['country_id'] = (string)$record['country_id'];
        if (isset($record['region'])) $recordToStore['region'] = (string)$record['region'];
        if (isset($record['city'])) $recordToStore['city'] = (string)$record['city'];

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
        $placeholders = implode(', ', array_pad(array(), count($recordToStore), '?'));
        $params = array_values($recordToStore);
        return $this->update("INSERT INTO metrics ($fields) VALUES ($placeholders)", $params);
    }
}

?>