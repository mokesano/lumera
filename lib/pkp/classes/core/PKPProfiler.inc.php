<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPProfiler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPProfiler
 * @ingroup core
 *
 * @brief Basic shell class used to wrap the PHP Quick Profiler Class.
 */

require_once('./lib/pkp/lib/pqp/classes/PhpQuickProfiler.php');
require_once('./lib/pkp/lib/pqp/classes/Console.php');

import('lib.pkp.classes.core.PKPDBProfiler');

class PKPProfiler {

    /** @var object instance of the PQP profiler */
    public $profiler;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPProfiler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Gather information to be used to display profiling
     * @return array
     */
    public function getData() {
        $profiler = $this->profiler;
        $profiler->db = new PKPDBProfiler();

        $profiler->gatherConsoleData();
        $profiler->gatherFileData();
        $profiler->gatherMemoryData();
        $profiler->gatherQueryData();
        $profiler->gatherSpeedData();

        return $profiler->output;
    }

}
?>