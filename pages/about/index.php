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
 * 
 * @brief Handle requests for about the journal functions, DAN about the
 * publisher/site functions -- dipisah tegas ke handler yang berbeda.
 */

/** @var string $op */
switch($op) {
	//
	// About JOURNAL Context
	//
	// Publisher/Journal Context
	case 'index':
	case 'sitemap':
		define('HANDLER_CLASS', 'AboutHandler');
		import('pages.about.AboutHandler');
		break;

	// Contact Journal
	case 'contact-editorial-office':
	// Editorial & Policies
	case 'editorial-team':
	case 'displayMembership':
	case 'display-membership':
	case 'editorialTeamBio':
	case 'editorial-team-bio':
	case 'editorial-policies':
	// Journal Services
	case 'subscriptions':
	case 'memberships':
	case 'submissions':
	case 'sponsorship':
	// Impact & Progress
	case 'journal-history':
	case 'insights':
	case 'statistics':
		define('HANDLER_CLASS', 'AboutJournalHandler');
		import('pages.about.AboutJournalHandler');
		break;

	//
	// About PUBLISHER/SITE Context
	//
	// Who We Are (Publisher Identity)
	case 'mission':
	case 'history':
	case 'leadership':
	case 'awards':
	case 'crossmark-policy':
	case 'contact':
		define('HANDLER_CLASS', 'AboutPublisherHandler');
		import('pages.about.AboutPublisherHandler');
		break;

	// Impact & Report
	case 'impact':
	case 'annual-report':
	// Publishing Model
	case 'how-we-publish':
	case 'open-access':
	case 'research-integrity':
	case 'quality-assurance':
	case 'research-topics':
	case 'fair-data':
	case 'fee-policy':
	// Services
	case 'partnerships':
	case 'collaborators':
	case 'contributors':
	case 'agreements':
	case 'sustainability':
	// Legal
	case 'term-of-use':
	case 'privacy-statement':
	case 'cookies':
	case 'press-office':
	case 'careers':
		$targetUrl = Application::get()->getRequest()->url(null, 'about', 'index');
		header("Location: $targetUrl", true, 302);
		exit();
}

?>