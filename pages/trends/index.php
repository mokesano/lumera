<?php
declare(strict_types=1);

/**
 * @file pages/trends/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @ingroup pages_trends
 * @brief Native Route Registry for 'trends' pages.
 * Menangani URL: /{context}/trends/{op}
 */

/** @var string $op */
switch ($op) {
    case 'index':
    case '':
        // [WIZDAM] - Halaman Hub Utama
        define('HANDLER_CLASS', 'TrendsHandler');
        import('pages.trends.TrendsHandler');
        break;
        
    case 'popular':
        define('HANDLER_CLASS', 'MostPopularHandler');
        import('pages.trends.MostPopularHandler');
        break;
        
    case 'download':
        // Disiapkan untuk AI selanjutnya
        define('HANDLER_CLASS', 'MostDownloadHandler');
        import('pages.trends.MostDownloadHandler');
        break;
        
    case 'cited':
        // Disiapkan untuk AI selanjutnya
        define('HANDLER_CLASS', 'MostCitedHandler');
        import('pages.trends.MostCitedHandler');
        break;
}

?>