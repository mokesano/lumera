<?php
declare(strict_types=1);

/**
 * @defgroup announcement
 */

/**
 * @file classes/announcement/Announcement.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Announcement
 * @ingroup announcement
 * @see AnnouncementDAO
 *
 * @brief Basic class describing a announcement.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor)
 * - Strict SHIM
 */

import('lib.pkp.classes.announcement.PKPAnnouncement');

class Announcement extends PKPAnnouncement {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Announcement() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class Announcement uses deprecated constructor parent::Announcement(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }
}

?>