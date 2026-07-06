<?php
declare(strict_types=1);

/**
 * @defgroup plugins_themes_classical Classical Theme
 */

/**
 * @file plugins/themes/classical/index.php
 *
 * Copyright (c) 2017-2026 Codecanau, LLC and CodeLumera, LLC
 * Copyright (c) 2017-2026 Rochmady and Teams
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_themes_classical
 * @brief Wrapper for "classical" theme plugin.
 *
 */

require_once('Classical.inc.php');

return new Classical();