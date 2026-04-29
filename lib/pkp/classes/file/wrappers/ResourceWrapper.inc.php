<?php
declare(strict_types=1);

/**
 * @file classes/file/wrappers/ResourceWrapper.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResourceWrapper
 * @ingroup file_wrappers
 *
 * @brief Class abstracting operations for accessing resources.
 */

class ResourceWrapper extends FileWrapper {
    
    /**
     * Constructor.
     * @param resource $fp The file pointer resource
     */
    public function __construct($fp) {
        // Di PHP moderen, resource passed by handle, tidak perlu reference
        $this->fp = $fp;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ResourceWrapper($fp) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::ResourceWrapper(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($fp);
    }

    /**
     * Open the file.
     * @param $mode string
     * @return boolean
     */
    public function open($mode = 'r') {
        // The resource should already be open
        return true;
    }
}

?>