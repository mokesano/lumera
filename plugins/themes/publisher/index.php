<?php
declare(strict_types=1);

/**
 * @defgroup plugins_themes_publisher
 */

/**
 * @file plugins/themes/publisher/index.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_themes_publisher
 * @brief Wrapper for "publisher" theme plugin.
 *
 */

require_once('PublisherThemePlugin.inc.php');

return new PublisherThemePlugin();