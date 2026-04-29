<?php
declare(strict_types=1);

/**
 * @file classes/plugins/ThemePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ThemePlugin
 * @ingroup plugins
 *
 * @brief Abstract class for theme plugins
 */

import('classes.plugins.Plugin');

class ThemePlugin extends Plugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ThemePlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        assert(false); // Should always be overridden
        return 'ThemePlugin';
    }

    /**
     * Get the display name of this plugin. This name is displayed on the
     * Journal Manager's setup page 5, for example.
     * @return string
     */
    public function getDisplayName(): string {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Activate the theme.
     * @param PKPTemplateManager $templateMgr
     */
    public function activate($templateMgr) {
        // Subclasses may override this function.

        $stylesheetFilename = $this->getStylesheetFilename();
        if ($stylesheetFilename) {
            $path = Request::getBaseUrl() . '/' . $this->getPluginPath() . '/' . $stylesheetFilename;
            $templateMgr->addStyleSheet($path);
        }
    }
}
?>