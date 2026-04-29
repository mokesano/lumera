<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/ListbuilderMap.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ListbuilderMap
 * @ingroup controllers_listbuilder
 *
 * @brief Utility class representing a simple name / value association
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

class ListbuilderMap {
    /** @var mixed */
    protected $key = null;

    /** @var string */
    protected $value = '';

    /**
     * Constructor
     * @param mixed $key
     * @param string $value
     */
    public function __construct($key, string $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ListbuilderMap($key, $value) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($key, (string) $value);
    }

    /**
     * Get the key for this map
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Get the value for this map
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }
}

?>