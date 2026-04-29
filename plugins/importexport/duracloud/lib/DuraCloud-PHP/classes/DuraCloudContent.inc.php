<?php
declare(strict_types=1);

/**
 * @file classes/DuraCloudContent.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudContent
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud API content
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, Reference Cleanup, Safety Checks)
 */

class DuraCloudContent {
    
    /** @var object */
    protected $contentDescriptor;

    /** @var resource|null */
    protected $fp;

    /**
     * Constructor
     * @param object $contentDescriptor
     */
    public function __construct($contentDescriptor) {
        $this->contentDescriptor = $contentDescriptor;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudContent($contentDescriptor) {
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
     * Get content descriptor
     * @return object
     */
    public function getDescriptor() {
        return $this->contentDescriptor;
    }

    /**
     * Set resource file pointer
     * @param resource $fp
     */
    public function setResource($fp) {
        $this->fp = $fp;
    }

    /**
     * Get resource file pointer
     * @return resource|null
     */
    public function getResource() {
        return $this->fp;
    }

    /**
     * Close the resource
     */
    public function close() {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
}

?>