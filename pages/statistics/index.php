<?php
declare(strict_types=1);

/**
 * @file pages/statistics/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LoginHandler
 * @ingroup pages_login
 * 
 * @brief URL Router untuk modul statistics ScholarWizdam
 */

switch ($op) {
    case 'index':
    case 'journal':
        define('HANDLER_CLASS', 'JournalStatsHandler');
        import('pages.statistics.JournalStatsHandler');
        break;
}
?>