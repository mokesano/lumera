<?php
declare(strict_types=1);

/**
 * @defgroup plugins_themes_mpg
 */

/**
 * @file plugins/themes/mpg/index.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_themes_sangia
 * @brief Wrapper for "sangia classical" theme plugin.
 *
 */

require_once('SangiaClas.inc.php');

return new SangiaClas();