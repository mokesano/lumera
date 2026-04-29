<?php
declare(strict_types=1);

/**
 * @file plugins/themes/uncommon/UncommonThemePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UncommonThemePlugin
 * @ingroup plugins_themes_uncommon
 *
 * @brief "Uncommon" theme plugin
 */

import('classes.plugins.ThemePlugin');

class UncommonThemePlugin extends ThemePlugin {
    
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName(): string {
		return 'UncommonThemePlugin';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName(): string {
		return 'Uncommon Theme';
	}

	/**
	 * Get the description of this plugin.
	 * @return String
	 */
	function getDescription(): string {
		return 'Chunky, blue, solid layout';
	}

	/**
	 * Get the style sheet filename of this plugin.
	 */
	function getStylesheetFilename() {
		return 'uncommon.css';
	}
	
	/**
	 * Get the locale filename of this plugin.
	 */
	function getLocaleFilename($locale) {
		return null; // No locale data
	}
}

?>