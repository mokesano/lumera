<?php
declare(strict_types=1);

/**
 * @file pages/trends/index.php
 *
 * [WIZDAM] - Native Route Registry for 'trends' pages.
 * Menangani URL: /{context}/trends/{op}
 */

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