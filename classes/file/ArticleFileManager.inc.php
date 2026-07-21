<?php
declare(strict_types=1);

/**
 * @file classes/file/ArticleFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleFileManager
 * @ingroup file
 *
 * @brief Class defining operations for article file management.
 */

import('lib.pkp.classes.file.FileManager');
import('classes.article.ArticleFile');

class ArticleFileManager extends FileManager {

    /** @var string the path to location of the files */
    public $filesDir;

    /** @var int the ID of the associated article */
    public $articleId;

    /** @var Article|null the associated article */
    public $article;

    /**
     * Constructor.
     * @param int $articleId
     */
    public function __construct($articleId) {
        $this->articleId = (int) $articleId;
        
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->article = $articleDao->getArticle($this->articleId);

        $journalId = $this->article ? (int) $this->article->getJournalId() : 0;
        $this->filesDir = Config::getVar('files', 'files_dir') . '/journals/' . $journalId .  '/articles/' . $this->articleId . '/';

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     * @param int $articleId
     */
    public function ArticleFileManager($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::ArticleFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct($articleId);
    }

    /**
     * Upload a submission file.
     * @param string $fileName
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function uploadSubmissionFile($fileName, $fileId = null, $overwrite = false, &$errorMsg = null) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_SUBMISSION, $fileId !== null ? (int) $fileId : null, $overwrite, $errorMsg);
    }

    /**
     * Upload a file to the review file folder.
     * @param string $fileName
     * @param int|null $fileId
     * @return int|bool
     */
    public function uploadReviewFile($fileName, $fileId = null) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_REVIEW, $fileId !== null ? (int) $fileId : null);
    }

    /**
     * Upload a file to the editor decision file folder.
     * @param string $fileName
     * @param int|null $fileId
     * @return int|bool
     */
    public function uploadEditorDecisionFile($fileName, $fileId = null) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_EDITOR, $fileId !== null ? (int) $fileId : null);
    }

    /**
     * Upload a file to the copyedit file folder.
     * @param string $fileName
     * @param int|null $fileId
     * @return int|bool
     */
    public function uploadCopyeditFile($fileName, $fileId = null) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_COPYEDIT, $fileId !== null ? (int) $fileId : null);
    }

    /**
     * Upload a section editor's layout editing file.
     * @param string $fileName
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function uploadLayoutFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_LAYOUT, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Upload a supp file.
     * @param string $fileName
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function uploadSuppFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_SUPP, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Upload a public file.
     * @param string $fileName
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function uploadPublicFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_PUBLIC, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Upload a note file.
     * @param string $fileName
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function uploadSubmissionNoteFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload((string) $fileName, ARTICLE_FILE_NOTE, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Write a public file.
     * @param string $fileName
     * @param string $contents
     * @param string $mimeType
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function writePublicFile($fileName, $contents, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleWrite((string) $fileName, (string) $contents, (string) $mimeType, ARTICLE_FILE_PUBLIC, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Copy a public file.
     * @param string $url
     * @param string $mimeType
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function copyPublicFile($url, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleCopy((string) $url, (string) $mimeType, ARTICLE_FILE_PUBLIC, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Write a supplemental file.
     * @param string $fileName
     * @param string $contents
     * @param string $mimeType
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function writeSuppFile($fileName, $contents, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleWrite((string) $fileName, (string) $contents, (string) $mimeType, ARTICLE_FILE_SUPP, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Copy a supplemental file.
     * @param string $url
     * @param string $mimeType
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function copySuppFile($url, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleCopy((string) $url, (string) $mimeType, ARTICLE_FILE_SUPP, $fileId !== null ? (int) $fileId : null, $overwrite);
    }

    /**
     * Copy an attachment file.
     * @param string $url
     * @param string $mimeType
     * @param int|null $fileId
     * @param bool $overwrite
     * @param int|null $assocId
     * @return int|bool
     */
    public function copyAttachmentFile($url, $mimeType, $fileId = null, $overwrite = true, $assocId = null) {
        return $this->handleCopy(
            (string) $url, 
            (string) $mimeType, 
            ARTICLE_FILE_ATTACHMENT, 
            $fileId !== null ? (int) $fileId : null, 
            $overwrite, 
            $assocId !== null ? (int) $assocId : null
        );
    }

    /**
     * Retrieve file information by file ID.
     * @param int $fileId
     * @param int|null $revision
     * @return ArticleFile|null
     */
    public function getFile($fileId, $revision = null) {
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        return $articleFileDao->getArticleFile((int) $fileId, $revision !== null ? (int) $revision : null, $this->articleId);
    }

    /**
     * Read a file's contents.
     * @param int $fileId
     * @param int|null $revision
     * @param bool $output
     * @return bool|string
     */
    public function readFile($fileId, $revision = null, $output = false) {
        $articleFile = $this->getFile((int) $fileId, $revision);

        if ($articleFile !== null) {
            $filePath = $this->filesDir . $this->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
            return parent::readFile($filePath, $output);
        }
        return false;
    }

    /**
     * Delete a file by ID.
     * If no revision is specified, all revisions of the file are deleted.
     * @param int $fileId
     * @param int|null $revision
     * @return int number of files removed
     */
    public function deleteFile($fileId, $revision = null) {
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $files = [];
        if ($revision !== null) {
            $file = $articleFileDao->getArticleFile((int) $fileId, (int) $revision);
            if ($file !== null) {
                $files[] = $file;
            }
        } else {
            $revisions = $articleFileDao->getArticleFileRevisions((int) $fileId);
            $files = is_array($revisions) ? $revisions : [];
        }

        if (!empty($files)) {
            foreach ($files as $f) {
                parent::deleteFile($this->filesDir . $this->fileStageToPath($f->getFileStage()) . '/' . $f->getFileName());
            }
        }

        $articleFileDao->deleteArticleFileById((int) $fileId, $revision !== null ? (int) $revision : null);

        return count($files);
    }

    /**
     * Delete the entire tree of files belonging to an article.
     */
    public function deleteArticleTree() {
        parent::rmtree($this->filesDir);
    }

    /**
     * Download a file.
     * @param int $fileId
     * @param int|null $revision
     * @param bool $inline
     * @param string|null $fileName
     * @return bool
     */
    public function downloadFile($fileId, $revision = null, $inline = false, $fileName = null) {
        $articleFile = $this->getFile((int) $fileId, $revision);

        if ($articleFile !== null) {
            $fileType = $articleFile->getFileType();
            $filePath = $this->filesDir . $this->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
            return parent::downloadFile($filePath, $fileType, $inline, $fileName);
        }
        return false;
    }

    /**
     * Copies an existing file to create a review file.
     * @param int $fileId
     * @param int|null $revision
     * @param int|null $destFileId
     * @return int|bool
     */
    public function copyToReviewFile($fileId, $revision = null, $destFileId = null) {
        return $this->copyAndRenameFile((int) $fileId, $revision !== null ? (int) $revision : null, ARTICLE_FILE_REVIEW, $destFileId !== null ? (int) $destFileId : null);
    }

    /**
     * Copies an existing file to create an editor decision file.
     * @param int $fileId
     * @param int|null $revision
     * @param int|null $destFileId
     * @return int|bool
     */
    public function copyToEditorFile($fileId, $revision = null, $destFileId = null) {
        return $this->copyAndRenameFile((int) $fileId, $revision !== null ? (int) $revision : null, ARTICLE_FILE_EDITOR, $destFileId !== null ? (int) $destFileId : null);
    }

    /**
     * Copies an existing file to create a copyedit file.
     * @param int $fileId
     * @param int|null $revision
     * @return int|bool
     */
    public function copyToCopyeditFile($fileId, $revision = null) {
        return $this->copyAndRenameFile((int) $fileId, $revision !== null ? (int) $revision : null, ARTICLE_FILE_COPYEDIT);
    }

    /**
     * Copies an existing file to create a layout file.
     * @param int $fileId
     * @param int|null $revision
     * @return int|bool
     */
    public function copyToLayoutFile($fileId, $revision = null) {
        return $this->copyAndRenameFile((int) $fileId, $revision !== null ? (int) $revision : null, ARTICLE_FILE_LAYOUT);
    }

    /**
     * Return path associated with a file stage code.
     * @param int $fileStage
     * @return string
     */
    public function fileStageToPath($fileStage) {
        switch ((int) $fileStage) {
            case ARTICLE_FILE_PUBLIC: return 'public';
            case ARTICLE_FILE_SUPP: return 'supp';
            case ARTICLE_FILE_NOTE: return 'note';
            case ARTICLE_FILE_REVIEW: return 'submission/review';
            case ARTICLE_FILE_EDITOR: return 'submission/editor';
            case ARTICLE_FILE_COPYEDIT: return 'submission/copyedit';
            case ARTICLE_FILE_LAYOUT: return 'submission/layout';
            case ARTICLE_FILE_ATTACHMENT: return 'attachment';
            case ARTICLE_FILE_SUBMISSION: 
            default: return 'submission/original';
        }
    }

    /**
     * Return abbreviation associated with a file stage code (used for naming files).
     * @param int $fileStage
     * @return string
     */
    public function fileStageToAbbrev($fileStage) {
        switch ((int) $fileStage) {
            case ARTICLE_FILE_PUBLIC: return 'PB';
            case ARTICLE_FILE_SUPP: return 'SP';
            case ARTICLE_FILE_NOTE: return 'NT';
            case ARTICLE_FILE_REVIEW: return 'RV';
            case ARTICLE_FILE_EDITOR: return 'ED';
            case ARTICLE_FILE_COPYEDIT: return 'CE';
            case ARTICLE_FILE_LAYOUT: return 'LE';
            case ARTICLE_FILE_ATTACHMENT: return 'AT';
            case ARTICLE_FILE_SUBMISSION: 
            default: return 'SM';
        }
    }

    /**
     * Copies an existing ArticleFile and renames it.
     * @param int $sourceFileId
     * @param int $sourceRevision
     * @param int $fileStage
     * @param int|null $destFileId
     * @return int|bool
     */
    public function copyAndRenameFile($sourceFileId, $sourceRevision, $fileStage, $destFileId = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::copyAndRenameFile', [&$sourceFileId, &$sourceRevision, &$fileStage, &$destFileId, &$result])) {
            return $result;
        }

        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $articleFile = new ArticleFile();

        $fileStagePath = $this->fileStageToPath((int) $fileStage);
        $destDir = $this->filesDir . $fileStagePath . '/';

        if ($destFileId !== null) {
            $currentRevision = $articleFileDao->getRevisionNumber((int) $destFileId);
            $revision = ($currentRevision !== null ? (int) $currentRevision : 0) + 1;
        } else {
            $revision = 1;
        }

        $sourceArticleFile = $articleFileDao->getArticleFile((int) $sourceFileId, (int) $sourceRevision, $this->articleId);

        if ($sourceArticleFile === null) {
            return false;
        }

        $sourceDir = $this->filesDir . $this->fileStageToPath($sourceArticleFile->getFileStage()) . '/';

        if ($destFileId !== null) {
            $articleFile->setFileId($destFileId);
        }
        $articleFile->setArticleId($this->articleId);
        $articleFile->setSourceFileId($sourceFileId);
        $articleFile->setSourceRevision($sourceRevision);
        $articleFile->setFileName($sourceArticleFile->getFileName());
        $articleFile->setFileType($sourceArticleFile->getFileType());
        $articleFile->setFileSize($sourceArticleFile->getFileSize());
        $articleFile->setOriginalFileName($sourceArticleFile->getOriginalFileName());
        $articleFile->setFileStage($fileStage);
        $articleFile->setDateUploaded(Core::getCurrentDate());
        $articleFile->setDateModified(Core::getCurrentDate());
        if ($this->article) {
            $articleFile->setRound($this->article->getCurrentRound());
        }
        $articleFile->setRevision($revision);

        $fileId = $articleFileDao->insertArticleFile($articleFile);

        // Rename the file.
        $fileExtension = $this->parseFileExtension($sourceArticleFile->getFileName());
        $newFileName = $this->articleId . '-' . $fileId . '-' . $revision . '-' . $this->fileStageToAbbrev($fileStage) . '.' . $fileExtension;

        if (!$this->fileExists($destDir, 'dir')) {
            $this->mkdirtree($destDir);
        }

        copy($sourceDir . $sourceArticleFile->getFileName(), $destDir . $newFileName);

        $articleFile->setFileName($newFileName);
        $articleFileDao->updateArticleFile($articleFile);

        return $fileId;
    }

    /**
     * PRIVATE routine to generate a dummy file. Used in handleUpload.
     * @param object $article
     * @return ArticleFile
     */
    public function generateDummyFile($article) {
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $articleFile = new ArticleFile();

        $articleFile->setArticleId($article->getId());
        $articleFile->setFileName('temp');
        $articleFile->setOriginalFileName('temp');
        $articleFile->setFileType('temp');
        $articleFile->setFileSize(0);
        $articleFile->setFileStage(1);
        $articleFile->setDateUploaded(Core::getCurrentDate());
        $articleFile->setDateModified(Core::getCurrentDate());
        $articleFile->setRound(0);
        $articleFile->setRevision(1);

        $articleFile->setFileId($articleFileDao->insertArticleFile($articleFile));

        return $articleFile;
    }

    /**
     * PRIVATE routine to remove all prior revisions of a file.
     * @param int $fileId
     * @param int $revision
     */
    public function removePriorRevisions($fileId, $revision) {
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $revisions = $articleFileDao->getArticleFileRevisions((int) $fileId);

        if (is_array($revisions)) {
            foreach ($revisions as $revisionFile) {
                if ($revisionFile->getRevision() !== $revision) {
                    $this->deleteFile($fileId, $revisionFile->getRevision());
                }
            }
        }
    }

    /**
     * PRIVATE routine to generate a filename for an article file.
     * @param ArticleFile $articleFile
     * @param int $fileStage
     * @param string $originalName
     * @return string
     */
    public function generateFilename($articleFile, $fileStage, $originalName) {
        $extension = $this->parseFileExtension((string) $originalName);
        $newFileName = $articleFile->getArticleId() . '-' . $articleFile->getFileId() . '-' . $articleFile->getRevision() . '-' . $this->fileStageToAbbrev((int) $fileStage) . '.' . $extension;
        $articleFile->setFileName($newFileName);

        return $newFileName;
    }

    /**
     * PRIVATE routine to upload the file and add it to the database.
     * @param string $fileName
     * @param int $fileStage
     * @param int|null $fileId
     * @param bool $overwrite
     * @param string|null &$errorMsg [LUMERA FIX] Tambahkan parameter referensi ini
     * @return int|bool
     */
    public function handleUpload($fileName, $fileStage, $fileId = null, $overwrite = false, &$errorMsg = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::handleUpload', [&$fileName, &$fileStage, &$fileId, &$overwrite, &$result])) {
            return $result;
        }

        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath((int) $fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        $dummyFile = false;
        if ($fileId === null) {
            $dummyFile = true;
            $articleFile = $this->generateDummyFile($this->article);
        } else {
            $articleFile = new ArticleFile();
            $currentRev = $articleFileDao->getRevisionNumber((int) $fileId);
            $articleFile->setRevision(($currentRev !== null ? (int) $currentRev : 0) + 1);
            $articleFile->setArticleId($this->articleId);
            $articleFile->setFileId((int) $fileId);
            $articleFile->setDateUploaded(Core::getCurrentDate());
            $articleFile->setDateModified(Core::getCurrentDate());
        }

        // [LUMERA FIX] Set errorMsg jika file tidak ada
        if (!isset($_FILES[$fileName]) || !is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            if ($dummyFile) {
                $articleFileDao->deleteArticleFileById($articleFile->getFileId());
            }
            $errorMsg = __('common.uploadFailed');
            return false;
        }

        $articleFile->setFileType((string) $this->getUploadedFileType($fileName));
        $articleFile->setFileSize((int) $_FILES[$fileName]['size']);
        $articleFile->setOriginalFileName($this->truncateFileName((string) $_FILES[$fileName]['name'], 127));
        $articleFile->setFileStage((int) $fileStage);
        if ($this->article) {
            $articleFile->setRound($this->article->getCurrentRound());
        }

        $newFileName = $this->generateFilename($articleFile, $fileStage, $this->getUploadedFileName($fileName));

        $errorMsg = null; // Reset sebelum dipanggil
        if (!$this->uploadFile($fileName, $dir . $newFileName, $errorMsg)) {
            if ($dummyFile) {
                $articleFileDao->deleteArticleFileById($articleFile->getFileId());
            }
            if (empty($errorMsg)) {
                $errorMsg = __('common.uploadFailed');
            }
            return false;
        }

        if ($dummyFile) {
            $articleFileDao->updateArticleFile($articleFile);
        } else {
            $articleFileDao->insertArticleFile($articleFile);
        }

        if ($overwrite) {
            $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());
        }

        return $articleFile->getFileId();
    }

    /**
     * PRIVATE routine to write an article file and add it to the database.
     * @param string $fileName
     * @param string $contents
     * @param string $mimeType
     * @param int $fileStage
     * @param int|null $fileId
     * @param bool $overwrite
     * @return int|bool
     */
    public function handleWrite($fileName, $contents, $mimeType, $fileStage, $fileId = null, $overwrite = false) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::handleWrite', [&$fileName, &$contents, &$mimeType, &$fileId, &$overwrite, &$result])) {
            return $result;
        }

        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath((int) $fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        $dummyFile = false;
        if ($fileId === null) {
            $dummyFile = true;
            $articleFile = $this->generateDummyFile($this->article);
        } else {
            $articleFile = new ArticleFile();
            $currentRev = $articleFileDao->getRevisionNumber((int) $fileId);
            $articleFile->setRevision(($currentRev !== null ? (int) $currentRev : 0) + 1);
            $articleFile->setArticleId($this->articleId);
            $articleFile->setFileId((int) $fileId);
            $articleFile->setDateUploaded(Core::getCurrentDate());
            $articleFile->setDateModified(Core::getCurrentDate());
        }

        $articleFile->setFileType((string) $mimeType);
        $articleFile->setFileSize((int) strlen((string) $contents));
        $articleFile->setOriginalFileName($this->truncateFileName((string) $fileName, 127));
        $articleFile->setFileStage((int) $fileStage);
        if ($this->article) {
            $articleFile->setRound($this->article->getCurrentRound());
        }

        $newFileName = $this->generateFilename($articleFile, $fileStage, $fileName);

        if (!$this->writeFile($dir . $newFileName, $contents)) {
            if ($dummyFile) {
                $articleFileDao->deleteArticleFileById($articleFile->getFileId());
            }
            return false;
        }

        if ($dummyFile) {
            $articleFileDao->updateArticleFile($articleFile);
        } else {
            $articleFileDao->insertArticleFile($articleFile);
        }

        if ($overwrite) {
            $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());
        }

        return $articleFile->getFileId();
    }

    /**
     * PRIVATE routine to copy an article file and add it to the database.
     * @param string $url
     * @param string $mimeType
     * @param int $fileStage
     * @param int|null $fileId
     * @param bool $overwrite
     * @param int|null $assocId
     * @return int|bool
     */
    public function handleCopy($url, $mimeType, $fileStage, $fileId = null, $overwrite = false, $assocId = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::handleCopy', [&$url, &$mimeType, &$fileStage, &$fileId, &$overwrite, &$assocId, &$result])) {
            return $result;
        }

        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath((int) $fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        $dummyFile = false;
        if ($fileId === null) {
            $dummyFile = true;
            $articleFile = $this->generateDummyFile($this->article);
        } else {
            $articleFile = new ArticleFile();
            $currentRev = $articleFileDao->getRevisionNumber((int) $fileId);
            $articleFile->setRevision(($currentRev !== null ? (int) $currentRev : 0) + 1);
            $articleFile->setArticleId($this->articleId);
            $articleFile->setFileId((int) $fileId);
            $articleFile->setDateUploaded(Core::getCurrentDate());
            $articleFile->setDateModified(Core::getCurrentDate());
        }

        $articleFile->setFileType((string) $mimeType);
        $articleFile->setOriginalFileName($this->truncateFileName((string) basename($url), 127));
        $articleFile->setFileStage((int) $fileStage);
        if ($this->article) {
            $articleFile->setRound($this->article->getCurrentRound());
        }
        if ($assocId !== null) {
            $articleFile->setAssocId((int) $assocId);
        }

        $newFileName = $this->generateFilename($articleFile, $fileStage, $articleFile->getOriginalFileName());

        if (!$this->copyFile($url, $dir . $newFileName)) {
            if ($dummyFile) {
                $articleFileDao->deleteArticleFileById($articleFile->getFileId());
            }
            return false;
        }

        $articleFile->setFileSize((int) filesize($dir . $newFileName));

        if ($dummyFile) {
            $articleFileDao->updateArticleFile($articleFile);
        } else {
            $articleFileDao->insertArticleFile($articleFile);
        }

        if ($overwrite) {
            $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());
        }

        return $articleFile->getFileId();
    }

    /**
     * Copy a temporary file to an article file.
     * @param TemporaryFile $temporaryFile
     * @param int $fileStage
     * @param int|null $assocId
     * @return int|bool
     */
    public function temporaryFileToArticleFile($temporaryFile, $fileStage, $assocId = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::temporaryFileToArticleFile', [&$temporaryFile, &$fileStage, &$assocId, &$result])) {
            return $result;
        }

        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath((int) $fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        $articleFile = $this->generateDummyFile($this->article);
        $articleFile->setFileType((string) $temporaryFile->getFileType());
        $articleFile->setOriginalFileName((string) $temporaryFile->getOriginalFileName());
        $articleFile->setFileStage((int) $fileStage);
        if ($this->article) {
            $articleFile->setRound($this->article->getCurrentRound());
        }
        if ($assocId !== null) {
            $articleFile->setAssocId((int) $assocId);
        }

        $newFileName = $this->generateFilename($articleFile, $fileStage, $articleFile->getOriginalFileName());

        if (!$this->copyFile($temporaryFile->getFilePath(), $dir . $newFileName)) {
            $articleFileDao->deleteArticleFileById($articleFile->getFileId());
            return false;
        }

        $articleFile->setFileSize((int) filesize($dir . $newFileName));
        $articleFileDao->updateArticleFile($articleFile);
        $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());

        return $articleFile->getFileId();
    }

}
?>