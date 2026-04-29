<?php
declare(strict_types=1);

/**
 * @file classes/file/PrivateFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PrivateFileManager
 * @ingroup file
 *
 * @brief Class defining operations for private file management.
 */

import('lib.pkp.classes.file.FileManager');

class PrivateFileManager extends FileManager {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PrivateFileManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PrivateFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Get the base path for file storage.
     * @return string
     */
    public function getBasePath() {
        return Config::getVar('files', 'files_dir');
    }
}

?>