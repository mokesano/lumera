<?php
declare(strict_types=1);

/**
 * @file plugins/themes/sangiapub/SangiaPub.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Sangia Publishing
 * @ingroup plugins_themes_sangiapub
 *
 * @brief "sangiapub" theme plugin
 */

import('classes.plugins.ThemePlugin');

class SangiaPub extends ThemePlugin {
    
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName(): string {
		return 'SangiaPub';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName(): string {
		return 'Sangia Publishing Modern Theme';
	}

	/**
	 * Get the description of this plugin.
	 * @return String
	 */
	function getDescription(): string {
		return 'Sangia Publishing Modern Theme is Publishing on behalf of SRM Publishing publishes journals, monographs, and reference in print and online. Since 2017 Sangia declared to up growth publisher in Indonesia and our world. This plugins to dedicated comitment about them. Plugin implements no customizeable theme.';
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
										. 'sangiapub' 
										. DIRECTORY_SEPARATOR 
										. 'templates';   
											      
		$templateMgr->compile_id = 'sangiapub';
	}
}

?>