<?php
declare(strict_types=1);

/**
 * @file classes/search/ArticleSearchDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleSearchDAO
 * @ingroup search
 * @see ArticleSearch
 *
 * @brief DAO class for article search index.
 */

import('classes.search.ArticleSearch');
import('classes.article.Article');

class ArticleSearchDAO extends DAO {
    
    /** @var array Cache for article search keyword IDs */
    protected $_articleSearchKeywordIds = [];
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ArticleSearchDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
    
    /**
     * [LUMERA CRITICAL] Method to clear memory cache.
     * Called by Rebuild Indexer to prevent Out Of Memory errors.
     * @return void
     */
    public function clearKeywordCache(): void {
        $this->_articleSearchKeywordIds = [];
    }

    /**
     * Add a word to the keyword list (if it doesn't already exist).
     * @param string $keyword
     * @return int|null The keyword ID
     */
    public function _insertKeyword(string $keyword): ?int {
        if (isset($this->_articleSearchKeywordIds[$keyword])) {
            return (int) $this->_articleSearchKeywordIds[$keyword];
        }
        
        $keywordId = null;
        $result = $this->retrieve(
            'SELECT keyword_id FROM article_search_keyword_list WHERE keyword_text = ?',
            [$keyword]
        );
        
        // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $keywordId = (int) $row['keyword_id'];
            $result->Close();
        } else {
            if ($result) {
                $result->Close();
            }
            
            if ($this->update(
                'INSERT INTO article_search_keyword_list (keyword_text) VALUES (?)',
                [$keyword],
                true,
                false
            )) {
                $keywordId = (int) $this->getInsertId('article_search_keyword_list', 'keyword_id');
            } else {
                // Retry logic for race conditions
                $retryResult = $this->retrieve('SELECT keyword_id FROM article_search_keyword_list WHERE keyword_text = ?', [$keyword]);
                if ($retryResult && !$retryResult->EOF) {
                    $retryRow = $retryResult->GetRowAssoc(false);
                    $keywordId = (int) $retryRow['keyword_id'];
                }
                if ($retryResult) {
                    $retryResult->Close();
                }
            }
        }

        // Cache the result
        if ($keywordId !== null) {
            // Limit cache size to prevent memory leaks during massive imports
            if (count($this->_articleSearchKeywordIds) > 5000) {
                $this->_articleSearchKeywordIds = []; // Auto-flush if too big
            }
            $this->_articleSearchKeywordIds[$keyword] = $keywordId;
        }

        return $keywordId;
    }

    /**
     * Retrieve the top results for phrases with the given limit.
     * @param mixed $journal
     * @param array $phrase
     * @param string|null $publishedFrom
     * @param string|null $publishedTo
     * @param int|null $type
     * @param int $limit
     * @param int $cacheHours
     * @return DBRowIterator
     */
    public function getPhraseResults($journal, array $phrase, ?string $publishedFrom = null, ?string $publishedTo = null, $type = null, int $limit = 500, int $cacheHours = 24) {
        import('lib.pkp.classes.db.DBRowIterator');
        
        if (empty($phrase)) {
            return new DBRowIterator(false);
        }

        // Sanitize Type
        $type = ($type === '' || $type === false) ? null : (int) $type;
        
        // 1. OPTIMASI DETEKSI FULLTEXT ---
        $hasWildcard = false;
        foreach ($phrase as $term) {
            if (strpos((string) $term, '%') !== false) {
                $hasWildcard = true;
                break;
            }
        }
        
        $useFulltext = !$hasWildcard; 

        $sqlSelect = 'o.article_id, COUNT(o.article_id) AS count';
        $sqlFrom   = '';
        $sqlWhere  = '';
        $params    = [];

        if ($useFulltext) {
            // SKENARIO CEPAT (FULLTEXT) ---
            $searchString = '';
            foreach ($phrase as $term) {
                $searchString .= '+' . $term . ' ';
            }
            $searchString = trim($searchString);

            $sqlFrom = ' JOIN article_search_objects o ON pa.article_id = o.article_id 
                         JOIN article_search_object_keywords ok ON o.object_id = ok.object_id 
                         JOIN article_search_keyword_list k ON ok.keyword_id = k.keyword_id ';
            
            $sqlWhere = ' AND MATCH(k.keyword_text) AGAINST (? IN BOOLEAN MODE)';
            $params[] = $searchString;

        } else {
            // SKENARIO LAMBAT (LEGACY / WILDCARD) ---
            $legacyJoins = '';
            $phraseCount = count($phrase);
            for ($i = 0; $i < $phraseCount; $i++) {
                if ($i > 0) {
                    $legacyJoins .= ' AND ';
                }
                
                $aliasO = 'o' . $i;
                $aliasK = 'k' . $i;
                
                $sqlFrom .= ", article_search_object_keywords $aliasO, article_search_keyword_list $aliasK";
                $sqlWhere .= " AND o.object_id = $aliasO.object_id AND $aliasO.keyword_id = $aliasK.keyword_id";

                if (strpos((string) $phrase[$i], '%') === false) {
                    $sqlWhere .= " AND $aliasK.keyword_text = ?";
                } else {
                    $sqlWhere .= " AND $aliasK.keyword_text LIKE ?";
                }
                $params[] = $phrase[$i];
                
                if ($i > 0) {
                    $sqlWhere .= " AND o0.pos + $i = $aliasO.pos";
                }
            }
            
            $sqlFrom = ' JOIN article_search_objects o ON pa.article_id = o.article_id ' . $sqlFrom;
        }

        // Filter Tambahan
        if ($type !== null && $type !== 0) {
            $sqlWhere .= ' AND (o.type & ?) != 0';
            $params[] = $type;
        }
        if ($publishedFrom !== null && $publishedFrom !== '') {
            $sqlWhere .= ' AND pa.date_published >= ' . $this->datetimeToDB($publishedFrom);
        }
        if ($publishedTo !== null && $publishedTo !== '') {
            $sqlWhere .= ' AND pa.date_published <= ' . $this->datetimeToDB($publishedTo);
        }
        if ($journal !== null) {
            $sqlWhere .= ' AND a.journal_id = ?';
            $params[] = (int) $journal->getId();
        } else {
            $sqlWhere .= ' AND j.enabled = 1';
        }

        // 2. PERBAIKAN STRUKTUR JOIN UTAMA ---
        $mainQuery = 'SELECT ' . $sqlSelect . '
                      FROM published_articles pa
                      JOIN articles a ON pa.article_id = a.article_id
                      JOIN issues i ON pa.issue_id = i.issue_id
                      JOIN journals j ON a.journal_id = j.journal_id
                      ' . $sqlFrom . '
                      WHERE a.status = ' . STATUS_PUBLISHED . ' 
                        AND i.published = 1 
                        ' . $sqlWhere . '
                      GROUP BY o.article_id
                      ORDER BY count DESC
                      LIMIT ' . (int) $limit;

        $result = $this->retrieveCached($mainQuery, $params, 3600 * $cacheHours);

        return new DBRowIterator($result);
    }

    /**
     * Delete all keywords for an article object.
     * @param int $articleId
     * @param int|null $type (optional)
     * @param int|null $assocId (optional)
     * @return void
     */
    public function deleteArticleKeywords(int $articleId, ?int $type = null, ?int $assocId = null): void {
        $sql = 'SELECT object_id FROM article_search_objects WHERE article_id = ?';
        $params = [(int) $articleId];

        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = (int) $type;
        }

        if ($assocId !== null) {
            $sql .= ' AND assoc_id = ?';
            $params[] = (int) $assocId;
        }

        $result = $this->retrieve($sql, $params);
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $objectId = (int) $row['object_id'];
                $this->update('DELETE FROM article_search_object_keywords WHERE object_id = ?', [$objectId]);
                $this->update('DELETE FROM article_search_objects WHERE object_id = ?', [$objectId]);
                $result->MoveNext();
            }
            $result->Close();
        }
    }

    /**
     * Add an article object to the index (if already exists, indexed keywords are cleared).
     * @param int $articleId
     * @param int $type
     * @param int $assocId
     * @return int The object ID
     */
    public function insertObject(int $articleId, int $type, int $assocId): int {
        $objectId = null;
        
        $result = $this->retrieve(
            'SELECT object_id FROM article_search_objects WHERE article_id = ? AND type = ? AND assoc_id = ?',
            [(int) $articleId, (int) $type, (int) $assocId]
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $objectId = (int) $row['object_id'];
            $result->Close();

            $this->update(
                'DELETE FROM article_search_object_keywords WHERE object_id = ?',
                [$objectId]
            );
        } else {
            if ($result) {
                $result->Close();
            }
            $this->update(
                'INSERT INTO article_search_objects (article_id, type, assoc_id) VALUES (?, ?, ?)',
                [(int) $articleId, (int) $type, (int) $assocId]
            );
            $objectId = (int) $this->getInsertId('article_search_objects', 'object_id');
        }

        return $objectId;
    }

    /**
     * Index an occurrence of a keyword in an object.
     * @param int $objectId
     * @param string $keyword
     * @param int $position
     * @return int|null The keyword ID
     */
    public function insertObjectKeyword(int $objectId, string $keyword, int $position): ?int {
        $keywordId = $this->_insertKeyword($keyword);
        if ($keywordId === null) {
            return null;
        }
        
        $this->update(
            'INSERT INTO article_search_object_keywords (object_id, keyword_id, pos) VALUES (?, ?, ?)',
            [(int) $objectId, (int) $keywordId, (int) $position]
        );
        return $keywordId;
    }

    /**
     * Clear the search index.
     * @return void
     * @deprecated Use ArticleSearchIndexManager instead.
     */
    public function clearIndex(): void {
        $this->update('DELETE FROM article_search_object_keywords');
        $this->update('DELETE FROM article_search_objects');
        $this->update('DELETE FROM article_search_keyword_list');

        $dataSource = $this->getDataSource();
        if (method_exists($dataSource, 'CacheFlush')) {
            $dataSource->CacheFlush();
        }
    }
    
    /**
     * [LUMERA] Check if an article is already indexed.
     * Important to prevent new articles from being overwritten/deleted.
     * @param int $articleId
     * @return array
     */
    public function getSearchObjectsByArticleId(int $articleId): array {
        $result = $this->retrieve(
            'SELECT object_id FROM article_search_objects WHERE article_id = ?',
            [(int) $articleId]
        );

        $objectIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $objectIds[] = (int) $row['object_id'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $objectIds;
    }
    
}
?>