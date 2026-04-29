<?php
declare(strict_types=1);

/**
 * @defgroup plugins_themes_sangiapub
 */

/**
 * @file plugins/themes/sangiapub/index.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_themes_sangiapub
 * @brief Wrapper for "SangiaPub" theme plugin.
 *
 */

require_once('SangiaPub.inc.php');

return new SangiaPub();