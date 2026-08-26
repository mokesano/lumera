<?php
declare(strict_types=1);

/**
 * @defgroup pages_image
 */
 
/**
 * @file pages/image/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @brief Handle dynamic image requests.
 *
 * @ingroup pages_image
 */

/** @var string $op */
switch ($op) {
	case 'issue':   // Akses: .../image/issue/...
	case 'header':  // Akses: .../image/header/...
	case 'article': // Akses: .../image/article/...
		define('HANDLER_CLASS', 'ImageHandler');
		import('pages.image.ImageHandler');
		break;
}

?>