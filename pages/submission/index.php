<?php
declare(strict_types=1);

/**
 * @defgroup pages_submission
 */

/**
 * @file pages/submission/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * @ingroup pages_submission
 * @brief Handle requests for article submission -- skema URL yang
 * lebih rasional dan agnostik terhadap peran, dipindahkan dari
 * pages/author/ (lihat SubmitHandler.inc.php untuk detail).
 */

/** @var string $op */
switch ($op) {
	//
	// Article Submission
	//
	case 'submit':
	case 'saveSubmit':
	case 'submitSuppFile':
	case 'saveSubmitSuppFile':
	case 'deleteSubmitSuppFile':
	case 'expediteSubmission':
		define('HANDLER_CLASS', 'SubmitHandler');
		import('pages.submission.SubmitHandler');
		break;
}

?>