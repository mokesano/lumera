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
 * Distributed under the GNU GPL v3.
 *
 * @ingroup plugins_publisherId
 * @brief Wrapper for Publisher Id plugin.
 *
 */
 
require_once('PublisherIdPlugin.inc.php');

return new PublisherIdPlugin();