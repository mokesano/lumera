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
 * 
 */

class Registry {
    
    /** @var array Static storage for registry items */
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
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the registry data structure.
     * Modifying the result of this function will NOT modify the global registry 
     * unless explicitly set back via Registry::setHooks().
     * @return array
     */
    public static function getRegistry() {
        return self::$_registry;
    }

    /**
     * Get the value of an item in the registry.
     * @param string $key string
     * @param $createIfEmpty boolean
     * @param $createWithDefault mixed
     * @return mixed
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
     * @param string $key string
     * @param mixed $value mixed
     */
    public static function set($key, $value) {
        self::$_registry[$key] = $value;
    }

    /**
     * Remove an item from the registry.
     * @param string $key string
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