<?php
declare(strict_types=1);

/**
 * @file classes/plugins/PluginRegistry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginRegistry
 * @ingroup plugins
 * @see Plugin
 *
 * @brief Registry class for managing plugins.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Static methods, Ref removal)
 * - Strict Typing
 * - Security hardening
 */

define('PLUGINS_PREFIX', 'plugins/');

class PluginRegistry {
    //
    // Public methods
    //

    /**
     * Return all plugins in the given category as an array, or, if the
     * category is not specified, all plugins in an associative array of
     * arrays by category.
     * @param string|null $category the name of the category to retrieve
     * @return array
     */
    public static function getPlugins($category = null) {
        $plugins = Registry::get('plugins');
        if ($plugins === null) {
            $plugins = array();
            Registry::set('plugins', $plugins);
        }

        if ($category !== null) {
            return isset($plugins[$category]) ? $plugins[$category] : array();
        }
        
        return $plugins;
    }

    /**
     * Get all plugins in a single array.
     * @return array
     */
    public static function getAllPlugins() {
        $plugins = PluginRegistry::getPlugins();
        $allPlugins = array();
        
        if (is_array($plugins)) {
            foreach ($plugins as $category => $list) {
                if (is_array($list)) {
                    $allPlugins += $list;
                }
            }
        }
        
        return $allPlugins;
    }

    /**
     * Register a plugin with the registry in the given category.
     * @param string $category the name of the category to extend
     * @param Plugin $plugin The instantiated plugin to add (Object passed by handle in PHP 5+)
     * @param string $path The path the plugin was found in
     * @return boolean True IFF the plugin was registered successfully
     */
    public static function register($category, $plugin, $path) {
        // Normalize plugin name to lower case
        $pluginName = $plugin->getName();
        $plugins = PluginRegistry::getPlugins(); // Returns copy/handle, not ref
        
        // If the plugin was already loaded, do not load it again.
        if (isset($plugins[$category][$pluginName])) return false;

        // Allow the plugin to register.
        if (!$plugin->register($category, $path)) return false;

        // Update the array structure
        if (!isset($plugins[$category])) {
            $plugins[$category] = array();
        }
        
        $plugins[$category][$pluginName] = $plugin;
        
        // Persist back to Registry
        Registry::set('plugins', $plugins);
        
        return true;
    }

    /**
     * Get a plugin by name.
     * @param string $category category name
     * @param string $name plugin name
     * @return Plugin|null
     */
    public static function getPlugin($category, $name) {
        $plugins = PluginRegistry::getPlugins();
        if (isset($plugins[$category]) && isset($plugins[$category][$name])) {
            return $plugins[$category][$name];
        }
        return null;
    }

    /**
     * Load all plugins for a given category.
     * @param string $category The name of the category to load
     * @param boolean $enabledOnly if true load only enabled plug-ins
     * @param int|null $mainContextId
     * @return array
     */
    public static function loadCategory($category, $enabledOnly = false, $mainContextId = null) {
        $plugins = array();
        
        // [WIZDAM GUARD] Pastikan kategori bukan null sebelum membentuk path
        if (is_null($category) || $category === '') {
            return $plugins;
        }
        
        $categoryDir = PLUGINS_PREFIX . $category;
        
        if (!is_dir($categoryDir)) return $plugins;

        if ($enabledOnly && Config::getVar('general', 'installed')) {
            // Get enabled plug-ins from the database.
            $application = PKPApplication::getApplication();
            $contextIdTyped = ($mainContextId === null) ? null : (int) $mainContextId;
            $products = $application->getEnabledProducts('plugins.' . $category, $contextIdTyped);
            
            foreach ($products as $product) {
                $file = $product->getProduct();
                // Instantiate without reference
                $plugin = PluginRegistry::_instantiatePlugin($category, $categoryDir, $file, $product->getProductClassname());
                
                if ($plugin && is_object($plugin)) {
                    $plugins[$plugin->getSeq()]["$categoryDir/$file"] = $plugin;
                }
            }
        } else {
            // Get all plug-ins from disk.
            $handle = opendir($categoryDir);
            if ($handle) {
                while (($file = readdir($handle)) !== false) {
                    if ($file == '.' || $file == '..') continue;
                    
                    $plugin = PluginRegistry::_instantiatePlugin($category, $categoryDir, $file);
                    
                    if ($plugin && is_object($plugin)) {
                        $plugins[$plugin->getSeq()]["$categoryDir/$file"] = $plugin;
                    }
                }
                closedir($handle);
            }
        }

        // Hook Modernization: Use dispatch
        // Arrays are primitives in PHP, so we keep & if we want the hook to modify the array
        HookRegistry::dispatch('PluginRegistry::loadCategory', array(&$category, &$plugins));

        // Register the plugins in sequence.
        ksort($plugins);
        foreach ($plugins as $seq => $junk1) {
            foreach ($plugins[$seq] as $pluginPath => $junk2) {
                PluginRegistry::register($category, $plugins[$seq][$pluginPath], $pluginPath);
            }
        }
        unset($plugins);

        // Return the list of successfully-registered plugins.
        return PluginRegistry::getPlugins($category);
    }

    /**
     * Load a specific plugin from a category by path name.
     * @param string $category
     * @param string $pathName
     * @return Plugin|null
     */
    public static function loadPlugin($category, $pathName) {
        $pluginPath = PLUGINS_PREFIX . $category . '/' . $pathName;
        $plugin = null;
        
        if (!file_exists($pluginPath . '/index.php')) return $plugin;

        // Security: Ensure path doesn't contain traversal attempts before including
        if (strpos($pathName, '..') !== false) return null;

        $plugin = include("$pluginPath/index.php");
        
        if ($plugin && is_object($plugin)) {
            PluginRegistry::register($category, $plugin, $pluginPath);
        }
        return $plugin;
    }

    /**
     * Get a list of the various plugin categories available.
     * @return array
     */
    public static function getCategories() {
        $application = PKPApplication::getApplication();
        $categories = $application->getPluginCategories();
        
        HookRegistry::dispatch('PluginRegistry::getCategories', array(&$categories));
        
        return $categories;
    }

    /**
     * Load all plugins in the system and return them in a single array.
     * @param boolean $enabledOnly load only enabled plug-ins
     * @return array
     */
    public static function loadAllPlugins($enabledOnly = false) {
        // Retrieve and register categories (order is significant).
        foreach (PluginRegistry::getCategories() as $category) {
            PluginRegistry::loadCategory($category, $enabledOnly);
        }
        return PluginRegistry::getAllPlugins();
    }


    //
    // Private helper methods
    //
    
    /**
     * Instantiate a plugin.
     * @param string $category
     * @param string $categoryDir
     * @param string $file
     * @param string|null $classToCheck
     * @return Plugin|null
     */
    private static function _instantiatePlugin($category, $categoryDir, $file, $classToCheck = null) {
        // Wizdam Security: Strict alphanumeric check prevents LFI
        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $file) || !preg_match('/^[a-zA-Z0-9_]+$/', (string) $category)) {
            // Tetap menggunakan fatalError sesuai standar keamanan ScholarWizdam Anda
            fatalError('Wizdam Security Violation: Invalid plugin naming detected for file "' . $file . '" in category "' . $category . '"');
        }

        $pluginPath = "$categoryDir/$file";
        $plugin = null;

        // Try the plug-in wrapper first (index.php)
        $pluginWrapper = "$pluginPath/index.php";
        if (file_exists($pluginWrapper)) {
            $plugin = include($pluginWrapper);
            // PHP 8: Use is_a or instanceof
            if ($classToCheck && !is_a($plugin, (string) $classToCheck)) {
                // Fail silently or log? Original asserted.
                return null;
            }
        } else {
            // Try the well-known plug-in class name next.
            $pluginClassName = ucfirst((string) $file) . ucfirst((string) $category) . 'Plugin';
            $pluginClassFile = $pluginClassName.'.inc.php';
            
            if (file_exists("$pluginPath/$pluginClassFile")) {
                // Try to instantiate the plug-in class.
                $pluginPackage = 'plugins.'.$category.'.'.$file;
                
                // Wizdam: Direct instantiation handling
                // Assuming 'instantiate' helper function handles the class loading via ADODB or import
                $plugin = instantiate($pluginPackage.'.'.$pluginClassName, $pluginClassName, $pluginPackage, 'register');
            }
        }

        // Make sure that the plug-in inherits from the right class.
        if (is_object($plugin)) {
            if (!is_a($plugin, 'Plugin')) {
                // Wizdam: Strict Type Check
                return null;
            }
        } else {
            return null;
        }

        return $plugin;
    }
}

?>