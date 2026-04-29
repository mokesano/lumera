<?php
declare(strict_types=1);

/**
 * @file classes/DuraCloudComponent.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudComponent
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud API client implementation base class
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, Visibility, Reference Cleanup)
 */

class DuraCloudComponent {
    
    /** @var DuraCloudConnection */
    protected $dcc;

    /** @var string */
    protected $componentName;

    /**
     * Constructor
     * @param DuraCloudConnection $dcc
     * @param string $componentName
     */
    public function __construct($dcc, $componentName) {
        $this->dcc = $dcc;
        $this->componentName = $componentName;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudComponent($dcc, $componentName) {
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
     * Get the connection object
     * @return DuraCloudConnection
     */
    public function getConnection() {
        return $this->dcc;
    }

    /**
     * Get the component prefix path
     * @return string
     */
    public function getPrefix() {
        return $this->componentName . '/';
    }
}

?>