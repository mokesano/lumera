<?php
declare(strict_types=1);

/**
 * @file classes/announcement/AnnouncementTypeDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeDAO
 * @ingroup announcement
 * @see AnnouncementType
 *
 * @brief Operations for retrieving and modifying AnnouncementType objects.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor)
 * - Strict SHIM
 * - Visibility explicit
 */

import('classes.announcement.AnnouncementType');
import('lib.pkp.classes.announcement.PKPAnnouncementTypeDAO');

class AnnouncementTypeDAO extends PKPAnnouncementTypeDAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementTypeDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class AnnouncementTypeDAO uses deprecated constructor parent::AnnouncementTypeDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * @see PKPAnnouncementTypeDAO::newDataObject
     * @return AnnouncementType
     */
    public function newDataObject() {
        return new AnnouncementType();
    }
}

?>