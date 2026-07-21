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
        if ($fileId === null || (int) $fileId === 0) {
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

        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (is_array($row)) {
                $returner = $this->_returnArticleFileFromRow($row);
            }
        }

        if ($result) {
            $result->Close();
        }
        
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
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                if (is_array($row)) {
                    $articleFiles[] = $this->_returnArticleFileFromRow($row);
                }
                $result->moveNext();
            }
            $result->Close();
        }

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
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                if (is_array($row)) {
                    $articleFiles[] = $this->_returnArticleFileFromRow($row);
                }
                $result->moveNext();
            }
            $result->Close();
        }

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
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (is_array($row) && isset($row['max_revision'])) {
                $returner = (int) $row['max_revision'];
            }
        }

        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve all article files for an article.
     * 
     * @param int $articleId
     * @return array ArticleFiles
     */
    public function getArticleFilesByArticle($articleId) {
        $result = $this->retrieve(
            'SELECT * FROM article_files WHERE article_id = ?',
            [(int) $articleId]
        );

        $articleFiles = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                if (is_array($row)) {
                    $articleFiles[] = $this->_returnArticleFileFromRow($row);
                }
                $result->moveNext();
            }
            $result->Close();
        }

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
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                if (is_array($row)) {
                    $articleFiles[] = $this->_returnArticleFileFromRow($row);
                }
                $result->moveNext();
            }
            $result->Close();
        }

        return $articleFiles;
    }

    /**
     * Internal function to return an ArticleFile object from a row.
     * 
     * @param array $row
     * @return ArticleFile
     */
    public function _returnArticleFileFromRow($row) {
        if (!is_array($row)) {
            return null; // Safety check
        }

        $articleFile = new ArticleFile();
        $articleFile->setFileId((int) ($row['file_id'] ?? 0));
        $articleFile->setSourceFileId(isset($row['source_file_id']) ? (int) $row['source_file_id'] : null);
        $articleFile->setSourceRevision(isset($row['source_revision']) ? (int) $row['source_revision'] : null);
        $articleFile->setRevision((int) ($row['revision'] ?? 1));
        $articleFile->setArticleId((int) ($row['article_id'] ?? 0));
        $articleFile->setFileName((string) ($row['file_name'] ?? ''));
        $articleFile->setFileType((string) ($row['file_type'] ?? ''));
        $articleFile->setFileSize((int) ($row['file_size'] ?? 0));
        $articleFile->setOriginalFileName((string) ($row['original_file_name'] ?? ''));
        $articleFile->setFileStage((int) ($row['file_stage'] ?? 1));
        $articleFile->setAssocId(isset($row['assoc_id']) ? (int) $row['assoc_id'] : null);
        $articleFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded'] ?? null));
        $articleFile->setDateModified($this->datetimeFromDB($row['date_modified'] ?? null));
        $articleFile->setRound(isset($row['round']) ? (int) $row['round'] : 1);
        $articleFile->setViewable((bool) ($row['viewable'] ?? false));
        
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
        $revision = $articleFile->getRevision() !== null ? (int) $articleFile->getRevision() : 1;
        $articleId = $articleFile->getArticleId() !== null ? (int) $articleFile->getArticleId() : 0;
        $sourceFileId = $articleFile->getSourceFileId() ? (int) $articleFile->getSourceFileId() : null;
        $sourceRevision = $articleFile->getSourceRevision() ? (int) $articleFile->getSourceRevision() : null;
        $fileName = (string) $articleFile->getFileName();
        $fileType = (string) $articleFile->getFileType();
        $fileSize = (int) $articleFile->getFileSize();
        $originalFileName = (string) $articleFile->getOriginalFileName();
        $fileStage = (int) $articleFile->getFileStage();
        $round = $articleFile->getRound() !== null ? (int) $articleFile->getRound() : 1;
        $viewable = $articleFile->getViewable() ? 1 : 0;
        $assocId = $articleFile->getAssocId() ? (int) $articleFile->getAssocId() : null;

        $params = [
            $revision,
            $articleId,
            $sourceFileId,
            $sourceRevision,
            $fileName,
            $fileType,
            $fileSize,
            $originalFileName,
            $fileStage,
            $round,
            $viewable,
            $assocId
        ];

        if ($fileId) {
            array_unshift($params, (int) $fileId);
        }

        $dateUploaded = $articleFile->getDateUploaded() ? $this->datetimeToDB($articleFile->getDateUploaded()) : null;
        $dateModified = $articleFile->getDateModified() ? $this->datetimeToDB($articleFile->getDateModified()) : null;
        
        array_splice($params, 9, 0, [$dateUploaded, $dateModified]);

        $this->update(
            sprintf('INSERT INTO article_files
                (' . ($fileId ? 'file_id, ' : '') . 'revision, article_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, file_stage, date_uploaded, date_modified, round, viewable, assoc_id)
                VALUES
                (' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'),
            $params
        );

        if (!$fileId) {
            $articleFile->setFileId((int) $this->getInsertArticleFileId());
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
        $articleId = $articleFile->getArticleId() !== null ? (int) $articleFile->getArticleId() : 0;
        $sourceFileId = $articleFile->getSourceFileId() ? (int) $articleFile->getSourceFileId() : null;
        $sourceRevision = $articleFile->getSourceRevision() ? (int) $articleFile->getSourceRevision() : null;
        $fileName = (string) $articleFile->getFileName();
        $fileType = (string) $articleFile->getFileType();
        $fileSize = (int) $articleFile->getFileSize();
        $originalFileName = (string) $articleFile->getOriginalFileName();
        $fileStage = (int) $articleFile->getFileStage();
        $round = $articleFile->getRound() !== null ? (int) $articleFile->getRound() : 1;
        $viewable = $articleFile->getViewable() ? 1 : 0;
        $assocId = $articleFile->getAssocId() ? (int) $articleFile->getAssocId() : null;
        $fileId = (int) $articleFile->getFileId();
        $revision = (int) $articleFile->getRevision();

        $params = [
            $articleId,
            $sourceFileId,
            $sourceRevision,
            $fileName,
            $fileType,
            $fileSize,
            $originalFileName,
            $fileStage,
            $round,
            $viewable,
            $assocId,
            $fileId,
            $revision
        ];

        $dateUploaded = $articleFile->getDateUploaded() ? $this->datetimeToDB($articleFile->getDateUploaded()) : null;
        $dateModified = $articleFile->getDateModified() ? $this->datetimeToDB($articleFile->getDateModified()) : null;
        
        array_splice($params, 8, 0, [$dateUploaded, $dateModified]);

        $this->update(
            'UPDATE article_files
                SET
                    article_id = ?,
                    source_file_id = ?,
                    source_revision = ?,
                    file_name = ?,
                    file_type = ?,
                    file_size = ?,
                    original_file_name = ?,
                    file_stage = ?,
                    date_uploaded = ?,
                    date_modified = ?,
                    round = ?,
                    viewable = ?,
                    assoc_id = ?
                WHERE file_id = ? AND revision = ?',
            $params
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
        return (int) $this->getInsertId('article_files', 'file_id');
    }
    
}
?>