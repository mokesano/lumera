<?php
declare(strict_types=1);

/**
 * @defgroup pages_register
 */

/**
 * @file pages/register/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * @brief Halaman terpisah (dedicated) untuk registrasi pengguna baru.
 *
 * [WIZDAM] Dipindahkan dari page="user" op="register" menjadi page="register"
 * tersendiri -- mengikuti pola pages/login (page sendiri, bare path memetakan
 * ke op=index). RegistrationHandler.inc.php TETAP berada di pages/user/
 * (kelasnya tidak dipindah, hanya rute URL-nya) dan diimpor lintas
 * direktori, pola yang sudah lazim dipakai di codebase ini.
 *
 * TIDAK ADA redirect balik dari user/register lama (sesuai keputusan:
 * ganti total, fokus tema sangiapub, tema lain menyesuaikan manual).
 *
 * @ingroup pages_register
 */

/** @var string $op */
switch ($op) {
	case 'index':
	case 'register':
	case 'registerUser':
	case 'activateUser':
		define('HANDLER_CLASS', 'RegistrationHandler');
		import('pages.register.RegistrationHandler');
		break;
}

?>