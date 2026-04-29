<?php
declare(strict_types=1);

/**
 * @file classes/DuraCloudFileContent.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudFileContent
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud API content model for files
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, Visibility, Standardized SHIM)
 */

class DuraCloudFileContent extends DuraCloudContent {
    
    /**
     * Constructor
     * @param object $contentDescriptor
     */
    public function __construct($contentDescriptor) {
        parent::__construct($contentDescriptor);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudFileContent($contentDescriptor) {
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
     * Open a file by name.
     * @param string $fileName
     * @param string $mode
     */
    public function open($fileName, $mode = 'r') {
        $this->fp = fopen($fileName, $mode);
    }

    /**
     * Get the size of the content.
     * as it involves seeking to the end first.
     * @return int
     */
    public function getSize() {
        // PHP 8 throws TypeError if fseek is called on non-resource (e.g. if fopen failed)
        if (!is_resource($this->fp)) {
            return 0;
        }

        fseek($this->fp, 0, SEEK_END);
        $i = ftell($this->fp);
        fseek($this->fp, 0, SEEK_SET);
        
        return (int) $i;
    }
}

?>