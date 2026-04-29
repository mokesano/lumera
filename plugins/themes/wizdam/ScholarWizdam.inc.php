<?php
declare(strict_types=1);

/**
 * @file plugins/themes/wizdam/ScholarWizdam.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ScholarWizdam
 * @ingroup plugins_themes_premium
 *
 * @brief "ScholarWizdam" theme plugin
 */

import('classes.plugins.ThemePlugin');

class ScholarWizdam extends ThemePlugin {
    
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName(): string {
		return 'ScholarWizdam';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName(): string {
		return 'Wizdam Premium Theme';
	}

	/**
	 * Get the description of this plugin.
	 * @return String
	 */
	function getDescription(): string {
		return 'Scholar Wizdam layout';
	}

	/**
	 * Get the locale filename of this plugin.
     * @param $locale string
     * @return string|null
	 */
	function getLocaleFilename($locale) {
		return null; // No locale data
	}

	/**
	 * Get the template of this plugin.
     * @param $templateMgr object
     * @return void
	 */
	function activate($templateMgr) {
		$templateMgr->template_dir[0] = Core::getBaseDir() 
										. DIRECTORY_SEPARATOR 
										. 'plugins' 
										. DIRECTORY_SEPARATOR 
										. 'themes' 
										. DIRECTORY_SEPARATOR 
										. 'wizdam' 
										. DIRECTORY_SEPARATOR 
										. 'templates';   
											      
		$templateMgr->compile_id = 'ScholarWizdam';
	}
}

?>