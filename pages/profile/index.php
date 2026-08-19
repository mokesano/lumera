<?php
declare(strict_types=1);

/**
 * @defgroup pages_profile
 */

/**
 * @file pages/profile/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * @brief Halaman terpisah (dedicated) untuk registrasi pengguna baru.
 *
 * @ingroup pages_profile
 */

/** @var string $op */
switch ($op) {
	case 'index':
	case 'profile':
		define('HANDLER_CLASS', 'ProfileHandler');
		import('pages.profile.ProfileHandler');
		break;
}

?>