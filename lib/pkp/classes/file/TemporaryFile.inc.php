<?php
declare(strict_types=1);

/**
 * @file classes/file/TemporaryFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemporaryFile
 * @ingroup file
 * @see TemporaryFileDAO
 *
 * @brief Temporary file class.
 */

import('lib.pkp.classes.file.PKPFile');

class TemporaryFile extends PKPFile {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TemporaryFile() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::TemporaryFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Return absolute path to the file on the host filesystem.
     * @return string
     */
    public function getFilePath() {
        import('lib.pkp.classes.file.PKPTemporaryFileManager');
        $temporaryFileManager = new PKPTemporaryFileManager();
        return $temporaryFileManager->getBasePath() . $this->getFileName();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of associated user.
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set ID of associated user.
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }
}

?>