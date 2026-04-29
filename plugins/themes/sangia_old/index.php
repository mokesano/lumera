<?php
declare(strict_types=1);

/**
 * @defgroup plugins_themes_publisher Theme Plugin
 */

/**
 * @file plugins/themes/sangia_old/index.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_themes_sangia
 * @brief Wrapper for "sangia" theme plugin.
 *
 */

require_once('SangiaThemePlugin.inc.php');

return new SangiaThemePlugin();