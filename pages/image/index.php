<?php
declare(strict_types=1);

/**
 * @defgroup pages_image
 */
 
/**
 * @file pages/image/index.php
 *
 * @brief Handle dynamic image requests.
 *
 * @ingroup pages_image
 */

switch ($op) {
	case 'issue':   // Akses: .../image/issue/...
	case 'header':  // Akses: .../image/header/...
	case 'article': // Akses: .../image/article/...
		define('HANDLER_CLASS', 'ImageHandler');
		import('pages.image.ImageHandler');
		break;
}

?>