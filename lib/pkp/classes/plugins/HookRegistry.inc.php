<?php
declare(strict_types=1);

/**
 * @file classes/plugins/HookRegistry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HookRegistry
 * @ingroup plugins
 *
 * @brief Class for linking core functionality with plugins
 */

class HookRegistry {
    
    /** @var array The list of registered hooks */
    private static $hooks = array();

    /** @var bool Flag for testing/debugging */
    private static $rememberCalledHooks = false;

    /** @var array List of called hooks for testing */
    private static $calledHooks = array();

    /**
     * Get the current set of hook registrations.
     * @return array
     */
    public static function getHooks() {
        return self::$hooks;
    }

    /**
     * Set the hooks table for the given hook name.
     * @param string $hookName string
     * @param array $hooksToRegister array
     */
    public static function setHooks($hookName, $hooksToRegister) {
        self::$hooks[$hookName] = $hooksToRegister;
    }

    /**
     * Clear hooks registered against the given name.
     * @param string $hookName string
     */
    public static function clear($hookName) {
        if (isset(self::$hooks[$hookName])) {
            unset(self::$hooks[$hookName]);
        }
    }

    /**
     * Register a hook against the given hook name.
     * @param string $hookName string
     * @param mixed $callback mixed Callback (object/array/string)
     */
    public static function register($hookName, $callback) {
        if (!isset(self::$hooks[$hookName])) {
            self::$hooks[$hookName] = array();
        }
        self::$hooks[$hookName][] = $callback;
    }

    /**
	 * Dispatch each callback registered against $hookName in sequence.
     * @param string $hookName string
     * @param $args mixed
     * @return mixed
     */
    public static function dispatch($hookName, $args = null) {
        // For testing only.
        if (self::$rememberCalledHooks) {
            self::$calledHooks[] = array(
                $hookName, $args
            );
        }

        if (!isset(self::$hooks[$hookName])) {
            return false;
        }

        $result = false;
        $isSidebarHook = ($hookName == 'Templates::Common::LeftSidebar' || $hookName == 'Templates::Common::RightSidebar');

        if ($isSidebarHook) {
            foreach (self::$hooks[$hookName] as $hook) {
               $result = call_user_func($hook, $hookName, $args);
            }
        } else {
            foreach (self::$hooks[$hookName] as $hook) {
                if ($result = call_user_func($hook, $hookName, $args)) {
                    break;
                }
            }
        }
        
        return $result;
    }

    /**
     * LEGACY SHIM: Call each callback registered against $hookName in sequence.
     * Mengarahkan ke dispatch() untuk eksekusi logika.
     * @param string $hookName string
     * @param mixed $args mixed
     * @return mixed
     */
    public static function call($hookName, $args = null) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $callerInfo = isset($trace[0]['file']) 
            ? "Called from " . $trace[0]['file'] . " on line " . $trace[0]['line'] 
            : "Caller unknown";

        trigger_error(
           "Deprecated: HookRegistry::call('$hookName') used. $callerInfo. Please refactor to HookRegistry::dispatch().", 
           E_USER_DEPRECATED
        );

        return self::dispatch($hookName, $args);
    }

    //
    // Methods required for testing only.
    //
    
    /**
     * Set/query the flag that triggers storing of called hooks.
     * @param $askOnly boolean
     * @param $updateTo boolean
     * @return boolean
     */
    public static function rememberCalledHooks($askOnly = false, $updateTo = true) {
        if (!$askOnly) {
            self::$rememberCalledHooks = $updateTo;
        }
        return self::$rememberCalledHooks;
    }

    /**
     * Switch off the function to store hooks and delete all stored hooks.
     * @param $leaveAlive boolean
     */
    public static function resetCalledHooks($leaveAlive = false) {
        if (!$leaveAlive) self::rememberCalledHooks(false, false);
        self::$calledHooks = array();
    }

    /**
     * Return the stored hooks.
     * @return array
     */
    public static function getCalledHooks() {
        return self::$calledHooks;
    }
    
}
?>