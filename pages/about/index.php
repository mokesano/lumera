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
	// About JOURNAL Context
	//
	case 'index':
	case 'contact':
	case 'sitemap':
	//
	// Editorial & Policies
	// 
	case 'editorial-team':
	case 'displayMembership':
	case 'display-membership':
	case 'editorialTeamBio':
	case 'editorial-team-bio':
	case 'editorial-policies':
	//
	// Journal Services
	//
	case 'subscriptions':
	case 'memberships':
	case 'submissions':
	case 'sponsorship':
	case 'aboutThisPublishingSystem': // SHIM for legacy URL
	//
	// Impact & Progress
	//
	case 'journal-history':
	case 'insights':
	case 'statistics':
		define('HANDLER_CLASS', 'AboutHandler');
		import('pages.about.AboutHandler');
		break;


	//
	// About PUBLISHER/SITE Context
	//

	//
	// Who We Are (Publisher Identity)
	//
	case 'mission':
	case 'history':
	case 'leadership':
	case 'awards':
		define('HANDLER_CLASS', 'PublisherHandler');
		import('pages.about.PublisherHandler');
		break;
	//
	// Contact & Impact
	//
	case 'contacts';
	case 'sitemap':
	case 'impact':
	case 'annual-report':
		define('HANDLER_CLASS', 'PublisherAboutHandler');
		import('pages.about.PublisherAboutHandler');
		break;
	//
	// Publishing Model
	//
	case 'how-we-publish':
	case 'open-access':
	case 'research-integrity':
	case 'quality-assurance':
	case 'research-topics':
	case 'fair-data':
	case 'fee-policy':
		define('HANDLER_CLASS', 'PublisherModelHandler');
		import('pages.about.PublisherAboutHandler');
		break;
	//
	// Services
	//
	case 'partnerships':
	case 'collaborators':
	case 'contributors':
	case 'agreements':
	case 'sustainability':
		define('HANDLER_CLASS', 'PublisherServiceHandler');
		import('pages.about.PublisherServiceHandler');
		break;

	//
	// Legal
	//
	case 'term-of-use':
	case 'privacy-statement':
	case 'cookies':
	case 'press-office':
	case 'careers':
		define('HANDLER_CLASS', 'PublisherLegalHandler');
		import('pages.about.PublisherLegalHandler');
		break;
}

?>