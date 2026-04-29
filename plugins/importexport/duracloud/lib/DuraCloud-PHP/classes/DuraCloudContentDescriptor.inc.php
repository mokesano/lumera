<?php
declare(strict_types=1);

/**
 * @file classes/DuraCloudContentDescriptor.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudContentDescriptor
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud API content descriptor
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, Visibility, Standardized SHIM)
 */

class DuraCloudContentDescriptor {
    
    /** @var array */
    protected $metadata = [];

    /** @var string|null */
    protected $md5;

    /** @var string|null */
    protected $contentType;

    /**
     * Constructor
     * @param array $metadata
     */
    public function __construct($metadata = []) {
        $this->metadata = $metadata;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudContentDescriptor($metadata = []) {
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
     * Set MD5 checksum
     * @param string $md5
     */
    public function setMD5($md5) {
        $this->md5 = $md5;
    }

    /**
     * Get MD5 checksum
     * @return string|null
     */
    public function getMD5() {
        return $this->md5;
    }

    /**
     * Set content type
     * @param string $contentType
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    /**
     * Get content type
     * @return string|null
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * Get the entire metadata set, or a particular entry
     * @param string|null $name Name of metadata element to get, or null for full array
     * @return mixed
     */
    public function getMetadata($name = null) {
        if ($name === null) return $this->metadata;
        if (!isset($this->metadata[$name])) return null;
        return $this->metadata[$name];
    }

    /**
     * Set the entire metadata set, or a particular entry
     * Usage: setMetadata(array('name' => 'value'));
     * ...or: setMetadata('name', 'value');
     * @param string|array $name
     * @param mixed $value
     */
    public function setMetadata($name, $value = null) {
        if (is_array($name)) {
            $this->metadata = $name;
        } else {
            if (!is_array($this->metadata)) {
                $this->metadata = [];
            }
            $this->metadata[$name] = $value;
        }
    }
}

?>