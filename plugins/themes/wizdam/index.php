<?php
declare(strict_types=1);

/**
 * @defgroup plugins_themes_stipwunaraha
 */

/**
 * @file plugins/themes/wizdam/index.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_themes_mpg
 * @brief Wrapper for "wizdam" theme plugin.
 *
 */

require_once('ScholarWizdam.inc.php');

return new ScholarWizdam();