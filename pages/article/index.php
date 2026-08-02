<?php
declare(strict_types=1);

/**
 * @defgroup pages_article
 */
 
/**
 * @file pages/article/index.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_article
 * @brief Handle requests for article functions. 
 *
 */

/** @var string $op */
switch ($op) {
	//
	// Aticle View
	//
	case 'view':
	case 'pii':
	case 'viewPDFInterstitial':
	case 'viewDownloadInterstitial':
	case 'viewArticle':
	case 'viewRST':
	case 'viewFile':
	case 'download':
	case 'downloadSuppFile':
		define('HANDLER_CLASS', 'ArticleHandler');
		import('pages.article.ArticleHandler');
		break;
	//
	// Article Metrics
	//
	case 'metrics':
		define('HANDLER_CLASS', 'ArticleMetricsHandler');
		import('pages.article.ArticleMetricsHandler');
		break;
}

?>