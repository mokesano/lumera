<?php
declare(strict_types=1);

/**
 * @file classes/DuraCloudStreamContent.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudStreamContent
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud API content model for streams
 * [WIZDAM EDITION] Refactored for PHP 7.x/8.x (Property Type Hint Removed for Safety)
 */

class DuraCloudStreamContent extends DuraCloudContent {
    
    /** @var int */
    protected $size = 0;

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
    public function DuraCloudStreamContent($contentDescriptor) {
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
     * Set the content size
     * @param int $size
     */
    public function setSize($size) {
        $this->size = (int) $size;
    }

    /**
     * Get the size of the content.
     * @return int
     */
    public function getSize() {
        return $this->size;
    }
}

?>