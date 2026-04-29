<?php
declare(strict_types=1);

/**
 * @defgroup rt_ojs
 */

/**
 * @file classes/rt/ojs/JournalRT.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalRT
 * @ingroup rt_ojs
 *
 * @brief OJS-specific Reading Tools end-user interface.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.rt.RT');
import('classes.rt.ojs.RTDAO');

class JournalRT extends RT {
    
    /** @var int */
    public $journalId;
    
    /** @var bool */
    public $enabled;

    /**
     * Constructor
     * @param int $journalId
     */
    public function __construct($journalId) {
        $this->setJournalId((int)$journalId);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalRT($journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::JournalRT(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($journalId);
    }

    // Getter/setter methods

    /**
     * Get the journal ID.
     * @return int
     */
    public function getJournalId() {
        return $this->journalId;
    }

    /**
     * Set the journal ID.
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->journalId = (int) $journalId;
    }
}
?>