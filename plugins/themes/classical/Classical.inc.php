<?php
declare(strict_types=1);

/**
 * @file plugins/themes/classical/Classical.inc.php
 *
 * Copyright (c) 2017-2026 Codecanau, LLC and CodeLumera, LLC
 * Copyright (c) 2017-2026 Rochmady and Teams
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Classical
 * @ingroup plugins_themes_classical
 *
 * @brief "Classical" theme plugin
 */

import('classes.plugins.ThemePlugin');

class Classical extends ThemePlugin {
    
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName(): string {
		return 'Classical';
	}

    /**
     * Get the display name of this plugin.
     * @return String
     */
	function getDisplayName(): string {
		return 'New Classical';
	}

    /**
     * Get a description of the plugin.
     * @return String
     */
	function getDescription(): string {
		return 'Classical Sangia Publishing Group theme for journals layout';
	}

	/**
	 * Get the locale file name of this plugin.
     * @see ThemePlugin::getLocaleFilename()
     * @param string $locale
     * @return string
     * @return null Since this plugin does not have locale data.
	 */
	function getLocaleFilename($locale) {
		return null; // No locale data
	}

	/**
	 * Get the template of this plugin.
     * The path is relative to the base directory.
     * @see ThemePlugin::activate()
     * @param TemplateManager $templateMgr
     * @return string Path to the template directory for this theme plugin.
     * @return void
	 */
	function activate($templateMgr) {
		$templateMgr->template_dir[0] = Core::getBaseDir() 
										. DIRECTORY_SEPARATOR 
										. 'plugins' 
										. DIRECTORY_SEPARATOR 
										. 'themes' 
										. DIRECTORY_SEPARATOR 
										. 'classical' 
										. DIRECTORY_SEPARATOR 
										. 'templates';   
											      
		$templateMgr->compile_id = 'Classical';
	}
}
?>