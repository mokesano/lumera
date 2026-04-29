<?php
declare(strict_types=1);

/**
 * @defgroup process
 */

/**
 * @file classes/process/Process.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Process
 * @ingroup process
 * @see ProcessDAO
 *
 * @brief A class representing a running process.
 * REFACTORED: Wizdam Edition (PHP 8 Constructor, Visibility)
 */

// Process types
define('PROCESS_TYPE_CITATION_CHECKING', 0x01);

import('lib.pkp.classes.core.DataObject');

class Process extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Process() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Process(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Setters and Getters
    //
    /**
     * Set the process type
     * @param $processType integer
     */
    public function setProcessType($processType) {
        $this->setData('processType', (integer)$processType);
    }

    /**
     * Get the process type
     * @return integer
     */
    public function getProcessType() {
        return $this->getData('processType');
    }

    /**
     * Set the starting time of the process
     * @param $timeStarted integer unix timestamp
     */
    public function setTimeStarted($timeStarted) {
        $this->setData('timeStarted', (integer)$timeStarted);
    }

    /**
     * Get the starting time of the process
     * @return integer unix timestamp
     */
    public function getTimeStarted() {
        return $this->getData('timeStarted');
    }

    /**
     * Set the one-time-key usage flag
     * @param $obliterated boolean
     */
    public function setObliterated($obliterated) {
        $this->setData('obliterated', (boolean)$obliterated);
    }

    /**
     * Get the one-time-key usage flag
     * @return boolean
     */
    public function getObliterated() {
        return $this->getData('obliterated');
    }
}

?>