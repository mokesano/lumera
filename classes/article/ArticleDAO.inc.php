<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleDAO
 * @ingroup article
 * @see Article
 *
 * @brief Operations for retrieving and modifying Article objects.
 */

import('classes.article.Article');

class ArticleDAO extends DAO {

    /** @var AuthorDAO */
    public $authorDao;
    
    /** @var ObjectCache */
    public $cache;

    /**
     * Internal function to return an Article object from a cache miss.
     * @param ObjectCache $cache
     * @param int $id
     * @return Article
     */
    public function _cacheMiss($cache, $id) {
        $article = $this->getArticle($id, null, false);
        $cache->setCache($id, $article);
        return $article;
    }

    /**
     * Get the cache for this DAO.
     * @return ObjectCache
     */
    public function _getCache() {
        if (!isset($this->cache)) {
            $cacheManager = CacheManager::getManager();
            $this->cache = $cacheManager->getObjectCache('articles', 0, [$this, '_cacheMiss']);
        }
        return $this->cache;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->authorDao = DAORegistry::getDAO('AuthorDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleDAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get a list of field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), [
            'title', 'cleanTitle', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
            'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor',
            'copyrightHolder'
        ]);
    }

    /**
     * Get a list of additional fields that do not have dedicated accessors.
     * @return array
     */
    public function getAdditionalFieldNames() {
        $additionalFields = parent::getAdditionalFieldNames();
        $additionalFields[] = 'pub-id::publisher-id';
        $additionalFields[] = 'copyrightYear';
        $additionalFields[] = 'licenseURL';
        
        // [WIZDAM FEATURES] Add custom fields to settings
        $additionalFields[] = 'articleType';
        $additionalFields[] = 'pubScope';
        $additionalFields[] = 'eLocator';
        $additionalFields[] = 'pii';
        
        return $additionalFields;
    }

    /**
     * Update the settings for this object
     * @param Article $article
     */
    public function updateLocaleFields($article) {
        $this->updateDataObjectSettings('article_settings', $article, [
            'article_id' => $article->getId()
        ]);
    }

    /**
     * Retrieve an article by ID.
     * @param int $articleId
     * @param int|null $journalId optional
     * @param bool $useCache optional
     * @return Article|null
     */
    public function getArticle($articleId, $journalId = null, $useCache = false) {
        if ($useCache) {
            $cache = $this->_getCache();
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
        $sql = 'SELECT a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM articles a
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE article_id = ?';
        if ($journalId !== null) {
            $sql .= ' AND a.journal_id = ?';
            $params[] = (int) $journalId;
        }

        $result = $this->retrieve($sql, $params);

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnArticleFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        return $returner;
    }

    /**
     * Find articles by querying article settings.
     * @param string $settingName
     * @param mixed $settingValue
     * @param int|null $journalId optional
     * @param DBResultRange|null $rangeInfo optional
     * @return DAOResultFactory The articles identified by setting.
     */
    public function getBySetting($settingName, $settingValue, $journalId = null, $rangeInfo = null) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();

        $params = [
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale,
            $settingName
        ];

        $sql = 'SELECT a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM articles a
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?) ';
        if (is_null($settingValue)) {
            $sql .= 'LEFT JOIN article_settings ast ON a.article_id = ast.article_id AND ast.setting_name = ?
                WHERE (ast.setting_value IS NULL OR ast.setting_value = \'\')';
        } else {
            $params[] = $settingValue;
            $sql .= 'INNER JOIN article_settings ast ON a.article_id = ast.article_id
                WHERE ast.setting_name = ? AND ast.setting_value = ?';
        }
        if ($journalId) {
            $params[] = (int) $journalId;
            $sql .= ' AND a.journal_id = ?';
        }
        $sql .= ' ORDER BY a.journal_id, a.article_id';
        $result = $this->retrieveRange($sql, $params, $rangeInfo);

        return new DAOResultFactory($result, $this, '_returnArticleFromRow');
    }

    /**
     * Internal function to return an Article object from a row.
     * @param array $row
     * @return Article
     */
    public function _returnArticleFromRow($row) {
        $article = new Article();
        $this->_articleFromRow($article, $row);
        return $article;
    }

    /**
     * Internal function to fill in the passed article object from the row.
     * @param Article $article
     * @param array $row
     */
    public function _articleFromRow($article, $row) {
        $article->setId($row['article_id']);
        $article->setLocale($row['locale']);
        $article->setUserId($row['user_id']);
        $article->setJournalId($row['journal_id']);
        $article->setSectionId($row['section_id']);
        $article->setSectionTitle($row['section_title']);
        $article->setSectionAbbrev($row['section_abbrev']);
        $article->setLanguage($row['language']);
        $article->setCommentsToEditor($row['comments_to_ed']);
        $article->setCitations($row['citations']);
        $article->setDateSubmitted($this->datetimeFromDB($row['date_submitted']));
        $article->setDateStatusModified($this->datetimeFromDB($row['date_status_modified']));
        $article->setLastModified($this->datetimeFromDB($row['last_modified']));
        $article->setStatus($row['status']);
        $article->setSubmissionProgress($row['submission_progress']);
        $article->setCurrentRound($row['current_round']);
        $article->setSubmissionFileId($row['submission_file_id']);
        $article->setRevisedFileId($row['revised_file_id']);
        $article->setReviewFileId($row['review_file_id']);
        $article->setEditorFileId($row['editor_file_id']);
        $article->setPages($row['pages']);
        $article->setFastTracked($row['fast_tracked']);
        $article->setHideAuthor($row['hide_author']);
        $article->setCommentsStatus($row['comments_status']);

        $this->getDataObjectSettings('article_settings', 'article_id', $row['article_id'], $article);

        HookRegistry::dispatch('ArticleDAO::_returnArticleFromRow', [&$article, &$row]);
    }

    /**
     * Insert a new Article.
     * @param Article $article
     * @return int
     */
    public function insertArticle($article) {
        $article->stampModified();
        $this->update(
            sprintf('INSERT INTO articles
                (locale, user_id, journal_id, section_id, language, comments_to_ed, citations, date_submitted, date_status_modified, last_modified, status, submission_progress, current_round, submission_file_id, revised_file_id, review_file_id, editor_file_id, pages, fast_tracked, hide_author, comments_status)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->datetimeToDB($article->getDateSubmitted()), $this->datetimeToDB($article->getDateStatusModified()), $this->datetimeToDB($article->getLastModified())),
            [
                $article->getLocale(),
                (int) $article->getUserId(),
                (int) $article->getJournalId(),
                (int) $article->getSectionId(),
                $article->getLanguage(),
                $article->getCommentsToEditor(),
                $article->getCitations(),
                $article->getStatus() === null ? STATUS_QUEUED : (int) $article->getStatus(),
                $article->getSubmissionProgress() === null ? 1 : (int) $article->getSubmissionProgress(),
                $article->getCurrentRound() === null ? 1 : (int) $article->getCurrentRound(),
                $this->nullOrInt($article->getSubmissionFileId()),
                $this->nullOrInt($article->getRevisedFileId()),
                $this->nullOrInt($article->getReviewFileId()),
                $this->nullOrInt($article->getEditorFileId()),
                $article->getPages(),
                (int) $article->getFastTracked(),
                (int) $article->getHideAuthor(),
                (int) $article->getCommentsStatus()
            ]
        );

        $article->setId($this->getInsertArticleId());
        $this->updateLocaleFields($article);

        return $article->getId();
    }

    /**
     * Update an existing article.
     * @param Article $article
     */
    public function updateArticle($article) {
        if ($article->getStatus() == 3) {
            $identifiers = $this->getArticleIdentifiers((int) $article->getId());
            if (is_array($identifiers)) {
                $article->setData('eLocator', $identifiers['eLocator']);
                $article->setData('pii', $identifiers['pii']);
            }
        }
        
        $article->stampModified();
        $this->update(
            sprintf('UPDATE articles
                SET locale = ?,
                    user_id = ?,
                    section_id = ?,
                    language = ?,
                    comments_to_ed = ?,
                    citations = ?,
                    date_submitted = %s,
                    date_status_modified = %s,
                    last_modified = %s,
                    status = ?,
                    submission_progress = ?,
                    current_round = ?,
                    submission_file_id = ?,
                    revised_file_id = ?,
                    review_file_id = ?,
                    editor_file_id = ?,
                    pages = ?,
                    fast_tracked = ?,
                    hide_author = ?,
                    comments_status = ?
                WHERE article_id = ?',
                $this->datetimeToDB($article->getDateSubmitted()), $this->datetimeToDB($article->getDateStatusModified()), $this->datetimeToDB($article->getLastModified())),
            [
                $article->getLocale(),
                (int) $article->getUserId(),
                (int) $article->getSectionId(),
                $article->getLanguage(),
                $article->getCommentsToEditor(),
                $article->getCitations(),
                (int) $article->getStatus(),
                (int) $article->getSubmissionProgress(),
                (int) $article->getCurrentRound(),
                $this->nullOrInt($article->getSubmissionFileId()),
                $this->nullOrInt($article->getRevisedFileId()),
                $this->nullOrInt($article->getReviewFileId()),
                $this->nullOrInt($article->getEditorFileId()),
                $article->getPages(),
                (int) $article->getFastTracked(),
                (int) $article->getHideAuthor(),
                (int) $article->getCommentsStatus(),
                (int) $article->getId()
            ]
        );

        $this->updateLocaleFields($article);

        // [WIZDAM] Optimasi: Gunakan foreach alih-alih for loop
        if ($this->authorDao) {
            $authors = $article->getAuthors();
            foreach ($authors as $author) {
                if ($author->getId() > 0) {
                    $this->authorDao->updateAuthor($author);
                } else {
                    $this->authorDao->insertAuthor($author);
                }
            }
            $this->authorDao->resequenceAuthors($article->getId());
        }

        $this->flushCache();
    }

    /**
     * Delete an article.
     * @param Article $article
     * @return bool
     */
    public function deleteArticle($article) {
        return $this->deleteArticleById($article->getId());
    }

    /**
     * Delete an article by ID.
     * [WIZDAM] Null-safety ditambahkan pada semua DAO calls
     * @param int $articleId
     * @return bool
     */
    public function deleteArticleById($articleId) {
        $articleId = (int) $articleId;
        
        if ($this->authorDao) {
            $this->authorDao->deleteAuthorsByArticle($articleId);
        }

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        if ($publishedArticleDao) $publishedArticleDao->deletePublishedArticleByArticleId($articleId);

        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');
        if ($commentDao) $commentDao->deleteBySubmissionId($articleId);

        /** @var NoteDAO $noteDao */
        $noteDao = DAORegistry::getDAO('NoteDAO');
        if ($noteDao) $noteDao->deleteByAssoc(ASSOC_TYPE_ARTICLE, $articleId);

        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        if ($sectionEditorSubmissionDao) {
            $sectionEditorSubmissionDao->deleteDecisionsByArticle($articleId);
            $sectionEditorSubmissionDao->deleteReviewRoundsByArticle($articleId);
        }

        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        if ($reviewAssignmentDao) $reviewAssignmentDao->deleteBySubmissionId($articleId);

        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        if ($editAssignmentDao) $editAssignmentDao->deleteEditAssignmentsByArticle($articleId);

        // Delete copyedit, layout, and proofread signoffs
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        if ($signoffDao) {
            $signoffs = [
                $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId),
                $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId),
                $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $articleId),
                $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId),
                $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId),
                $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId),
                $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId)
            ];
            foreach ($signoffs as $signoff) {
                if ($signoff) $signoffDao->deleteObject($signoff);
            }
        }

        /** @var ArticleCommentDAO $articleCommentDao */
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        if ($articleCommentDao) $articleCommentDao->deleteArticleComments($articleId);

        /** @var ArticleGalleyDAO $articleGalleyDao */
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        if ($articleGalleyDao) $articleGalleyDao->deleteGalleysByArticle($articleId);

        /** @var ArticleSearchDAO $articleSearchDao */
        $articleSearchDao = DAORegistry::getDAO('ArticleSearchDAO');
        if ($articleSearchDao) $articleSearchDao->deleteArticleKeywords($articleId);

        /** @var ArticleEventLogDAO $articleEventLogDao */
        $articleEventLogDao = DAORegistry::getDAO('ArticleEventLogDAO');
        if ($articleEventLogDao) $articleEventLogDao->deleteByAssoc(ASSOC_TYPE_ARTICLE, $articleId);

        /** @var ArticleEmailLogDAO $articleEmailLogDao */
        $articleEmailLogDao = DAORegistry::getDAO('ArticleEmailLogDAO');
        if ($articleEmailLogDao) $articleEmailLogDao->deleteByAssoc(ASSOC_TYPE_ARTICLE, $articleId);

        /** @var NotificationDAO $notificationDao */
        $notificationDao = DAORegistry::getDAO('NotificationDAO');
        if ($notificationDao) $notificationDao->deleteByAssoc(ASSOC_TYPE_ARTICLE, $articleId);

        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        if ($suppFileDao) $suppFileDao->deleteSuppFilesByArticle($articleId);

        // Delete article files
        import('classes.file.ArticleFileManager');
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        if ($articleFileDao) {
            $articleFiles = $articleFileDao->getArticleFilesByArticle($articleId);
            $articleFileManager = new ArticleFileManager($articleId);
            foreach ($articleFiles as $articleFile) {
                $articleFileManager->deleteFile($articleFile->getFileId());
            }
            $articleFileDao->deleteArticleFiles($articleId);
        }

        // Delete article citations
        /** @var CitationDAO $citationDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        if ($citationDao) $citationDao->deleteObjectsByAssocId(ASSOC_TYPE_ARTICLE, $articleId);

        $this->update('DELETE FROM article_settings WHERE article_id = ?', [$articleId]);
        $this->update('DELETE FROM articles WHERE article_id = ?', [$articleId]);

        import('classes.search.ArticleSearchIndex');
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->articleDeleted($articleId);
        $articleSearchIndex->articleChangesFinished();

        $this->flushCache();
        return true;
    }

    /**
     * Get all articles for a journal.
     * @param int|null $journalId
     * @return DAOResultFactory
     */
    public function getArticlesByJournalId($journalId = null) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();

        $params = [
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale
        ];
        if ($journalId !== null) $params[] = (int) $journalId;

        $result = $this->retrieve(
            'SELECT a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM articles a
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            ' . ($journalId !== null ? 'WHERE a.journal_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnArticleFromRow');
    }

    /**
     * Delete all articles by journal ID.
     * @param int $journalId
     */
    public function deleteArticlesByJournalId($journalId) {
        $articles = $this->getArticlesByJournalId($journalId);

        while (!$articles->eof()) {
            $article = $articles->next();
            $this->deleteArticleById($article->getId());
        }
    }

    /**
     * Get all articles for a user.
     * @param int $userId
     * @param int|null $journalId optional
     * @return array Articles
     */
    public function getArticlesByUserId($userId, $journalId = null) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        $params = [
            'title', $primaryLocale, 'title', $locale,
            'abbrev', $primaryLocale, 'abbrev', $locale,
            (int) $userId
        ];
        if ($journalId) $params[] = (int) $journalId;
        
        $articles = [];

        $result = $this->retrieve(
            'SELECT a.*,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM articles a
                LEFT JOIN sections s ON s.section_id = a.section_id
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE a.user_id = ?' .
            (isset($journalId) ? ' AND a.journal_id = ?' : ''),
            $params
        );

        while (!$result->EOF) {
            $articles[] = $this->_returnArticleFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        return $articles;
    }

    /**
     * Get the ID of the journal an article is in.
     * @param int $articleId
     * @return int|false
     */
    public function getArticleJournalId($articleId) {
        // [WIZDAM] FIX: Parameter dibungkus array + Type narrowing
        $result = $this->retrieve(
            'SELECT journal_id FROM articles WHERE article_id = ?', 
            [(int) $articleId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) ? (int) $fields[0] : false;

        $result->Close();
        return $returner;
    }

    /**
     * Check if the specified incomplete submission exists.
     * @param int $articleId
     * @param int $userId
     * @param int $journalId
     * @return int|false the submission progress
     */
    public function incompleteSubmissionExists($articleId, $userId, $journalId) {
        $result = $this->retrieve(
            'SELECT submission_progress FROM articles WHERE article_id = ? AND user_id = ? AND journal_id = ? AND date_submitted IS NULL',
            [(int) $articleId, (int) $userId, (int) $journalId]
        );
        
        // [WIZDAM] FIX: Type narrowing
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) ? (int) $fields[0] : false;

        $result->Close();
        return $returner;
    }

    /**
     * Change the status of the article
     * @param int $articleId
     * @param int $status
     */
    public function changeArticleStatus($articleId, $status) {
        $this->update(
            'UPDATE articles SET status = ? WHERE article_id = ?', 
            [(int) $status, (int) $articleId]
        );
        $this->flushCache();
    }

    /**
     * Add/update an article setting.
     * @param int $articleId
     * @param string $name
     * @param mixed $value
     * @param string $type Data type of the setting.
     * @param bool $isLocalized
     */
    public function updateSetting($articleId, $name, $value, $type, $isLocalized = false) {
        if ($isLocalized) {
            if (is_array($value)) {
                $values = $value;
            } else {
                return;
            }
        } else {
            $values = ['' => $value];
        }
        unset($value);

        $keyFields = ['setting_name', 'locale', 'article_id'];
        foreach ($values as $locale => $value) {
            if ($isLocalized) {
                $this->update(
                    'DELETE FROM article_settings WHERE article_id = ? AND setting_name = ? AND locale = ?',
                    [(int) $articleId, $name, $locale]
                );
                if (empty($value)) continue;
            }

            $value = $this->convertToDB($value, $type);

            $this->replace('article_settings',
                [
                    'article_id' => (int) $articleId,
                    'setting_name' => $name,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'locale' => $locale
                ],
                $keyFields
            );
        }
        $this->flushCache();
    }

    /**
     * Change the public ID of an article.
     * @param int $articleId
     * @param string $pubIdType
     * @param string $pubId
     */
    public function changePubId($articleId, $pubIdType, $pubId) {
        $this->updateSetting($articleId, 'pub-id::'.$pubIdType, $pubId, 'string');
    }

    /**
     * Checks if public identifier exists (other than for the specified article ID).
     * @param string $pubIdType
     * @param string $pubId
     * @param int $articleId An ID to be excluded from the search.
     * @param int $journalId
     * @return bool
     */
    public function pubIdExists($pubIdType, $pubId, $articleId, $journalId) {
        $result = $this->retrieve(
            'SELECT COUNT(*)
            FROM article_settings ast
                INNER JOIN articles a ON ast.article_id = a.article_id
            WHERE ast.setting_name = ? AND ast.setting_value = ? AND ast.article_id <> ? AND a.journal_id = ?',
            [
                'pub-id::'.$pubIdType,
                $pubId,
                (int) $articleId,
                (int) $journalId
            ]
        );
        
        // [WIZDAM] FIX: Type narrowing + logika > 0
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = isset($fields[0]) && (int) $fields[0] > 0;
        
        $result->Close();
        return $returner;
    }

    /**
     * Removes articles from a section by section ID
     * @param int $sectionId
     */
    public function removeArticlesFromSection($sectionId) {
        $this->update(
            'UPDATE articles SET section_id = null WHERE section_id = ?', 
            [(int) $sectionId]
        );
        $this->flushCache();
    }

    /**
     * Delete and re-initialize the attached licenses of all articles in a journal.
     * @param int $journalId
     */
    public function resetPermissions($journalId) {
        $journalId = (int) $journalId;
        $articles = $this->getArticlesByJournalId($journalId);
        while ($article = $articles->next()) {
            $this->update(
                'DELETE FROM article_settings WHERE (setting_name = ? OR setting_name = ? OR setting_name = ?) AND article_id = ?',
                ['licenseURL', 'copyrightHolder', 'copyrightYear', (int) $article->getId()]
            );
            $article = $this->getArticle($article->getId());
            if ($article) {
                $article->initializePermissions();
                $this->updateLocaleFields($article);
            }
            unset($article);
        }
        $this->flushCache();
    }

    /**
     * Delete the public IDs of all articles in a journal.
     * @param int $journalId
     * @param string $pubIdType
     */
    public function deleteAllPubIds($journalId, $pubIdType) {
        $journalId = (int) $journalId;
        $settingName = 'pub-id::'.$pubIdType;

        $articles = $this->getArticlesByJournalId($journalId);
        while ($article = $articles->next()) {
            $this->update(
                'DELETE FROM article_settings WHERE setting_name = ? AND article_id = ?',
                [$settingName, (int) $article->getId()]
            );
            unset($article);
        }
        $this->flushCache();
    }

    /**
     * Delete the public ID of an article.
     * @param int $articleId
     * @param string $pubIdType
     */
    public function deletePubId($articleId, $pubIdType) {
        $settingName = 'pub-id::'.$pubIdType;
        $this->update(
            'DELETE FROM article_settings WHERE setting_name = ? AND article_id = ?',
            [$settingName, (int) $articleId]
        );
        $this->flushCache();
    }

    /**
     * Get the ID of the last inserted article.
     * @return int
     */
    public function getInsertArticleId() {
        return $this->getInsertId('articles', 'article_id');
    }

    /**
     * Flush the cache.
     */
    public function flushCache() {
        $cache = $this->_getCache();
        $cache->flush();
        
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        if ($publishedArticleDao) {
            $cache = $publishedArticleDao->_getPublishedArticleCache();
            $cache->flush();
        }
    }
    
    /**
     * [WIZDAM] Mengambil data timeline editorial (genesis).
     * @param int $articleId
     * @return array
     */
    public function getEditorialTimeline($articleId) {
        $timeline = [
            'revisionDate' => null,
            'acceptedDate' => null
        ];
        
        // [WIZDAM] FIX: Parameter dibungkus array
        $result = $this->retrieve(
            "SELECT decision, date_decided 
             FROM edit_decisions 
             WHERE article_id = ?
             ORDER BY date_decided ASC",
            [(int) $articleId]
        );
        
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                if ($row['decision'] == 2) $timeline['revisionDate'] = $row['date_decided'];
                elseif ($row['decision'] == 1) $timeline['acceptedDate'] = $row['date_decided'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $timeline;
    }

    /**
     * [ORCHESTRATOR] Centralized Article Identifiers Resolution
     * @param int $articleId
     * @return array|false
     */
    public function getArticleIdentifiers(int $articleId) {
        $articleData = $this->fetchArticleData($articleId);
        if (!$articleData) return false;

        $journalId = (int) $articleData['journal_id'];
        $datePublished = $articleData['date_published'];

        $currentELocator = $this->getSettingValue($articleId, 'eLocator');
        $currentPii = $this->getSettingValue($articleId, 'pii');

        // WIZDAM SELF-HEALING: Audit kelayakan PII lama
        if ($currentPii !== '') {
            if (!$this->auditExistingPii($currentPii)) {
                $this->update("DELETE FROM article_settings WHERE article_id = ? AND setting_name = 'pii'", [$articleId]);
                $currentPii = ''; 
            }
        }

        if ($currentELocator !== '' && $currentPii !== '') {
            return ['eLocator' => $currentELocator, 'pii' => $currentPii];
        }

        if ($currentELocator === '') {
            $currentELocator = $this->generateELocator($articleId);
        }

        if ($currentPii === '') {
            $currentPii = $this->generatePii($articleId, $journalId, $datePublished, $currentELocator);
        }

        return ['eLocator' => $currentELocator, 'pii' => $currentPii];
    }

    /**
     * [HELPER] Fetch basic article data
     * @param int $articleId
     * @return array|false
     */
    private function fetchArticleData(int $articleId) {
        $result = $this->retrieve(
            "SELECT a.journal_id, pa.date_published 
             FROM articles a 
             LEFT JOIN published_articles pa ON a.article_id = pa.article_id 
             WHERE a.article_id = ?", 
            [$articleId]
        );
        
        if ($result->EOF) {
            $result->Close();
            return false;
        }
        $row = $result->GetRowAssoc(false);
        $result->Close();
        return $row;
    }

    /**
     * [HELPER] Get a specific setting value for an article
     * @param int $articleId
     * @param string $settingName
     * @return string
     */
    private function getSettingValue(int $articleId, string $settingName): string {
        $result = $this->retrieve(
            "SELECT setting_value FROM article_settings WHERE article_id = ? AND setting_name = ?", 
            [$articleId, $settingName]
        );
        
        // [WIZDAM] FIX: Type narrowing
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) ? (string) $fields[0] : '';
        
        $result->Close();
        return $returner;
    }

    /**
     * [WORKER] Audit existing PII using strict mathematical check digit
     * @param string $pii
     * @return bool
     */
    private function auditExistingPii(string $pii): bool {
        if (strlen($pii) !== 18 || substr($pii, 0, 1) !== 'P') {
            return false;
        }

        $extractedIssn = substr($pii, 1, 8);
        $reconstructedIssn = substr($extractedIssn, 0, 4) . '-' . substr($extractedIssn, 4, 4);

        import('lib.pkp.classes.validation.ValidatorISSN');
        $validator = new ValidatorISSN();
        
        return $validator->isValid($reconstructedIssn);
    }

    /**
     * [WORKER] Generate and save eLocator
     * @param int $articleId
     * @return string
     */
    private function generateELocator(int $articleId): string {
        $secretSalt = Config::getVar('security', 'salt');
        if (empty($secretSalt)) {
            $secretSalt = 'WizdamSafe_' . Config::getVar('general', 'base_url');
        }

        $hashHex = substr(md5($articleId . microtime(true) . $secretSalt), 0, 8);
        $hashInt = hexdec($hashHex);
        $numeric7 = str_pad((string)($hashInt % 10000000), 7, '0', STR_PAD_LEFT);
        $generatedELocator = 'f' . $numeric7;
        
        $this->updateSetting($articleId, 'eLocator', $generatedELocator, 'string');
        return $generatedELocator;
    }

    /**
     * [WORKER] Generate and save PII strictly relying on ValidatorISSN
     * @param int $articleId
     * @param int $journalId
     * @param string|null $datePublished
     * @param string $eLocator
     * @return string
     */
    private function generatePii(int $articleId, int $journalId, ?string $datePublished, string $eLocator): string {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao ? $journalDao->getById($journalId) : null;
        
        $rawIssn = $journal ? ($journal->getSetting('onlineIssn') ?: $journal->getSetting('printIssn')) : '';
        
        import('lib.pkp.classes.validation.ValidatorISSN');
        $validator = new ValidatorISSN();

        if ($validator->isValid($rawIssn)) {
            $issnClean = str_replace('-', '', strtoupper($rawIssn));
            
            $yymm = $datePublished ? date('ym', strtotime($datePublished)) : date('ym');
            $numeric7 = substr($eLocator, 1);
            $piiSuffix = substr($numeric7, 0, 5);
            
            $generatedPii = 'P' . $issnClean . $yymm . $piiSuffix;
            
            $this->updateSetting($articleId, 'pii', $generatedPii, 'string');
            return $generatedPii;
        }

        return '';
    }
}
?>