<?php
declare(strict_types=1);

/**
 * @file classes/article/PublishedArticleDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedArticleDAO
 * @ingroup article
 * @see PublishedArticle
 *
 * @brief Operations for retrieving and modifying PublishedArticle objects.
 */

import('classes.article.PublishedArticle');

class PublishedArticleDAO extends DAO {

    /** @var ArticleDAO */
    public $articleDao;

    /** @var AuthorDAO */
    public $authorDao;

    /** @var ArticleGalleyDAO */
    public $galleyDao;
    
    /** @var SuppFileDAO */
    public $suppFileDao;

    /** @var mixed $articleCache */
    public $articleCache;
    
    /** @var mixed $articlesInSectionsCache */
    public $articlesInSectionsCache;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->authorDao = DAORegistry::getDAO('AuthorDAO');
        $this->galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $this->suppFileDao = DAORegistry::getDAO('SuppFileDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PublishedArticleDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Caching
    //

    /**
     * Article cache miss handler.
     * @param object $cache
     * @param int $id
     * @return PublishedArticle
     */
    public function _articleCacheMiss($cache, $id) {
        $publishedArticle = $this->getPublishedArticleByBestArticleId(null, $id, null);
        $cache->setCache($id, $publishedArticle);
        return $publishedArticle;
    }

    /**
     * Get published article cache.
     * @return object
     */
    public function _getPublishedArticleCache() {
        if (!isset($this->articleCache)) {
            $cacheManager = CacheManager::getManager();
            $this->articleCache = $cacheManager->getObjectCache('publishedArticles', 0, [$this, '_articleCacheMiss']);
        }
        return $this->articleCache;
    }

    /**
     * Articles in sections cache miss handler.
     * @param object $cache
     * @param int $id
     * @return array
     */
    public function _articlesInSectionsCacheMiss($cache, $id) {
        $articlesInSections = $this->getPublishedArticlesInSections($id, null);
        $cache->setCache($id, $articlesInSections);
        return $articlesInSections;
    }

    /**
     * Get articles in sections cache.
     * @return object
     */
    public function _getArticlesInSectionsCache() {
        if (!isset($this->articlesInSectionsCache)) {
            $cacheManager = CacheManager::getManager();
            $this->articlesInSectionsCache = $cacheManager->getObjectCache('articlesInSections', 0, [$this, '_articlesInSectionsCacheMiss']);
        }
        return $this->articlesInSectionsCache;
    }

    /**
     * Retrieve Published Articles by issue id.
     * @param int $issueId
     * @return array PublishedArticle objects array
     */
    public function getPublishedArticles($issueId) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        
        $params = [
            (int) $issueId,
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale,
            (int) $issueId
        ];

        $sql = 'SELECT DISTINCT
                pa.*,
                a.*,
                SUBSTRING(COALESCE(stl.setting_value, stpl.setting_value) FROM 1 FOR 255) AS section_title,
                SUBSTRING(COALESCE(sal.setting_value, sapl.setting_value) FROM 1 FOR 255) AS section_abbrev,
                COALESCE(o.seq, s.seq) AS section_seq,
                pa.seq
            FROM published_articles pa,
                articles a LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN custom_section_orders o ON (a.section_id = o.section_id AND o.issue_id = ?)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE pa.article_id = a.article_id
                AND pa.issue_id = ?
                AND a.status <> ' . STATUS_ARCHIVED . '
            ORDER BY section_seq ASC, pa.seq ASC';

        $result = $this->retrieve($sql, $params);

        $publishedArticles = [];
        while (!$result->EOF) {
            $publishedArticles[] = $this->_returnPublishedArticleFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        return $publishedArticles;
    }

    /**
     * Retrieve a count of published articles in a journal.
     * @param int $journalId
     * @return int
     */
    public function getPublishedArticleCountByJournalId($journalId) {
        $result = $this->retrieve(
            'SELECT count(*) FROM published_articles pa, articles a WHERE pa.article_id = a.article_id AND a.journal_id = ? AND a.status <> ' . STATUS_ARCHIVED,
            [(int) $journalId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $count = isset($fields[0]) ? (int) $fields[0] : 0;
        
        $result->Close();
        return $count;
    }

    /**
     * Retrieve all published articles in a journal.
     * @param int|null $journalId
     * @param DBResultRange|null $rangeInfo
     * @param bool $reverse Whether to reverse the sort order
     * @return DAOResultFactory
     */
    public function getPublishedArticlesByJournalId($journalId = null, $rangeInfo = null, $reverse = false) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        $params = [
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale
        ];
        if ($journalId !== null) $params[] = (int) $journalId;
        
        $result = $this->retrieveRange(
            'SELECT pa.*,
                a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM published_articles pa
                LEFT JOIN articles a ON pa.article_id = a.article_id
                LEFT JOIN issues i ON pa.issue_id = i.issue_id
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE i.published = 1
                ' . ($journalId !== null ? 'AND a.journal_id = ?' : '') . '
                AND a.status <> ' . STATUS_ARCHIVED . '
            ORDER BY date_published ' . ($reverse ? 'DESC' : 'ASC'),
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnPublishedArticleFromRow');
    }

    /**
     * Retrieve Published Articles by issue id
     * @param int $issueId
     * @param bool $useCache optional
     * @return array PublishedArticle objects array
     */
    public function getPublishedArticlesInSections($issueId, $useCache = false) {
        if ($useCache) {
            $cache = $this->_getArticlesInSectionsCache();
            $returner = $cache->get($issueId);
            return $returner;
        }

        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        
        $params = [
            (int) $issueId,
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale,
            (int) $issueId
        ];

        $sql = 'SELECT DISTINCT
                pa.*,
                a.*,
                SUBSTRING(COALESCE(stl.setting_value, stpl.setting_value) FROM 1 FOR 255) AS section_title,
                SUBSTRING(COALESCE(sal.setting_value, sapl.setting_value) FROM 1 FOR 255) AS section_abbrev,
                s.abstracts_not_required AS abstracts_not_required,
                s.hide_title AS section_hide_title,
                s.hide_author AS section_hide_author,
                COALESCE(o.seq, s.seq) AS section_seq,
                pa.seq
            FROM published_articles pa,
                articles a
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN custom_section_orders o ON (a.section_id = o.section_id AND o.issue_id = ?)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE pa.article_id = a.article_id
                AND pa.issue_id = ?
                AND a.status <> ' . STATUS_ARCHIVED . '
            ORDER BY section_seq ASC, pa.seq ASC';

        $result = $this->retrieve($sql, $params);

        $currSectionId = 0;
        $publishedArticles = [];
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $publishedArticle = $this->_returnPublishedArticleFromRow($row);
            if ($publishedArticle->getSectionId() != $currSectionId && !isset($publishedArticles[$publishedArticle->getSectionId()])) {
                $currSectionId = $publishedArticle->getSectionId();
                $publishedArticles[$currSectionId] = [
                    'articles' => [],
                    'title' => '',
                    'abstractsNotRequired' => $row['abstracts_not_required'],
                    'hideTitle' => $row['section_hide_title'],
                    'hideAuthor' => $row['section_hide_author']
                ];

                if (!$row['section_hide_title']) {
                    $publishedArticles[$currSectionId]['title'] = $publishedArticle->getSectionTitle();
                }
            }
            $publishedArticles[$currSectionId]['articles'][] = $publishedArticle;
            $result->MoveNext();
        }

        $result->Close();
        return $publishedArticles;
    }

    /**
     * Retrieve Published Articles by section id
     * @param int $sectionId
     * @param int $issueId
     * @param bool $simple Whether or not to skip fetching dependent objects; default false
     * @return array PublishedArticle objects array
     */
    public function getPublishedArticlesBySectionId($sectionId, $issueId, $simple = false) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        $func = $simple ? '_returnSimplePublishedArticleFromRow' : '_returnPublishedArticleFromRow';
        $publishedArticles = [];

        $result = $this->retrieve(
            'SELECT pa.*,
                a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM published_articles pa,
                articles a,
                sections s
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE a.section_id = s.section_id
                AND pa.article_id = a.article_id
                AND a.section_id = ?
                AND pa.issue_id = ?
                AND a.status <> ' . STATUS_ARCHIVED . '
            ORDER BY pa.seq ASC',
            [
                'title', $primaryLocale, 'title', $locale,
                'abbrev', $primaryLocale, 'abbrev', $locale,
                (int) $sectionId, (int) $issueId
            ]
        );

        while (!$result->EOF) {
            $publishedArticle = $this->$func($result->GetRowAssoc(false));
            $publishedArticles[] = $publishedArticle;
            $result->MoveNext();
        }

        $result->Close();
        return $publishedArticles;
    }

    /**
     * Retrieve Published Article by pub id
     * @param int $publishedArticleId
     * @param bool $simple Whether or not to skip fetching dependent objects; default false
     * @return PublishedArticle|null
     */
    public function getPublishedArticleById($publishedArticleId, $simple = false) {
        $result = $this->retrieve(
            'SELECT * FROM published_articles WHERE published_article_id = ?', 
            [(int) $publishedArticleId]
        );

        $publishedArticle = null;
        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);

            $publishedArticle = new PublishedArticle();
            $publishedArticle->setPublishedArticleId($row['published_article_id']);
            $publishedArticle->setId($row['article_id']);
            $publishedArticle->setIssueId($row['issue_id']);
            $publishedArticle->setDatePublished($this->datetimeFromDB($row['date_published']));
            $publishedArticle->setSeq($row['seq']);
            $publishedArticle->setAccessStatus($row['access_status']);

            if (!$simple && $this->suppFileDao) {
                $publishedArticle->setSuppFiles($this->suppFileDao->getSuppFilesByArticle($row['article_id']));
            }
        }

        $result->Close();
        return $publishedArticle;
    }

    /**
     * Retrieve published article by article id
     * @param int $articleId
     * @param int|null $journalId optional
     * @param bool $useCache optional
     * @return PublishedArticle|null
     */
    public function getPublishedArticleByArticleId($articleId, $journalId = null, $useCache = false) {
        if ($useCache) {
            $cache = $this->_getPublishedArticleCache();
            $returner = $cache->get($articleId);
            if ($returner && $journalId != null && $journalId != $returner->getJournalId()) $returner = null;
            return $returner;
        }

        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        $params = [
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale,
            (int) $articleId
        ];
        if ($journalId) $params[] = (int) $journalId;

        $sql = 'SELECT pa.*,
                a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM published_articles pa,
                articles a
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE pa.article_id = a.article_id
                AND a.status <> ' . STATUS_ARCHIVED . '
                AND a.article_id = ?' .
            ($journalId ? ' AND a.journal_id = ?' : '');

        $result = $this->retrieve($sql, $params);

        $publishedArticle = null;
        
        if ($result && $result->RecordCount() != 0) {
            $publishedArticle = $this->_returnPublishedArticleFromRow($result->GetRowAssoc(false));
        }

        if ($result) {
            $result->Close();
        }
        
        return $publishedArticle;
    }

    /**
     * Retrieve published article by pub id
     * @param string $pubIdType One of the NLM pub-id-type values
     * @param string $pubId
     * @param int|null $journalId
     * @param bool $useCache optional
     * @return PublishedArticle|null
     */
    public function getPublishedArticleByPubId($pubIdType, $pubId, $journalId = null, $useCache = false) {
        if ($useCache && $pubIdType == 'publisher-id') {
            $cache = $this->_getPublishedArticleCache();
            $returner = $cache->get($pubId);
            if ($returner && $journalId != null && $journalId != $returner->getJournalId()) $returner = null;
            return $returner;
        }

        $publishedArticle = null;
        if (!empty($pubId)) {
            $publishedArticles = $this->getBySetting('pub-id::'.$pubIdType, $pubId, $journalId);
            if (!empty($publishedArticles)) {
                $publishedArticle = $publishedArticles[0];
            }
        }
        return $publishedArticle;
    }

    /**
     * Find published articles by querying article settings.
     * @param string $settingName
     * @param mixed $settingValue
     * @param int|null $journalId optional
     * @return array The articles identified by setting.
     */
    public function getBySetting($settingName, $settingValue, $journalId = null) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();

        $params = [
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale,
            $settingName
        ];

        $sql = 'SELECT pa.*,
                a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM published_articles pa
                INNER JOIN articles a ON pa.article_id = a.article_id
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?) ';
        if (is_null($settingValue)) {
            $sql .= 'LEFT JOIN article_settings ast ON a.article_id = ast.article_id AND ast.setting_name = ?
                WHERE (ast.setting_value IS NULL OR ast.setting_value = \'\')';
        } else {
            $params[] = (string) $settingValue;
            $sql .= 'INNER JOIN article_settings ast ON a.article_id = ast.article_id
                WHERE ast.setting_name = ? AND ast.setting_value = ?';
        }
        if ($journalId) {
            $params[] = (int) $journalId;
            $sql .= ' AND a.journal_id = ?';
        }
        $sql .= ' AND a.status <> ' . STATUS_ARCHIVED;
        $sql .= ' ORDER BY pa.issue_id, a.article_id';
        
        $result = $this->retrieve($sql, $params);

        $publishedArticles = [];
        while (!$result->EOF) {
            $publishedArticles[] = $this->_returnPublishedArticleFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }
        $result->Close();

        return $publishedArticles;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param int|null $journalId
     * @param string|int $articleId
     * @param bool $useCache
     * @return PublishedArticle|null
     */
    public function getPublishedArticleByBestArticleId_OLD($journalId, $articleId, $useCache = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function: getPublishedArticleByBestArticleId_OLD called. Please update your plugin code.', E_USER_NOTICE);
        }
        return $this->getPublishedArticleByBestArticleId($journalId, $articleId, $useCache);
    }

    /**
     * Retrieve Published Article by best published article id.
     * Checks both internal ID (numeric) and public ID (publisher-id).
     * @param int|null $journalId
     * @param string|int $articleId
     * @param bool $useCache
     * @return PublishedArticle|null
     */
    public function getPublishedArticleByBestArticleId($journalId, $articleId, $useCache = false) {
        if ($useCache) {
            $cache = $this->_getPublishedArticleCache();
            $returner = $cache->get($articleId);
            if ($returner && $journalId != null && $journalId != $returner->getJournalId()) $returner = null;
            return $returner;
        }

        if (is_numeric($articleId)) {
            return $this->getPublishedArticleByArticleId($articleId, $journalId);
        } else {
            return $this->getPublishedArticleByPubId('publisher-id', $articleId, $journalId);
        }
    }

    /**
     * Retrieve "article_id"s for published articles for a journal, sorted alphabetically.
     * @param int|null $journalId
     * @param bool $useCache Whether to use the query cache
     * @return array
     */
    public function getPublishedArticleIdsAlphabetizedByJournal($journalId = null, $useCache = true) {
        $params = [
            'cleanTitle', AppLocale::getLocale(),
            'cleanTitle'
        ];
        if (isset($journalId)) $params[] = (int) $journalId;

        $articleIds = [];
        $functionName = $useCache ? 'retrieveCached' : 'retrieve';
        
        $result = $this->$functionName(
            'SELECT a.article_id AS pub_id,
                COALESCE(atl.setting_value, atpl.setting_value) AS article_title
            FROM published_articles pa,
                issues i,
                articles a
                JOIN journals j ON (a.journal_id = j.journal_id)
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name = ? AND atl.locale = ?)
                LEFT JOIN article_settings atpl ON (a.article_id = atpl.article_id AND atpl.setting_name = ? AND atpl.locale = a.locale)
            WHERE pa.article_id = a.article_id
                AND a.status <> ' . STATUS_ARCHIVED . '
                AND i.issue_id = pa.issue_id
                AND i.published = 1
                AND s.section_id IS NOT NULL' .
                (isset($journalId) ? ' AND a.journal_id = ?' : ' AND j.enabled = 1') . ' ORDER BY article_title',
            $params
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $articleIds[] = $row['pub_id'];
            $result->moveNext();
        }

        $result->Close();
        return $articleIds;
    }

    /**
     * Retrieve "article_id"s for published articles for a journal, sorted by reverse publish date.
     * @param int|null $journalId
     * @param bool $useCache Whether to use the query cache
     * @return array
     */
    public function getPublishedArticleIdsByJournal($journalId = null, $useCache = true) {
        $articleIds = [];
        $functionName = $useCache ? 'retrieveCached' : 'retrieve';
        
        $params = isset($journalId) ? [(int) $journalId] : [];
        
        $result = $this->$functionName(
            'SELECT a.article_id AS pub_id
            FROM published_articles pa
                JOIN articles a ON a.article_id = pa.article_id
                JOIN sections s ON s.section_id = a.section_id
                JOIN issues i ON pa.issue_id = i.issue_id
            WHERE i.published = 1
                AND a.status <> ' . STATUS_ARCHIVED . '
                ' . (isset($journalId) ? ' AND a.journal_id = ?' : '') . '
            ORDER BY pa.date_published DESC',
            $params
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $articleIds[] = $row['pub_id'];
            $result->moveNext();
        }

        $result->Close();
        return $articleIds;
    }

    /**
     * Retrieve "article_id"s for published articles for a journal section, sorted by reverse publish date.
     * @param int $sectionId
     * @param bool $useCache Whether to use the query cache
     * @return array
     */
    public function getPublishedArticleIdsBySection($sectionId, $useCache = true) {
        $articleIds = [];
        $functionName = $useCache ? 'retrieveCached' : 'retrieve';
        
        $result = $this->$functionName(
            'SELECT a.article_id
            FROM published_articles pa,
                articles a,
                issues i
            WHERE pa.issue_id = i.issue_id
                AND i.published = 1
                AND pa.article_id = a.article_id
                AND a.section_id = ?
                AND a.status <> ' . STATUS_ARCHIVED . '
            ORDER BY pa.date_published DESC',
            [(int) $sectionId]
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $articleIds[] = $row['article_id'];
            $result->MoveNext();
        }

        $result->Close();
        return $articleIds;
    }

    /**
     * Internal function to return a PublishedArticle object from a row.
     * [WIZDAM] OPTIMASI: Tambahkan parameter $loadRelations untuk menghindari N+1 query
     * 
     * @param array $row
     * @param bool $callHooks Whether or not to call hooks
     * @param bool $loadRelations Whether to load galleys and suppFiles (default true)
     * @return PublishedArticle
     */
    public function _returnPublishedArticleFromRow($row, $callHooks = true, $loadRelations = true) {
        $publishedArticle = new PublishedArticle();
        
        $pubId = isset($row['published_article_id']) ? $row['published_article_id'] : (isset($row['pub_id']) ? $row['pub_id'] : 0);
        
        $publishedArticle->setPublishedArticleId((int) $pubId);
        $publishedArticle->setIssueId((int) $row['issue_id']);
        $publishedArticle->setDatePublished($this->datetimeFromDB($row['date_published']));
        $publishedArticle->setSeq($row['seq']);
        $publishedArticle->setAccessStatus((int) $row['access_status']);

        if ($loadRelations) {
            if ($this->galleyDao) {
                $publishedArticle->setGalleys($this->galleyDao->getGalleysByArticle($row['article_id']));
            }
            if ($this->suppFileDao) {
                $publishedArticle->setSuppFiles($this->suppFileDao->getSuppFilesByArticle($row['article_id']));
            }
        }

        // Article attributes
        if ($this->articleDao) {
            $this->articleDao->_articleFromRow($publishedArticle, $row);
        }

        if ($callHooks) HookRegistry::dispatch('PublishedArticleDAO::_returnPublishedArticleFromRow', [&$publishedArticle, &$row]);
        return $publishedArticle;
    }

    /**
     * Insert a new Published Article.
     * @param PublishedArticle $publishedArticle
     * @return int pubId
     */
    public function insertPublishedArticle($publishedArticle) {
        $this->update(
            sprintf('INSERT INTO published_articles
                (article_id, issue_id, date_published, seq, access_status)
                VALUES
                (?, ?, %s, ?, ?)',
                $this->datetimeToDB($publishedArticle->getDatePublished())),
            [
                (int) $publishedArticle->getId(),
                (int) $publishedArticle->getIssueId(),
                $publishedArticle->getSeq(),
                (int) $publishedArticle->getAccessStatus()
            ]
        );

        $publishedArticle->setPublishedArticleId($this->getInsertId('published_articles', 'pub_id'));
        return $publishedArticle->getPublishedArticleId();
    }

    /**
     * Get the ID of the last inserted published article.
     * @return int
     */
    public function getInsertPublishedArticleId() {
        return $this->getInsertId('published_articles', 'published_article_id');
    }

    /**
     * Removes an published Article by id
     * @param int $publishedArticleId
     */
    public function deletePublishedArticleById($publishedArticleId) {
        $this->update(
            'DELETE FROM published_articles WHERE published_article_id = ?', 
            [(int) $publishedArticleId]
        );
        $this->flushCache();
    }

    /**
     * Delete published article by article ID
     * NOTE: This does not delete the related Article or any dependent entities
     * @param int $articleId
     * @return bool
     */
    public function deletePublishedArticleByArticleId($articleId) {
        $result = $this->update(
            'DELETE FROM published_articles WHERE article_id = ?', 
            [(int) $articleId]
        );
        $this->flushCache();
        return $result;
    }

    /**
     * Delete published articles by section ID
     * @param int $sectionId
     */
    public function deletePublishedArticlesBySectionId($sectionId) {
        $result = $this->retrieve(
            'SELECT pa.article_id AS article_id FROM published_articles pa, articles a WHERE pa.article_id = a.article_id AND a.section_id = ?', 
            [(int) $sectionId]
        );

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $this->update(
                'DELETE FROM published_articles WHERE article_id = ?', 
                [(int) $row['article_id']]
            );
            $result->MoveNext();
        }
        $result->Close();
        $this->flushCache();
    }

    /**
     * Delete published articles by issue ID
     * @param int $issueId
     */
    public function deletePublishedArticlesByIssueId($issueId) {
        $this->update(
            'DELETE FROM published_articles WHERE issue_id = ?', 
            [(int) $issueId]
        );
        $this->flushCache();
    }

    /**
     * Update a Published Article.
     * @param PublishedArticle $publishedArticle
     * @return bool
     */
    public function updatePublishedArticle($publishedArticle) {
        $pubId = (int) $publishedArticle->getPublishedArticleId();
        if ($pubId <= 0) {
            return false;
        }

        // Perbaikan WIZDAM: Gunakan sprintf dan %s untuk menghindari double-escaping dari ADODB
        $this->update(
            sprintf(
                'UPDATE published_articles
                    SET article_id = ?,
                        issue_id = ?,
                        date_published = %s,
                        seq = ?,
                        access_status = ?
                    WHERE published_article_id = ?',
                $this->datetimeToDB($publishedArticle->getDatePublished())
            ),
            [
                (int) $publishedArticle->getId(),
                (int) $publishedArticle->getIssueId(),
                $publishedArticle->getSeq(),
                (int) $publishedArticle->getAccessStatus(),
                $pubId
            ]
        );
        
        $this->flushCache();
        return true;
    }

    /**
     * [SECURITY HARDENED] updates a published article field
     * @param int $publishedArticleId
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    public function updatePublishedArticleField($publishedArticleId, $field, $value) {
        $allowedFields = ['seq', 'access_status', 'date_published', 'section_id'];
        
        if (!in_array($field, $allowedFields, true)) {
            return false; 
        }

        if ($field === 'seq' || $field === 'access_status' || $field === 'section_id') {
            $value = (int) $value;
        } elseif ($field === 'date_published') {
            $value = $this->datetimeToDB($value);
        }

        $this->update(
            "UPDATE published_articles SET $field = ? WHERE published_article_id = ?", 
            [$value, (int) $publishedArticleId]
        );

        $this->flushCache();
        return true;
    }

    /**
     * Check if a published article exists.
     * @param int $publishedArticleId
     * @return bool
     */
    public function publishedArticleExists($publishedArticleId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM published_articles WHERE pub_id = ?',
            [(int) $publishedArticleId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = isset($fields[0]) && (int) $fields[0] > 0;
        
        $result->Close();
        return $returner;
    }

    /**
     * Sequentially renumber published articles in their sequence order.
     * @param int $sectionId
     * @param int $issueId
     */
    public function resequencePublishedArticles($sectionId, $issueId) {
        $result = $this->retrieve(
            'SELECT pa.published_article_id FROM published_articles pa, articles a WHERE a.section_id = ? AND a.article_id = pa.article_id AND pa.issue_id = ? ORDER BY pa.seq',
            [(int) $sectionId, (int) $issueId]
        );

        for ($i = 1; !$result->EOF; $i++) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            $publishedArticleId = (int) ($fields[0] ?? 0);
            
            $this->update(
                'UPDATE published_articles SET seq = ? WHERE published_article_id = ?',
                [$i, $publishedArticleId]
            );
            $result->MoveNext();
        }
        $result->Close();
        $this->flushCache();
    }

    /**
     * Retrieve a count of published authors in a journal.
     * @param int $journalId
     * @return int
     */
    public function getPublishedAuthorCountByJournalId($journalId) {
        $result = $this->retrieve(
            'SELECT count(*) FROM authors aa, articles a, published_articles pa WHERE aa.submission_id = a.article_id AND pa.article_id = a.article_id AND a.journal_id = ? AND pa.date_published IS NOT NULL',
            [(int) $journalId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $count = isset($fields[0]) ? (int) $fields[0] : 0;
        
        $result->Close();
        return $count;
    }

    /**
     * Flush the published article caches.
     */
    public function flushCache() {
        $cache = $this->_getPublishedArticleCache();
        $cache->flush();
        $cache = $this->_getArticlesInSectionsCache();
        $cache->flush();
    }

    // --- Custom Functions ---

    /**
     * Increment the view count for a published article.
     * @param int $articleId
     * @return bool
     */
    public function incrementViewsByArticleId($articleId) {
        return $this->update(
            'UPDATE published_articles SET views = views + 1 WHERE article_id = ?',
            [(int) $articleId]
        );
    }

    /**
     * Get the range of years in which articles have been published.
     * @param int|null $journalId
     * @return array
     */
    public function getArticleYearRange($journalId = null) {
        $params = [];
        if ($journalId) $params[] = (int) $journalId;

        $result = $this->retrieve(
            'SELECT MIN(YEAR(pa.date_published)), MAX(YEAR(pa.date_published))
            FROM published_articles pa, articles a
            WHERE pa.article_id = a.article_id
            AND a.status <> ' . STATUS_ARCHIVED .
            ($journalId ? ' AND a.journal_id = ?' : ''),
            $params
        );

        $returner = [];
        if ($result) {
            if (!$result->EOF) {
                /** @var array|bool $fields */
                $fields = $result->fields;
                if (is_array($fields) && isset($fields[0]) && isset($fields[1])) {
                    $returner = [(int) $fields[0], (int) $fields[1]];
                }
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * [LUMERA FORK] Mendapatkan artikel navigasi (sebelumnya/berikutnya)
     * SECARA GLOBAL, berdasarkan logika multi-langkah.
     * @param int $currentArticleId
     * @param int $journalId
     * @return array ['prev' => PublishedArticle|null, 'next' => PublishedArticle|null]
     */
    public function getGlobalArticleNavigation($currentArticleId, $journalId) {
        $prevArticle = null;
        $nextArticle = null;
        
        $currentIssueId = $this->_getIssueIdFromArticle($currentArticleId);
        if (!$currentIssueId) return ['prev' => null, 'next' => null];

        $articlesInIssue = $this->_getArticlesInIssue($currentIssueId, $journalId);

        $currentIndex = -1;
        foreach ($articlesInIssue as $index => $articleId) {
            if ($articleId == $currentArticleId) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === -1) return ['prev' => null, 'next' => null];

        $prevArticleId = null;
        $nextArticleId = null;

        if ($currentIndex > 0) {
            $prevArticleId = $articlesInIssue[$currentIndex - 1];
        } else {
            $prevArticleId = $this->_getPreviousIssueLastArticle($currentIssueId, $journalId);
        }

        if ($currentIndex < (count($articlesInIssue) - 1)) {
            $nextArticleId = $articlesInIssue[$currentIndex + 1];
        } else {
            $nextArticleId = $this->_getNextIssueFirstArticle($currentIssueId, $journalId);
        }

        if ($prevArticleId) $prevArticle = $this->getPublishedArticleByArticleId($prevArticleId);
        if ($nextArticleId) $nextArticle = $this->getPublishedArticleByArticleId($nextArticleId);

        return ['prev' => $prevArticle, 'next' => $nextArticle];
    }

    //
    // Helper
    //

    /**
     * Mendapatkan ID Edisi dari ID Artikel
     * @param int $articleId
     * @return int|null ID Edisi atau null jika tidak ditemukan
     */
    public function _getIssueIdFromArticle($articleId) {
        $result = $this->retrieve(
            'SELECT pa.issue_id FROM published_articles pa WHERE pa.article_id = ?',
            [(int) $articleId]
        );
        $issueId = null;
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            if (is_array($fields) && isset($fields['issue_id'])) {
                $issueId = (int) $fields['issue_id'];
            }
        }
        $result->Close();
        return $issueId;
    }

    /**
     * Mendapatkan semua artikel di 1 edisi
     * @param int $issueId
     * @param int $journalId
     * @return array Daftar ID artikel dalam edisi tersebut, diurutkan berdasarkan 'seq'
     */
    public function _getArticlesInIssue($issueId, $journalId) {
        $articles = [];
        $result = $this->retrieve(
            'SELECT pa.article_id 
             FROM published_articles pa 
             JOIN issues i ON (pa.issue_id = i.issue_id) 
             WHERE i.issue_id = ? AND i.journal_id = ?
             ORDER BY pa.seq ASC',
            [(int) $issueId, (int) $journalId]
        );
        while ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $articles[] = (int) $row['article_id'];
            $result->MoveNext();
        }
        $result->Close();
        return $articles;
    }

    /**
     * Mendapatkan artikel terakhir dari edisi SEBELUMNYA
     * @param int $currentIssueId
     * @param int $journalId
     * @return int|null ID artikel atau null jika tidak ditemukan
     */
    public function _getPreviousIssueLastArticle($currentIssueId, $journalId) {
        $result = $this->retrieve(
            'SELECT pa.article_id 
             FROM published_articles pa 
             JOIN issues i ON (pa.issue_id = i.issue_id) 
             WHERE i.journal_id = ? AND i.issue_id < ?
             ORDER BY i.issue_id DESC, pa.seq DESC 
             LIMIT 1',
            [(int) $journalId, (int) $currentIssueId]
        );
        $articleId = null;
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            if (is_array($fields) && isset($fields['article_id'])) {
                $articleId = (int) $fields['article_id'];
            }
        }
        $result->Close();
        return $articleId;
    }

    /**
     * Mendapatkan artikel pertama dari edisi BERIKUTNYA
     * @param int $currentIssueId
     * @param int $journalId
     * @return int|null ID artikel atau null jika tidak ditemukan
     */
    public function _getNextIssueFirstArticle($currentIssueId, $journalId) {
        $result = $this->retrieve(
            'SELECT pa.article_id 
             FROM published_articles pa 
             JOIN issues i ON (pa.issue_id = i.issue_id) 
             WHERE i.journal_id = ? AND i.issue_id > ?
             ORDER BY i.issue_id ASC, pa.seq ASC 
             LIMIT 1',
            [(int) $journalId, (int) $currentIssueId]
        );
        $articleId = null;
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            if (is_array($fields) && isset($fields['article_id'])) {
                $articleId = (int) $fields['article_id'];
            }
        }
        $result->Close();
        return $articleId;
    }
    
}
?>