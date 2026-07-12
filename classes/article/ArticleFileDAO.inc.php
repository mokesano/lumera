<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleFileDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleFileDAO
 * @ingroup article
 * @see ArticleFile
 *
 * @brief Operations for retrieving and modifying ArticleFile objects.
 */

import('lib.pkp.classes.file.PKPFileDAO');
import('classes.article.ArticleFile');

class ArticleFileDAO extends PKPFileDAO {

    /**
     * Retrieve an article file by ID.
     * 
     * @param int $fileId
     * @param int|null $revision optional, if omitted latest revision is used
     * @param int|null $articleId optional
     * @return ArticleFile|null
     */
    public function getArticleFile($fileId, $revision = null, $articleId = null) {
        if ($fileId === null) {
            return null;
        }

        $sql = 'SELECT a.* FROM article_files a WHERE file_id = ?';
        $params = [(int) $fileId];

        if ($revision !== null) {
            $sql .= ' AND revision = ?';
            $params[] = (int) $revision;
        }

        if ($articleId !== null) {
            $sql .= ' AND article_id = ?';
            $params[] = (int) $articleId;
        }

        if ($revision === null) {
            $sql .= ' ORDER BY revision DESC';
            $result = $this->retrieveLimit($sql, $params, 1);
        } else {
            $result = $this->retrieve($sql, $params);
        }

        $returner = null;
        if ($result && $result->RecordCount() != 0) {
            $returner = $this->_returnArticleFileFromRow($result->GetRowAssoc(false));
        }

        if ($result) {
            $result->Close();
        }
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all revisions of an article file.
     * 
     * @param int $fileId
     * @param int|null $round
     * @return array ArticleFiles
     */
    public function getArticleFileRevisions($fileId, $round = null) {
        if ($fileId === null) {
            return [];
        }
        
        $sql = 'SELECT a.* FROM article_files a WHERE file_id = ?';
        $params = [(int) $fileId];

        if ($round !== null) {
            $sql .= ' AND round = ?';
            $params[] = (int) $round;
        }

        $sql .= ' ORDER BY revision';

        $result = $this->retrieve($sql, $params);

        $articleFiles = [];
        while (!$result->EOF) {
            $articleFiles[] = $this->_returnArticleFileFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $articleFiles;
    }

    /**
     * Retrieve revisions of an article file in a range.
     * 
     * @param int $fileId
     * @param int $start
     * @param int|null $end
     * @return array ArticleFiles
     */
    public function getArticleFileRevisionsInRange($fileId, $start = 1, $end = null) {
        if ($fileId === null) {
            return [];
        }

        $sql = 'SELECT a.* FROM article_files a WHERE file_id = ? AND revision >= ?';
        $params = [(int) $fileId, (int) $start];

        if ($end !== null) {
            $sql .= ' AND revision <= ?';
            $params[] = (int) $end;
        }

        $sql .= ' ORDER BY revision';

        $result = $this->retrieve($sql, $params);

        $articleFiles = [];
        while (!$result->EOF) {
            $articleFiles[] = $this->_returnArticleFileFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $articleFiles;
    }

    /**
     * Retrieve the current revision number for a file.
     * 
     * @param int $fileId
     * @return int|null
     */
    public function getRevisionNumber($fileId) {
        if ($fileId === null) {
            return null;
        }
        
        $result = $this->retrieve(
            'SELECT MAX(revision) AS max_revision FROM article_files a WHERE file_id = ?',
            [(int) $fileId]
        );

        $returner = null;
        if ($result && $result->RecordCount() > 0) {
            // [WIZDAM] Type narrowing untuk FetchRow()
            /** @var array|bool $row */
            $row = $result->FetchRow();
            $returner = isset($row['max_revision']) ? (int) $row['max_revision'] : null;
        }

        if ($result) {
            $result->Close();
        }
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all article files for an article.
     * 
     * @param int $articleId
     * @return array ArticleFiles
     */
    public function getArticleFilesByArticle($articleId) {
        // [WIZDAM] FIX: Parameter dibungkus array
        $result = $this->retrieve(
            'SELECT * FROM article_files WHERE article_id = ?',
            [(int) $articleId]
        );

        $articleFiles = [];
        while (!$result->EOF) {
            $articleFiles[] = $this->_returnArticleFileFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $articleFiles;
    }

    /**
     * Retrieve all article files for a file stage and assoc ID.
     * 
     * @param int $assocId
     * @param int $fileStage
     * @return array ArticleFiles
     */
    public function getArticleFilesByAssocId($assocId, $fileStage) {
        import('classes.file.ArticleFileManager');
        
        $result = $this->retrieve(
            'SELECT * FROM article_files WHERE assoc_id = ? AND file_stage = ?',
            [(int) $assocId, (int) $fileStage]
        );

        $articleFiles = [];
        while (!$result->EOF) {
            $articleFiles[] = $this->_returnArticleFileFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $articleFiles;
    }

    /**
     * Internal function to return an ArticleFile object from a row.
     * 
     * @param array $row
     * @return ArticleFile
     */
    public function _returnArticleFileFromRow($row) {
        $articleFile = new ArticleFile();
        $articleFile->setFileId($row['file_id']);
        $articleFile->setSourceFileId($row['source_file_id']);
        $articleFile->setSourceRevision($row['source_revision']);
        $articleFile->setRevision($row['revision']);
        $articleFile->setArticleId($row['article_id']);
        $articleFile->setFileName($row['file_name']);
        $articleFile->setFileType($row['file_type']);
        $articleFile->setFileSize($row['file_size']);
        $articleFile->setOriginalFileName($row['original_file_name']);
        $articleFile->setFileStage($row['file_stage']);
        $articleFile->setAssocId($row['assoc_id']);
        $articleFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
        $articleFile->setDateModified($this->datetimeFromDB($row['date_modified']));
        $articleFile->setRound($row['round']);
        $articleFile->setViewable($row['viewable']);
        
        HookRegistry::dispatch('ArticleFileDAO::_returnArticleFileFromRow', [$articleFile, &$row]);
        
        return $articleFile;
    }

    /**
     * Insert a new ArticleFile.
     * 
     * @param ArticleFile $articleFile
     * @return int
     */
    public function insertArticleFile($articleFile) {
        $fileId = $articleFile->getFileId();
        
        $params = [
            $articleFile->getRevision() === null ? 1 : (int) $articleFile->getRevision(),
            (int) $articleFile->getArticleId(),
            $articleFile->getSourceFileId() ? (int) $articleFile->getSourceFileId() : null,
            $articleFile->getSourceRevision() ? (int) $articleFile->getSourceRevision() : null,
            $articleFile->getFileName(),
            $articleFile->getFileType(),
            (int) $articleFile->getFileSize(),
            $articleFile->getOriginalFileName(),
            (int) $articleFile->getFileStage(),
            (int) $articleFile->getRound(),
            $articleFile->getViewable(),
            $articleFile->getAssocId() ? (int) $articleFile->getAssocId() : null
        ];

        if ($fileId) {
            array_unshift($params, (int) $fileId);
        }

        $this->update(
            sprintf('INSERT INTO article_files
                (' . ($fileId ? 'file_id, ' : '') . 'revision, article_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, file_stage, date_uploaded, date_modified, round, viewable, assoc_id)
                VALUES
                (' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?)',
                $this->datetimeToDB($articleFile->getDateUploaded()), 
                $this->datetimeToDB($articleFile->getDateModified())
            ),
            $params
        );

        if (!$fileId) {
            $articleFile->setFileId($this->getInsertArticleFileId());
        }

        return $articleFile->getFileId();
    }

    /**
     * Update an existing article file.
     * 
     * @param ArticleFile $articleFile
     * @return int
     */
    public function updateArticleFile($articleFile) {
        $this->update(
            sprintf('UPDATE article_files
                SET
                    article_id = ?,
                    source_file_id = ?,
                    source_revision = ?,
                    file_name = ?,
                    file_type = ?,
                    file_size = ?,
                    original_file_name = ?,
                    file_stage = ?,
                    date_uploaded = %s,
                    date_modified = %s,
                    round = ?,
                    viewable = ?,
                    assoc_id = ?
                WHERE file_id = ? AND revision = ?',
                $this->datetimeToDB($articleFile->getDateUploaded()), 
                $this->datetimeToDB($articleFile->getDateModified())
            ),
            [
                (int) $articleFile->getArticleId(),
                $articleFile->getSourceFileId() ? (int) $articleFile->getSourceFileId() : null,
                $articleFile->getSourceRevision() ? (int) $articleFile->getSourceRevision() : null,
                $articleFile->getFileName(),
                $articleFile->getFileType(),
                (int) $articleFile->getFileSize(),
                $articleFile->getOriginalFileName(),
                (int) $articleFile->getFileStage(),
                (int) $articleFile->getRound(),
                $articleFile->getViewable(),
                $articleFile->getAssocId() ? (int) $articleFile->getAssocId() : null,
                (int) $articleFile->getFileId(),
                (int) $articleFile->getRevision()
            ]
        );

        return $articleFile->getFileId();
    }

    /**
     * Delete an article file.
     * 
     * @param ArticleFile $articleFile
     * @return bool
     */
    public function deleteArticleFile($articleFile) {
        return $this->deleteArticleFileById($articleFile->getFileId(), $articleFile->getRevision());
    }

    /**
     * Delete an article file by ID.
     * 
     * @param int $fileId
     * @param int|null $revision
     * @return bool
     */
    public function deleteArticleFileById($fileId, $revision = null) {
        $sql = 'DELETE FROM article_files WHERE file_id = ?';
        $params = [(int) $fileId];

        if ($revision !== null) {
            $sql .= ' AND revision = ?';
            $params[] = (int) $revision;
        }

        return $this->update($sql, $params);
    }

    /**
     * Delete all article files for an article.
     * 
     * @param int $articleId
     * @return bool
     */
    public function deleteArticleFiles($articleId) {
        return $this->update(
            'DELETE FROM article_files WHERE article_id = ?', 
            [(int) $articleId]
        );
    }

    /**
     * Get the ID of the last inserted article file.
     * 
     * @return int
     */
    public function getInsertArticleFileId() {
        return $this->getInsertId('article_files', 'file_id');
    }
}
?>