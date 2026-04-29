<?php
declare(strict_types=1);

/**
 * @file classes/core/Registry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Registry
 * @ingroup core
 *
 * @brief Maintains a static table of keyed references.
 * Used for storing/accessing single instance objects and values.
 * WIZDAM EDITION: Pure PHP 8 Strict (No References &)
 */


class Registry {
    
    /** * @var array Static storage for registry items.
     * Direct property access is optimized for PHP 8.
     */
    private static $_registry = array();

    /**
     * Constructor.
     */
    public function __construct() {
        // Not implement construct
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Registry() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Registry(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the registry data structure.
     * WIZDAM: Returns a COPY of the array in PHP 8 (Pass-by-value).
     * Modifying the result of this function will NOT modify the global registry 
     * unless explicitly set back via Registry::setHooks().
     * @return array
     */
    public static function getRegistry() {
        return self::$_registry;
    }

    /**
     * Get the value of an item in the registry.
     * WIZDAM: Strictly returns by value/handle. No reference (&).
     * * @param $key string
     * @param $createIfEmpty boolean Whether or not to create the entry if none exists
     * @param $createWithDefault mixed If $createIfEmpty, this value will be used as a default
     * @return mixed The value (Object Handle or Array Copy)
     */
    public static function get($key, $createIfEmpty = false, $createWithDefault = null) {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        } elseif ($createIfEmpty) {
            self::$_registry[$key] = $createWithDefault;
            return self::$_registry[$key];
        }

        return null;
    }

    /**
     * Set the value of an item in the registry.
     * WIZDAM: Strictly pass by value/handle.
     * @param $key string
     * @param $value mixed
     */
    public static function set($key, $value) {
        self::$_registry[$key] = $value;
    }

    /**
     * Remove an item from the registry.
     * @param $key string
     */
    public static function delete($key) {
        if (isset(self::$_registry[$key])) {
            unset(self::$_registry[$key]);
        }
    }

    /**
     * Clear the registry.
     */
    public static function clear() {
        self::$_registry = array();
    }
}

?>