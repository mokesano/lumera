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
 * [WIZDAM EDITION] FULLTEXT Optimized & PHP 8+ Compatible.
 */

import('classes.search.ArticleSearch');
import('classes.article.Article'); // Import STATUS_PUBLISHED for type safety

class ArticleSearchDAO extends DAO {
    // [WIZDAM] Simpan cache di properti class, bukan static variable lokal
    protected array $_articleSearchKeywordIds = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleSearchDAO(): void {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleSearchDAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }
    
    /**
     * [WIZDAM CRITICAL] Metode untuk mengosongkan cache memori
     * Dipanggil oleh Rebuild Indexer untuk mencegah Out Of Memory.
     */
    public function clearKeywordCache(): void {
        $this->_articleSearchKeywordIds = [];
    }

    /**
     * Add a word to the keyword list (if it doesn't already exist).
     * @param string $keyword
     * @return int|null the keyword ID
     */
    public function _insertKeyword(string $keyword): ?int {
        // [WIZDAM] Gunakan properti class
        if (isset($this->_articleSearchKeywordIds[$keyword])) {
            return $this->_articleSearchKeywordIds[$keyword];
        }
        
        $keywordId = null;
        
        $result = $this->retrieve(
            'SELECT keyword_id FROM article_search_keyword_list WHERE keyword_text = ?',
            [$keyword]
        );
        
        if($result->RecordCount() == 0) {
            $result->Close();
            if ($this->update(
                'INSERT INTO article_search_keyword_list (keyword_text) VALUES (?)',
                [$keyword],
                true,
                false
            )) {
                $keywordId = (int) $this->getInsertId('article_search_keyword_list', 'keyword_id');
            } else {
                // Retry logic for race conditions
                $result = $this->retrieve('SELECT keyword_id FROM article_search_keyword_list WHERE keyword_text = ?', [$keyword]);
                if($result->RecordCount() > 0) {
                    $keywordId = (int) $result->fields[0];
                    $result->Close();
                }
            }
        } else {
            $keywordId = (int) $result->fields[0];
            $result->Close();
        }

        // Cache the result
        if ($keywordId !== null) {
            // [WIZDAM] Limit cache size to prevent memory leaks during massive imports
            if (count($this->_articleSearchKeywordIds) > 5000) {
                $this->_articleSearchKeywordIds = []; // Auto-flush if too big
            }
            $this->_articleSearchKeywordIds[$keyword] = $keywordId;
        }

        return $keywordId;
    }

    /**
     * Retrieve the top results for a phrases with the given limit.
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

        // Sanitasi Type
        $type = ($type === '' || $type === false) ? null : (int) $type;
        
        // --- 1. OPTIMASI DETEKSI FULLTEXT ---
        // Hapus batasan 'count($phrase) == 1'. 
        // Selama tidak ada wildcard '%', kita PAKSA pakai Fulltext karena jauh lebih cepat.
        $hasWildcard = false;
        foreach ($phrase as $term) {
            if (strstr($term, '%') !== false) {
                $hasWildcard = true;
                break;
            }
        }
        
        // Gunakan Fulltext jika tidak ada wildcard
        $useFulltext = !$hasWildcard; 

        $sqlSelect = 'o.article_id, COUNT(o.article_id) AS count';
        $sqlFrom   = '';
        $sqlWhere  = '';
        $params    = [];

        if ($useFulltext) {
            // --- SKENARIO CEPAT (FULLTEXT) ---
            
            // Gabungkan semua kata menjadi satu string untuk BOOLEAN MODE
            // Contoh: array('sistem', 'informasi') -> "+sistem +informasi"
            $searchString = '';
            foreach ($phrase as $term) {
                // Tambahkan operator + agar kata tersebut WAJIB ada (AND logic)
                $searchString .= '+' . $term . ' ';
            }
            $searchString = trim($searchString);

            // Join Table Index
            // Kita mulai dari article_search_objects sebagai anchor
            $sqlFrom = ' JOIN article_search_objects o ON pa.article_id = o.article_id 
                         JOIN article_search_object_keywords ok ON o.object_id = ok.object_id 
                         JOIN article_search_keyword_list k ON ok.keyword_id = k.keyword_id ';
            
            // Syntax Match Against
            $sqlWhere = ' AND MATCH(k.keyword_text) AGAINST (? IN BOOLEAN MODE)';
            $params[] = $searchString;

            // OPTIONAL: Jika ingin mengurutkan berdasarkan skor relevansi MySQL (lebih akurat)
            // $sqlSelect = 'o.article_id, SUM(MATCH(k.keyword_text) AGAINST (? IN BOOLEAN MODE)) as relevance';
            // $params[] = $searchString; // Perlu double param jika masuk select & where

        } else {
            // --- SKENARIO LAMBAT (LEGACY / WILDCARD) ---
            // Fallback hanya jika user mencari dengan '%', misal 'komput%'
            
            $legacyJoins = '';
            for ($i = 0, $count = count($phrase); $i < $count; $i++) {
                if ($i > 0) $legacyJoins .= ' AND ';
                
                // Gunakan alias unik per kata
                $aliasO = 'o'.$i;
                $aliasK = 'k'.$i;
                
                // Bangun struktur JOIN manual agar lebih aman daripada NATURAL JOIN
                // Note: Kita melakukan cross-join implicit di WHERE clause untuk performa legacy
                $sqlFrom .= ", article_search_object_keywords $aliasO, article_search_keyword_list $aliasK";
                
                $sqlWhere .= " AND o.object_id = $aliasO.object_id AND $aliasO.keyword_id = $aliasK.keyword_id";

                if (strstr($phrase[$i], '%') === false) {
                    $sqlWhere .= " AND $aliasK.keyword_text = ?";
                } else {
                    $sqlWhere .= " AND $aliasK.keyword_text LIKE ?";
                }
                $params[] = $phrase[$i];
                
                // Proximity check (urutan kata)
                if ($i > 0) {
                    $sqlWhere .= " AND o0.pos + $i = $aliasO.pos";
                }
            }
            
            // Perlu inisialisasi tabel objek utama 'o' di legacy mode agar 'o.object_id' dikenali
            // Kita inject di klausa FROM utama nanti
            $sqlFrom = ' JOIN article_search_objects o ON pa.article_id = o.article_id ' . $sqlFrom;
        }

        // Filter Tambahan
        if (!empty($type)) {
            $sqlWhere .= ' AND (o.type & ?) != 0';
            $params[] = $type;
        }
        if (!empty($publishedFrom)) {
            $sqlWhere .= ' AND pa.date_published >= ' . $this->datetimeToDB($publishedFrom);
        }
        if (!empty($publishedTo)) {
            $sqlWhere .= ' AND pa.date_published <= ' . $this->datetimeToDB($publishedTo);
        }
        if (!empty($journal)) {
            $sqlWhere .= ' AND a.journal_id = ?';
            $params[] = $journal->getId();
        } else {
            $sqlWhere .= ' AND j.enabled = 1';
        }

        // --- 2. PERBAIKAN STRUKTUR JOIN UTAMA ---
        // Ubah dari "Comma Separated" (articles a, issues i) menjadi EXPLICIT JOIN
        // Ini memastikan query optimizer bekerja optimal saat digabung dengan Fulltext
        
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
                      LIMIT ' . $limit;

        $result = $this->retrieveCached($mainQuery, $params, 3600 * $cacheHours);

        return new DBRowIterator($result);
    }

    /**
     * Delete all keywords for an article object.
     * @param int $articleId
     * @param int|null $type optional
     * @param int|null $assocId optional
     */
    public function deleteArticleKeywords(int $articleId, ?int $type = null, ?int $assocId = null): void {
        $sql = 'SELECT object_id FROM article_search_objects WHERE article_id = ?';
        $params = [(int) $articleId];

        if (isset($type)) {
            $sql .= ' AND type = ?';
            $params[] = (int) $type;
        }

        if (isset($assocId)) {
            $sql .= ' AND assoc_id = ?';
            $params[] = (int) $assocId;
        }

        $result = $this->retrieve($sql, $params);
        while (!$result->EOF) {
            $objectId = (int) $result->fields[0];
            $this->update('DELETE FROM article_search_object_keywords WHERE object_id = ?', [(int)$objectId]);
            $this->update('DELETE FROM article_search_objects WHERE object_id = ?', [(int)$objectId]);
            $result->MoveNext();
        }
        $result->Close();
    }

    /**
     * Add an article object to the index (if already exists, indexed keywords are cleared).
     * @param int $articleId
     * @param int $type
     * @param int $assocId
     * @return int the object ID
     */
    public function insertObject(int $articleId, int $type, int $assocId): int {
        $objectId = null;
        
        $result = $this->retrieve(
            'SELECT object_id FROM article_search_objects WHERE article_id = ? AND type = ? AND assoc_id = ?',
            [(int) $articleId, (int) $type, (int) $assocId]
        );
        
        if ($result->RecordCount() == 0) {
            $this->update(
                'INSERT INTO article_search_objects (article_id, type, assoc_id) VALUES (?, ?, ?)',
                [(int) $articleId, (int) $type, (int) $assocId]
            );
            $objectId = (int) $this->getInsertId('article_search_objects', 'object_id');

        } else {
            $objectId = (int) $result->fields[0];
            $this->update(
                'DELETE FROM article_search_object_keywords WHERE object_id = ?',
                [(int)$objectId]
            );
        }
        $result->Close();

        return $objectId;
    }

    /**
     * Index an occurrence of a keyword in an object.
     * @param int $objectId
     * @param string $keyword
     * @param int $position
     * @return int|null $keywordId
     */
    public function insertObjectKeyword(int $objectId, string $keyword, int $position): ?int {
        $keywordId = $this->_insertKeyword($keyword);
        if ($keywordId === null) return null; // Bug #2324
        
        $this->update(
            'INSERT INTO article_search_object_keywords (object_id, keyword_id, pos) VALUES (?, ?, ?)',
            [(int)$objectId, (int)$keywordId, (int)$position]
        );
        return $keywordId;
    }

    /**
     * Clear the search index.
     * @return void
     * @deprecated in OJS 3.4.0. Use ArticleSearchIndexManager instead.
     */
    public function clearIndex(): void {
        $this->update('DELETE FROM article_search_object_keywords');
        $this->update('DELETE FROM article_search_objects');
        $this->update('DELETE FROM article_search_keyword_list');
        
        // Clear cached queries
        $this->setCacheDir(Config::getVar('files', 'files_dir') . '/_db');
        $dataSource = $this->getDataSource();
        $dataSource->CacheFlush();
    }
    
    /**
     * [WIZDAM] Cek apakah artikel sudah terindeks.
     * Penting agar artikel baru tidak tertimpa/terhapus.
     * @param int $articleId
     * @return array
     */
    public function getSearchObjectsByArticleId(int $articleId): array {
        $result = $this->retrieve(
            'SELECT object_id FROM article_search_objects WHERE article_id = ?',
            [(int) $articleId]
        );

        $objectIds = [];
        while (!$result->EOF) {
            $objectIds[] = (int) $result->fields[0];
            $result->MoveNext();
        }
        $result->Close();
        return $objectIds;
    }
}
?>