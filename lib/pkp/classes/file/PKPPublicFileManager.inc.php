<?php
declare(strict_types=1);

/**
 * @file classes/file/PKPPublicFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPPublicFileManager
 * @ingroup file
 *
 * @brief Wrapper class for uploading files to a site/journal's public directory.
 */

import('lib.pkp.classes.file.FileManager');

class PKPPublicFileManager extends FileManager {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPPublicFileManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPPublicFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct();
    }

    /**
     * Get the path to the site public files directory.
     * @return string
     */
    public function getSiteFilesPath() {
        return Config::getVar('files', 'public_files_dir') . '/site';
    }

    /**
     * Upload a file to the site's public directory.
     * Note: This uses the secured FileManager::uploadFile() which enforces extension whitelists.
     * @param string $fileName string
     * @param string $destFileName string
     * @return boolean
     */
    public function uploadSiteFile($fileName, $destFileName) {
        return $this->uploadFile((string) $fileName, $this->getSiteFilesPath() . '/' . (string) $destFileName);
    }

    /**
     * Delete a file from the site's public directory.
     * @param string $fileName string
     * @return boolean
     */
    public function removeSiteFile($fileName) {
        return $this->deleteFile($this->getSiteFilesPath() . '/' . (string) $fileName);
    }

}
?>