<?php
declare(strict_types=1);
/**
 * @file pages/volumes/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class VolumesHandler
 * @ingroup pages_volumes
 *
 * @brief Handle requests for volume functions.
 *
 * ROUTING MAP:
 *   displayArchive  → /volumes/                  (Rule 3 PageRouter)
 *   view            → /volumes/{vol}             (Rule 2 PageRouter)
 *                    /volumes/{vol}/issue/{slug} (Rule 1 fallback jika issue null)
 *                     /volumes/{vol}/issue/      (Rule 1b PageRouter)
 *   year            → /year/{year}               (Rule 4 PageRouter – Level 3 Degradasi)
 */

/** @var string $op */
switch ($op) {
    case 'displayArchive':
    case 'view':
    case 'year':
        define('HANDLER_CLASS', 'VolumesHandler');
        import('pages.volumes.VolumesHandler');
        break;
}

?>