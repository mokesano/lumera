<?php
declare(strict_types=1);

/**
 * @file plugins/themes/sangia_old/SangiaThemePlugin.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SangiaOldTheme
 * @ingroup plugins_themes_sangia_old
 *
 * @brief "sangia_old" theme plugin
 */

import('classes.plugins.ThemePlugin');

class SangiaThemePlugin extends ThemePlugin {
    
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName(): string {
		return 'SangiaThemePlugin';
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName(): string {
		return 'Sangia Classical Old';
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription(): string {
		return 'classical Sangia Publishing Group theme for journals layout';
	}

	/**
	 * Get the locale file name of this plugin
     * @param $locale string
     * @return string
	 */
	function getLocaleFilename($locale) {
		return null; // No locale data
	}

	/**
	 * Get the template of this plugin
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
										. 'sangia_old' 
										. DIRECTORY_SEPARATOR 
										. 'templates';   
											      
		$templateMgr->compile_id = 'sangiaTheme';
	}
}

?>