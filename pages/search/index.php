<?php
declare(strict_types=1);

/**
 * @defgroup pages_search
 */

/**
 * @file pages/search/index.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_search
 * @brief Handle search requests.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

switch ($op) {
    case 'index':
    case 'search':
    case 'authors':
    case 'titles':
    case 'categories':
    case 'category':
        define('HANDLER_CLASS', 'SearchHandler');
        import('pages.search.SearchHandler');
        break;
}

?>