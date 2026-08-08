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
 */

/** @var string $op */
switch ($op) {
    case 'index':
    case 'search':
        define('HANDLER_CLASS', 'SearchHandler');
        import('pages.search.SearchHandler');
        break;
    case 'authors':
        define('HANDLER_CLASS', 'AuthorSearchHandler');
        import('pages.search.AuthorSearchHandler');
        break;
    case 'titles':
        define('HANDLER_CLASS', 'TitleSearchHandler');
        import('pages.search.TitleSearchHandler');
        break;
    case 'categories':
    case 'category':
        define('HANDLER_CLASS', 'CategorySearchHandler');
        import('pages.search.CategorySearchHandler');
        break;
}

?>