<?php
declare(strict_types=1);

/**
 * @defgroup citation_output
 */

/**
 * @file classes/citation/PlainTextReferencesList.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PlainTextReferencesList
 * @ingroup citation
 *
 * @brief Class representing an ordered list of plain text citation output.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

define('REFERENCES_LIST_ORDERING_NUMERICAL', 0x01);
define('REFERENCES_LIST_ORDERING_ALPHABETICAL', 0x02);

class PlainTextReferencesList {
    /** @var int one of the REFERENCES_LIST_ORDERING_* constants */
    protected $_ordering;

    /** @var string the actual list */
    protected $_listContent;

    /**
     * Constructor.
     * @param string $listContent
     * @param int $ordering one of the REFERENCES_LIST_ORDERING_* constants
     */
    public function __construct($listContent, $ordering) {
        // [WIZDAM FIX] Cast to ensure strict type compliance
        $this->_listContent = (string) $listContent;
        $this->_ordering = (int) $ordering;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PlainTextReferencesList() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Getters and Setters
    //
    /**
     * Set the list content
     * @param string $listContent
     */
    public function setListContent($listContent) {
        $this->_listContent = (string) $listContent;
    }

    /**
     * Get the list content
     * @return string
     */
    public function getListContent() {
        return $this->_listContent;
    }

    /**
     * Set the ordering
     * @param int $ordering
     */
    public function setOrdering($ordering) {
        $this->_ordering = (int) $ordering;
    }

    /**
     * Get the ordering
     * @return int
     */
    public function getOrdering() {
        return $this->_ordering;
    }
}
?>