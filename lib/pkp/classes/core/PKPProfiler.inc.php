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
 * @brief Basic shell class used to wrap the PHP Quick Profiler Class
 * WIZDAM EDITION: PHP 8 Compatibility
 */

require_once('./lib/pkp/lib/pqp/classes/PhpQuickProfiler.php');
require_once('./lib/pkp/lib/pqp/classes/Console.php');

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
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPProfiler(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Gather information to be used to display profiling
     * @return array of stored profiling information
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

class PKPDBProfiler {

    /** @var int property to wrap DB connection query count */
    public $queryCount;

    /** @var array property to store queries (PHP 8 require explicit declaration) */
    public $queries;

    /**
     * Constructor.
     */
    public function __construct() {
        // PHP 8: Objects are passed by handle, reference & removed
        $dbconn = DBConnection::getInstance();

        $this->queryCount = $dbconn->getNumQueries();
        $this->queries = Registry::get('queries', true, array());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPDBProfiler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPDBProfiler(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }
}

?>