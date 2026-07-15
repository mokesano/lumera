<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPDBProfiler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPDBProfiler
 * @ingroup core
 *
 * @brief Wraps DB connection query data for use by PKPProfiler.
 */

class PKPDBProfiler {

    /** @var int property to wrap DB connection query count */
    public $queryCount;

    /** @var array property to store queries */
    public $queries;

    /**
     * Constructor.
     */
    public function __construct() {
        $dbconn = DBConnection::getInstance();

        $this->queryCount = $dbconn->getNumQueries();
        $this->queries = Registry::get('queries', true, array());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPDBProfiler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

}
?>