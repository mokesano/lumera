<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customLocale/CustomLocaleAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomLocaleAction
 * @ingroup plugins_generic_customLocale
 *
 * @brief Perform various tasks related to customization of locale strings.
 */

class CustomLocaleAction {

    /**
     * Constructor
     */
    public function __construct() {
        // Intentionally empty
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CustomLocaleAction() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomLocaleAction(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the list of locale files for a specific locale.
     * @param $locale string
     * @return array|null
     */
    public static function getLocaleFiles($locale) {
        if (!AppLocale::isLocaleValid($locale)) return null;

        $localeFiles = AppLocale::makeComponentMap($locale);
        $plugins = PluginRegistry::loadAllPlugins();
        
        // Modernized foreach loop
        foreach ($plugins as $plugin) {
            $localeFile = $plugin->getLocaleFilename($locale);
            if (!empty($localeFile)) {
                if (is_scalar($localeFile)) $localeFiles[] = $localeFile;
                if (is_array($localeFile)) $localeFiles = array_merge($localeFiles, $localeFile);
            }
        }
        
        return $localeFiles;
    }

    /**
     * Check if a filename is a valid locale file for the given locale.
     * @param $locale string
     * @param $filename string
     * @return boolean
     */
    public static function isLocaleFile($locale, $filename) {
        if (in_array($filename, self::getLocaleFiles($locale))) return true;
        return false;
    }

}
?>