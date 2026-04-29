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
 *
 * Article directory structure:
 * [article id]/note
 * [article id]/public
 * [article id]/submission
 * [article id]/submission/original
 * [article id]/submission/review
 * [article id]/submission/editor
 * [article id]/submission/copyedit
 * [article id]/submission/layout
 * [article id]/supp
 * [article id]/attachment
 */

import('lib.pkp.classes.file.FileManager');
import('classes.article.ArticleFile');

class ArticleFileManager extends FileManager {

    /** @var string the path to location of the files */
    public $filesDir;

    /** @var int the ID of the associated article */
    public $articleId;

    /** @var Article the associated article */
    public $article;

    /**
     * Constructor.
     * Create a manager for handling article file uploads.
     * @param $articleId int
     */
    public function __construct($articleId) {
        $this->articleId = $articleId;
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->article = $articleDao->getArticle($articleId);
        $journalId = $this->article->getJournalId();
        $this->filesDir = Config::getVar('files', 'files_dir') . '/journals/' . $journalId .  '/articles/' . $articleId . '/';

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleFileManager($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::ArticleFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($articleId);
    }

    /**
     * Upload a submission file.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @param $overwrite boolean
     * @return int|boolean file ID, is false if failure
     */
    public function uploadSubmissionFile($fileName, $fileId = null, $overwrite = false) {
        return $this->handleUpload($fileName, ARTICLE_FILE_SUBMISSION, $fileId, $overwrite);
    }

    /**
     * Upload a file to the review file folder.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @return int|boolean file ID, is false if failure
     */
    public function uploadReviewFile($fileName, $fileId = null) {
        return $this->handleUpload($fileName, ARTICLE_FILE_REVIEW, $fileId);
    }

    /**
     * Upload a file to the editor decision file folder.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @return int|boolean file ID, is false if failure
     */
    public function uploadEditorDecisionFile($fileName, $fileId = null) {
        return $this->handleUpload($fileName, ARTICLE_FILE_EDITOR, $fileId);
    }

    /**
     * Upload a file to the copyedit file folder.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @return int|boolean file ID, is false if failure
     */
    public function uploadCopyeditFile($fileName, $fileId = null) {
        return $this->handleUpload($fileName, ARTICLE_FILE_COPYEDIT, $fileId);
    }

    /**
     * Upload a section editor's layout editing file.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @param $overwrite boolean
     * @return int|boolean file ID, is null if failure
     */
    public function uploadLayoutFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload($fileName, ARTICLE_FILE_LAYOUT, $fileId, $overwrite);
    }

    /**
     * Upload a supp file.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @param $overwrite boolean
     * @return int|boolean file ID, is false if failure
     */
    public function uploadSuppFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload($fileName, ARTICLE_FILE_SUPP, $fileId, $overwrite);
    }

    /**
     * Upload a public file.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @param $overwrite boolean
     * @return int|boolean file ID, is false if failure
     */
    public function uploadPublicFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload($fileName, ARTICLE_FILE_PUBLIC, $fileId, $overwrite);
    }

    /**
     * Upload a note file.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @param $overwrite boolean
     * @return int|boolean file ID, is false if failure
     */
    public function uploadSubmissionNoteFile($fileName, $fileId = null, $overwrite = true) {
        return $this->handleUpload($fileName, ARTICLE_FILE_NOTE, $fileId, $overwrite);
    }

    /**
     * Write a public file.
     * @param $fileName string The original filename
     * @param $contents string The contents to be written to the file
     * @param $mimeType string The mime type of the original file
     * @param $fileId int
     * @param $overwrite boolean
     */
    public function writePublicFile($fileName, $contents, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleWrite($fileName, $contents, $mimeType, ARTICLE_FILE_PUBLIC, $fileId, $overwrite);
    }

    /**
     * Copy a public file.
     * @param $url string The source URL/filename
     * @param $mimeType string The mime type of the original file
     * @param $fileId int
     * @param $overwrite boolean
     */
    public function copyPublicFile($url, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleCopy($url, $mimeType, ARTICLE_FILE_PUBLIC, $fileId, $overwrite);
    }

    /**
     * Write a supplemental file.
     * @param $fileName string The original filename
     * @param $contents string The contents to be written to the file
     * @param $mimeType string The mime type of the original file
     * @param $fileId int
     * @param $overwrite boolean
     */
    public function writeSuppFile($fileName, $contents, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleWrite($fileName, $contents, $mimeType, ARTICLE_FILE_SUPP, $fileId, $overwrite);
    }

    /**
     * Copy a supplemental file.
     * @param $url string The source URL/filename
     * @param $mimeType string The mime type of the original file
     * @param $fileId int
     * @param $overwrite boolean
     */
    public function copySuppFile($url, $mimeType, $fileId = null, $overwrite = true) {
        return $this->handleCopy($url, $mimeType, ARTICLE_FILE_SUPP, $fileId, $overwrite);
    }

    /**
     * Copy an attachment file.
     * @param $url string The source URL/filename
     * @param $mimeType string The mime type of the original file
     * @param $fileId int
     * @param $overwrite boolean
     * @param $assocId int
     */
    public function copyAttachmentFile($url, $mimeType, $fileId = null, $overwrite = true, $assocId = null) {
        return $this->handleCopy($url, $mimeType, ARTICLE_FILE_ATTACHMENT, $fileId, $overwrite, $assocId);
    }

    /**
     * Retrieve file information by file ID.
     * @param $fileId int
     * @param $revision int
     * @return ArticleFile
     */
    public function getFile($fileId, $revision = null) {
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $articleFile = $articleFileDao->getArticleFile($fileId, $revision, $this->articleId);
        return $articleFile;
    }

    /**
     * Read a file's contents.
     * @param $fileId int
     * @param $revision int
     * @param $output boolean output the file's contents instead of returning a string
     * @return boolean|string
     */
    public function readFile($fileId, $revision = null, $output = false) {
        $articleFile = $this->getFile($fileId, $revision);

        if (isset($articleFile)) {
            $filePath = $this->filesDir .  $this->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
            return parent::readFile($filePath, $output);
        } else {
            return false;
        }
    }

    /**
     * Delete a file by ID.
     * If no revision is specified, all revisions of the file are deleted.
     * @param $fileId int
     * @param $revision int (optional)
     * @return int number of files removed
     */
    public function deleteFile($fileId, $revision = null) {
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $files = array();
        if (isset($revision)) {
            $file = $articleFileDao->getArticleFile($fileId, $revision);
            if (isset($file)) {
                $files[] = $file;
            }

        } else {
            $files = $articleFileDao->getArticleFileRevisions($fileId);
        }

        if ($files) foreach ($files as $f) {
            parent::deleteFile($this->filesDir . $this->fileStageToPath($f->getFileStage()) . '/' . $f->getFileName());
        }

        $articleFileDao->deleteArticleFileById($fileId, $revision);

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
     * @param $fileId int the file id of the file to download
     * @param $revision int the revision of the file to download
     * @param $inline print file as inline instead of attachment, optional
     * @param $fileName string
     * @return boolean
     */
    public function downloadFile($fileId, $revision = null, $inline = false, $fileName = null) {
        $articleFile = $this->getFile($fileId, $revision);
        if (isset($articleFile)) {
            $fileType = $articleFile->getFileType();
            $filePath = $this->filesDir .  $this->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();

            return parent::downloadFile($filePath, $fileType, $inline, $fileName);

        } else {
            return false;
        }
    }

    /**
     * Copies an existing file to create a review file.
     * @param $originalFileId int the file id of the original file.
     * @param $originalRevision int the revision of the original file.
     * @param $destFileId int the file id of the current review file
     * @return int the file id of the new file.
     */
    public function copyToReviewFile($fileId, $revision = null, $destFileId = null) {
        return $this->copyAndRenameFile($fileId, $revision, ARTICLE_FILE_REVIEW, $destFileId);
    }

    /**
     * Copies an existing file to create an editor decision file.
     * @param $fileId int the file id of the review file.
     * @param $revision int the revision of the review file.
     * @param $destFileId int file ID to copy to
     * @return int the file id of the new file.
     */
    public function copyToEditorFile($fileId, $revision = null, $destFileId = null) {
        return $this->copyAndRenameFile($fileId, $revision, ARTICLE_FILE_EDITOR, $destFileId);
    }

    /**
     * Copies an existing file to create a copyedit file.
     * @param $fileId int the file id of the editor file.
     * @param $revision int the revision of the editor file.
     * @return int the file id of the new file.
     */
    public function copyToCopyeditFile($fileId, $revision = null) {
        return $this->copyAndRenameFile($fileId, $revision, ARTICLE_FILE_COPYEDIT);
    }

    /**
     * Copies an existing file to create a layout file.
     * @param $fileId int the file id of the copyedit file.
     * @param $revision int the revision of the copyedit file.
     * @return int the file id of the new file.
     */
    public function copyToLayoutFile($fileId, $revision = null) {
        return $this->copyAndRenameFile($fileId, $revision, ARTICLE_FILE_LAYOUT);
    }

    /**
     * Return path associated with a file stage code.
     * @param $fileStage int
     * @return string
     */
    public function fileStageToPath($fileStage) {
        switch ($fileStage) {
            case ARTICLE_FILE_PUBLIC: return 'public';
            case ARTICLE_FILE_SUPP: return 'supp';
            case ARTICLE_FILE_NOTE: return 'note';
            case ARTICLE_FILE_REVIEW: return 'submission/review';
            case ARTICLE_FILE_EDITOR: return 'submission/editor';
            case ARTICLE_FILE_COPYEDIT: return 'submission/copyedit';
            case ARTICLE_FILE_LAYOUT: return 'submission/layout';
            case ARTICLE_FILE_ATTACHMENT: return 'attachment';
            case ARTICLE_FILE_SUBMISSION: default: return 'submission/original';
        }
    }

    /**
     * Return abbreviation associated with a file stage code (used for naming files).
     * @param $fileStage int
     * @return string
     */
    public function fileStageToAbbrev($fileStage) {
        switch ($fileStage) {
            case ARTICLE_FILE_PUBLIC: return 'PB';
            case ARTICLE_FILE_SUPP: return 'SP';
            case ARTICLE_FILE_NOTE: return 'NT';
            case ARTICLE_FILE_REVIEW: return 'RV';
            case ARTICLE_FILE_EDITOR: return 'ED';
            case ARTICLE_FILE_COPYEDIT: return 'CE';
            case ARTICLE_FILE_LAYOUT: return 'LE';
            case ARTICLE_FILE_ATTACHMENT: return 'AT';
            case ARTICLE_FILE_SUBMISSION: default: return 'SM';
        }
    }

    /**
     * Copies an existing ArticleFile and renames it.
     * @param $sourceFileId int
     * @param $sourceRevision int
     * @param $fileStage int
     * @param $destFileId int (optional)
     * @return int|boolean
     */
    public function copyAndRenameFile($sourceFileId, $sourceRevision, $fileStage, $destFileId = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::copyAndRenameFile', array(&$sourceFileId, &$sourceRevision, &$fileStage, &$destFileId, &$result))) return $result;

        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $articleFile = new ArticleFile();

        $fileStagePath = $this->fileStageToPath($fileStage);
        $destDir = $this->filesDir . $fileStagePath . '/';

        if ($destFileId != null) {
            $currentRevision = $articleFileDao->getRevisionNumber($destFileId);
            $revision = $currentRevision + 1;
        } else {
            $revision = 1;
        }

        $sourceArticleFile = $articleFileDao->getArticleFile($sourceFileId, $sourceRevision, $this->articleId);

        if (!isset($sourceArticleFile)) {
            return false;
        }

        $sourceDir = $this->filesDir .  $this->fileStageToPath($sourceArticleFile->getFileStage()) . '/';

        if ($destFileId != null) {
            $articleFile->setFileId($destFileId);
        }
        $articleFile->setArticleId($this->articleId);
        $articleFile->setSourceFileId($sourceFileId);
        $articleFile->setSourceRevision($sourceRevision);
        $articleFile->setFileName($sourceArticleFile->getFileName());
        $articleFile->setFileType($sourceArticleFile->getFileType());
        $articleFile->setFileSize($sourceArticleFile->getFileSize());
        $articleFile->setOriginalFileName($sourceArticleFile->getFileName());
        $articleFile->setFileStage($fileStage);
        $articleFile->setDateUploaded(Core::getCurrentDate());
        $articleFile->setDateModified(Core::getCurrentDate());
        $articleFile->setRound($this->article->getCurrentRound()); // FIXME This field is only applicable for review files?
        $articleFile->setRevision($revision);

        $fileId = $articleFileDao->insertArticleFile($articleFile);

        // Rename the file.
        $fileExtension = $this->parseFileExtension($sourceArticleFile->getFileName());
        $newFileName = $this->articleId.'-'.$fileId.'-'.$revision.'-'.$this->fileStageToAbbrev($fileStage).'.'.$fileExtension;

        if (!$this->fileExists($destDir, 'dir')) {
            // Try to create destination directory
            $this->mkdirtree($destDir);
        }

        copy($sourceDir.$sourceArticleFile->getFileName(), $destDir.$newFileName);

        $articleFile->setFileName($newFileName);
        $articleFileDao->updateArticleFile($articleFile);

        return $fileId;
    }

    /**
     * PRIVATE routine to generate a dummy file. Used in handleUpload.
     * @param $article object
     * @return ArticleFile
     */
    public function generateDummyFile($article) {
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
     * @param $fileId int
     * @param $revision int
     */
    public function removePriorRevisions($fileId, $revision) {
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $revisions = $articleFileDao->getArticleFileRevisions($fileId);
        if ($revisions) foreach ($revisions as $revisionFile) {
            if ($revisionFile->getRevision() != $revision) {
                $this->deleteFile($fileId, $revisionFile->getRevision());
            }
        }
    }

    /**
     * PRIVATE routine to generate a filename for an article file. Sets the filename
     * field in the articleFile to the generated value.
     * @param $articleFile ArticleFile The article to generate a filename for
     * @param $fileStage int The type of the article (e.g. as supplied to handleUpload)
     * @param $originalName string The name of the original file
     * @return string
     */
    public function generateFilename($articleFile, $fileStage, $originalName) {
        $extension = $this->parseFileExtension($originalName);
        $newFileName = $articleFile->getArticleId().'-'.$articleFile->getFileId().'-'.$articleFile->getRevision().'-'.$this->fileStageToAbbrev($fileStage).'.'.$extension;
        $articleFile->setFileName($newFileName);
        return $newFileName;
    }

    /**
     * PRIVATE routine to upload the file and add it to the database.
     * @param $fileName string index into the $_FILES array
     * @param $fileStage int identifying file stage (defined in ArticleFile)
     * @param $fileId int ID of an existing file to update
     * @param $overwrite boolean overwrite all previous revisions of the file (revision number is still incremented)
     * @return int|boolean the file ID (false if upload failed)
     */
    public function handleUpload($fileName, $fileStage, $fileId = null, $overwrite = false) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::handleUpload', array(&$fileName, &$fileStage, &$fileId, &$overwrite, &$result))) return $result;

        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath($fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        if (!$fileId) {
            // Insert dummy file to generate file id FIXME?
            $dummyFile = true;
            $articleFile = $this->generateDummyFile($this->article);
        } else {
            $dummyFile = false;
            $articleFile = new ArticleFile();
            $articleFile->setRevision($articleFileDao->getRevisionNumber($fileId)+1);
            $articleFile->setArticleId($this->articleId);
            $articleFile->setFileId($fileId);
            $articleFile->setDateUploaded(Core::getCurrentDate());
            $articleFile->setDateModified(Core::getCurrentDate());
        }

        $articleFile->setFileType($this->getUploadedFileType($fileName));
        $articleFile->setFileSize($_FILES[$fileName]['size']);
        $articleFile->setOriginalFileName($this->truncateFileName($_FILES[$fileName]['name'], 127));
        $articleFile->setFileStage($fileStage);
        $articleFile->setRound($this->article->getCurrentRound());

        $newFileName = $this->generateFilename($articleFile, $fileStage, $this->getUploadedFileName($fileName));

        $errorMsg = null;
        if (!$this->uploadFile($fileName, $dir.$newFileName, $errorMsg)) {
            // Delete the dummy file we inserted
            $articleFileDao->deleteArticleFileById($articleFile->getFileId());

            return false;
        }

        if ($dummyFile) $articleFileDao->updateArticleFile($articleFile);
        else $articleFileDao->insertArticleFile($articleFile);

        if ($overwrite) $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());

        return $articleFile->getFileId();
    }

    /**
     * PRIVATE routine to write an article file and add it to the database.
     * @param $fileName string original filename of the file
     * @param $contents string contents of the file to write
     * @param $mimeType string the mime type of the file
     * @param $fileStage int identifying type
     * @param $fileId int ID of an existing file to update
     * @param $overwrite boolean overwrite all previous revisions of the file (revision number is still incremented)
     * @return int|boolean the file ID (false if upload failed)
     */
    public function handleWrite($fileName, $contents, $mimeType, $fileStage, $fileId = null, $overwrite = false) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::handleWrite', array(&$fileName, &$contents, &$mimeType, &$fileId, &$overwrite, &$result))) return $result;

        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath($fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        if (!$fileId) {
            // Insert dummy file to generate file id FIXME?
            $dummyFile = true;
            $articleFile = $this->generateDummyFile($this->article);
        } else {
            $dummyFile = false;
            $articleFile = new ArticleFile();
            $articleFile->setRevision($articleFileDao->getRevisionNumber($fileId)+1);
            $articleFile->setArticleId($this->articleId);
            $articleFile->setFileId($fileId);
            $articleFile->setDateUploaded(Core::getCurrentDate());
            $articleFile->setDateModified(Core::getCurrentDate());
        }

        $articleFile->setFileType($mimeType);
        $articleFile->setFileSize(strlen($contents));
        $articleFile->setOriginalFileName($this->truncateFileName($fileName, 127));
        $articleFile->setFileStage($fileStage);
        $articleFile->setRound($this->article->getCurrentRound());

        $newFileName = $this->generateFilename($articleFile, $fileStage, $fileName);

        if (!$this->writeFile($dir.$newFileName, $contents)) {
            // Delete the dummy file we inserted
            $articleFileDao->deleteArticleFileById($articleFile->getFileId());

            return false;
        }

        if ($dummyFile) $articleFileDao->updateArticleFile($articleFile);
        else $articleFileDao->insertArticleFile($articleFile);

        if ($overwrite) $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());

        return $articleFile->getFileId();
    }

    /**
     * PRIVATE routine to copy an article file and add it to the database.
     * @param $url string original filename/url of the file
     * @param $mimeType string the mime type of the file
     * @param $fileStage int identifying type
     * @param $fileId int ID of an existing file to update
     * @param $overwrite boolean overwrite all previous revisions of the file (revision number is still incremented)
     * @return int|boolean the file ID (false if upload failed)
     */
    public function handleCopy($url, $mimeType, $fileStage, $fileId = null, $overwrite = false, $assocId = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::handleCopy', array(&$url, &$mimeType, &$fileStage, &$fileId, &$overwrite, &$result))) return $result;

        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath($fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        if (!$fileId) {
            // Insert dummy file to generate file id FIXME?
            $dummyFile = true;
            $articleFile = $this->generateDummyFile($this->article);
        } else {
            $dummyFile = false;
            $articleFile = new ArticleFile();
            $articleFile->setRevision($articleFileDao->getRevisionNumber($fileId)+1);
            $articleFile->setArticleId($this->articleId);
            $articleFile->setFileId($fileId);
            $articleFile->setDateUploaded(Core::getCurrentDate());
            $articleFile->setDateModified(Core::getCurrentDate());
        }

        $articleFile->setFileType($mimeType);
        $articleFile->setOriginalFileName($this->truncateFileName(basename($url), 127));
        $articleFile->setFileStage($fileStage);
        $articleFile->setRound($this->article->getCurrentRound());
        if ($assocId) $articleFile->setAssocId($assocId);

        $newFileName = $this->generateFilename($articleFile, $fileStage, $articleFile->getOriginalFileName());

        if (!$this->copyFile($url, $dir.$newFileName)) {
            // Delete the dummy file we inserted
            $articleFileDao->deleteArticleFileById($articleFile->getFileId());

            return false;
        }

        $articleFile->setFileSize(filesize($dir.$newFileName));

        if ($dummyFile) $articleFileDao->updateArticleFile($articleFile);
        else $articleFileDao->insertArticleFile($articleFile);

        if ($overwrite) $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());

        return $articleFile->getFileId();
    }

    /**
     * Copy a temporary file to an article file.
     * @param TemporaryFile $temporaryFile
     * @param int $fileStage
     * @param int $assocId
     * @return int|boolean the file ID (false if upload failed)
     */
    public function temporaryFileToArticleFile($temporaryFile, $fileStage, $assocId = null) {
        $result = null;
        if (HookRegistry::dispatch('ArticleFileManager::temporaryFileToArticleFile', array(&$temporaryFile, &$fileStage, &$assocId, &$result))) return $result;

        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $fileStagePath = $this->fileStageToPath($fileStage);
        $dir = $this->filesDir . $fileStagePath . '/';

        $articleFile = $this->generateDummyFile($this->article);
        $articleFile->setFileType($temporaryFile->getFileType());
        $articleFile->setOriginalFileName($temporaryFile->getOriginalFileName());
        $articleFile->setFileStage($fileStage);
        $articleFile->setRound($this->article->getCurrentRound());
        $articleFile->setAssocId($assocId);

        $newFileName = $this->generateFilename($articleFile, $fileStage, $articleFile->getOriginalFileName());

        if (!$this->copyFile($temporaryFile->getFilePath(), $dir.$newFileName)) {
            // Delete the dummy file we inserted
            $articleFileDao->deleteArticleFileById($articleFile->getFileId());

            return false;
        }

        $articleFile->setFileSize(filesize($dir.$newFileName));
        $articleFileDao->updateArticleFile($articleFile);
        $this->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());

        return $articleFile->getFileId();
    }
}

?>