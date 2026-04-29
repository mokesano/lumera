<?php
declare(strict_types=1);

/**
 * @file plugins/themes/publisher/PublisherThemePlugin.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublisherThemePlugin
 * @ingroup plugins_themes_publisher
 *
 * @brief "publisher" theme plugin
 */

import('classes.plugins.ThemePlugin');

class PublisherThemePlugin extends ThemePlugin {
    
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName(): string {
		return 'PublisherThemePlugin';
	}

    /**
     * Get the display name of this plugin.
     * @return String
     */
	function getDisplayName(): string {
		return 'Sangia Publisher Theme';
	}

    /**
     * Get a description of the plugin.
     * @return String
     */
	function getDescription(): string {
		return 'Sangia Publisher Group layout';
	}

    /**
     * Get the filename of the locale data for this plugin.
     * @param $locale String
     * @return String
     */
	function getLocaleFilename($locale) {
		return null; // No locale data
	}

    /**
     * Activate the theme.
     * @param $templateMgr TemplateManager
     * @return void
     */
	function activate($templateMgr) {
		$templateMgr->template_dir[0] = Core::getBaseDir() 
										. DIRECTORY_SEPARATOR 
										. 'plugins' 
										. DIRECTORY_SEPARATOR 
										. 'themes' 
										. DIRECTORY_SEPARATOR 
										. 'publisher' 
										. DIRECTORY_SEPARATOR 
										. 'templates';   
											      
		$templateMgr->compile_id = 'publisherTheme';
	}
}

?>