<?php
declare(strict_types=1);

/**
 * @file pages/manager/FilesHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilesHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for files browser functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class FilesHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FilesHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display the files associated with a journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function files($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'manager'), 'manager.journalManagement']]);

        // [WIZDAM] Initialize variables before passing by reference
        $currentDir = '';
        $parentDir = '';
        $this->_parseDirArg($args, $currentDir, $parentDir);
        
        $currentPath = $this->_getRealFilesDir($request, $currentDir);

        if (@is_file($currentPath)) {
            if ((int) $request->getUserVar('download')) {
                $fileManager->downloadFile($currentPath);
            } else {
                $fileManager->downloadFile($currentPath, $this->_fileMimeType($currentPath), true);
            }

        } else {
            $files = [];
            if ($dh = @opendir($currentPath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $currentPath . '/'. $file;
                        $isDir = is_dir($filePath);
                        $info = [
                            'name' => $file,
                            'isDir' => $isDir,
                            'mimetype' => $isDir ? '' : $this->_fileMimeType($filePath),
                            'mtime' => filemtime($filePath),
                            'size' => $isDir ? '' : $fileManager->getNiceFileSize(filesize($filePath)),
                        ];
                        $files[$file] = $info;
                    }
                }
                closedir($dh);
            }
            ksort($files);
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('files', $files);
            $templateMgr->assign('currentDir', $currentDir);
            $templateMgr->assign('parentDir', $parentDir);
            $templateMgr->assign('helpTopicId', 'journal.managementPages.fileBrowser');
            $templateMgr->display('manager/files/index.tpl');
        }
    }

    /**
     * Upload a new file.
     * @param array $args
     * @param PKPRequest $request
     */
    public function fileUpload($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $currentDir = '';
        $parentDir = '';
        $this->_parseDirArg($args, $currentDir, $parentDir);
        
        $currentPath = $this->_getRealFilesDir($request, $currentDir);

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        if ($fileManager->uploadedFileExists('file')) {
            $destPath = $currentPath . '/' . $this->_cleanFileName($fileManager->getUploadedFileName('file'));
            @$fileManager->uploadFile('file', $destPath);
        }

        $request->redirect(null, null, 'files', explode('/', $currentDir));
    }

    /**
     * Create a new directory
     * @param array $args
     * @param PKPRequest $request
     */
    public function fileMakeDir($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $currentDir = '';
        $parentDir = '';
        $this->_parseDirArg($args, $currentDir, $parentDir);

        if ($dirName = trim((string) basename($request->getUserVar('dirName')))) {
            $currentPath = $this->_getRealFilesDir($request, $currentDir);
            $newDir = $currentPath . '/' . $this->_cleanFileName($dirName);

            import('lib.pkp.classes.file.FileManager');
            $fileManager = new FileManager();
            @$fileManager->mkdir($newDir);
        }

        $request->redirect(null, null, 'files', explode('/', $currentDir));
    }

    /**
     * Delete a file.
     * @param array $args
     * @param PKPRequest $request
     */
    public function fileDelete($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $currentDir = '';
        $parentDir = '';
        $this->_parseDirArg($args, $currentDir, $parentDir);
        
        $currentPath = $this->_getRealFilesDir($request, $currentDir);

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        if (@is_file($currentPath)) {
            $fileManager->deleteFile($currentPath);
        } else {
            // TODO Use recursive delete (rmtree) instead?
            @$fileManager->rmdir($currentPath);
        }

        $request->redirect(null, null, 'files', explode('/', $parentDir));
    }


    //
    // Helper functions
    // FIXME Move some of these functions into common class (FileManager?)
    //

    /**
     * Parse directory arguments.
     * [WIZDAM] Retained reference & for Output Accumulator
     * @param array $args
     * @param string $currentDir
     * @param string $parentDir
     */
    protected function _parseDirArg($args, &$currentDir, &$parentDir) {
        $pathArray = array_filter($args, [$this, '_fileNameFilter']);
        $currentDir = join('/', $pathArray);
        array_pop($pathArray);
        $parentDir = join('/', $pathArray);
    }

    /**
     * Get real file path.
     * @param PKPRequest $request
     * @param string $currentDir
     * @return string
     */
    protected function _getRealFilesDir($request, $currentDir) {
        $journal = $request->getJournal();
        return Config::getVar('files', 'files_dir') . '/journals/' . $journal->getId() .'/' . $currentDir;
    }

    /**
     * Filter filename.
     * @param string $var
     * @return bool
     */
    protected function _fileNameFilter($var) {
        return (!empty($var) && $var != '..' && $var != '.' && strpos($var, '/') === false);
    }

    /**
     * Clean filename.
     * @param string $var
     * @return string
     */
    protected function _cleanFileName($var) {
        $var = PKPString::regexp_replace('/[^\w\-\.]/', '', $var);
        if (!$this->_fileNameFilter($var)) {
            $var = time() . '';
        }
        return $var;
    }

    /**
     * Get mime type.
     * @param string $filePath
     * @return string
     */
    protected function _fileMimeType($filePath) {
        return PKPString::mime_content_type($filePath);
    }
}

?>