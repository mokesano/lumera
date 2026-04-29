<?php
declare(strict_types=1);

/**
 * @defgroup plugins_publisherId
 */

/**
 * @file plugins/pubIds/publisherId/index.php
 *
 * Copyright (c) 2017-Current Sangia Publishing
 * Copyright (c) 2017-Current Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_publisherId
 * @brief Wrapper for Wizdam Publisher Id plugin.
 *
 */
 
require_once('PublisherIdPlugin.inc.php');

return new PublisherIdPlugin();