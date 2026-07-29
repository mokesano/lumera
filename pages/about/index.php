<?php
declare(strict_types=1);

/**
 * @defgroup pages_about
 */
 
/**
 * @file pages/about/index.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_about
 * @brief Handle requests for about the journal functions, DAN about the
 * publisher/site functions -- dipisah tegas ke handler yang berbeda.
 */

/** @var string $op */
switch($op) {
	//
	// About JOURNAL -- konteks jurnal (AboutHandler)
	//
	case 'index':
	case 'contact':
	case 'editorial-team':
	case 'leadership-journal': // Jika suatu saat perlu leadership level jurnal, beda dari level Penerbit
	case 'displayMembership':
	case 'display-membership':
	case 'editorialTeamBio':
	case 'editorial-team-bio':
	case 'editorial-policies':
	case 'subscriptions':
	case 'memberships':
	case 'submissions':
	case 'sponsorship':
	case 'sitemap':
	// case 'journal-history':
	case 'aboutThisPublishingSystem':
	case 'insights':
	case 'statistics':
		define('HANDLER_CLASS', 'AboutHandler');
		import('pages.about.AboutHandler');
		break;

	//
	// About PUBLISHER/SITE -- murni level root, TIDAK PERNAH bercabang jurnal
	//
	// case 'contact':
	case 'mission':
	case 'history':
	case 'leadership':
	case 'award':
		define('HANDLER_CLASS', 'PublisherAboutHandler');
		import('pages.about.PublisherAboutHandler');
		break;
}
?>