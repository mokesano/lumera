<?php
declare(strict_types=1);

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
 */

// Process types
define('PROCESS_TYPE_CITATION_CHECKING', 0x01);

import('lib.pkp.classes.core.DataObject');

class Process extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function Process() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Setters and Getters
    //

    /**
     * Set the process type.
     * @param int $processType
     * @return void
     */
    public function setProcessType($processType) {
        $this->setData('processType', (int) $processType);
    }

    /**
     * Get the process type.
     * @return int
     */
    public function getProcessType() {
        return (int) $this->getData('processType');
    }

    /**
     * Set the starting time of the process.
     * @param int $timeStarted Unix timestamp
     * @return void
     */
    public function setTimeStarted($timeStarted) {
        $this->setData('timeStarted', (int) $timeStarted);
    }

    /**
     * Get the starting time of the process.
     * @return int Unix timestamp
     */
    public function getTimeStarted() {
        return (int) $this->getData('timeStarted');
    }

    /**
     * Set the one-time-key usage flag.
     * @param bool $obliterated
     * @return void
     */
    public function setObliterated($obliterated) {
        $this->setData('obliterated', (bool) $obliterated);
    }

    /**
     * Get the one-time-key usage flag.
     * @return bool
     */
    public function getObliterated() {
        return (bool) $this->getData('obliterated');
    }
    
}
?>