<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30PersonStringFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30PersonStringFilter
 * @ingroup plugins_metadata_nlm30_filter
 * @see Nlm30NameSchema
 *
 * @brief Filter that converts from a string
 * to an (array of) NLM name description(s).
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & Removed create_function
 */

import('lib.pkp.classes.filter.Filter');
import('lib.pkp.classes.metadata.MetadataDescription');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30NameSchema');

define('PERSON_STRING_FILTER_MULTIPLE', 0x01);
define('PERSON_STRING_FILTER_SINGLE', 0x02);

define('PERSON_STRING_FILTER_ETAL', 'et-al');

class Nlm30PersonStringFilter extends Filter {
    /** @var int */
    public $_filterMode;

    /**
     * Constructor
     * @param string $inputType
     * @param string $outputType
     * @param int $filterMode one of the PERSON_STRING_FILTER_* constants
     */
    public function __construct($inputType, $outputType, $filterMode = PERSON_STRING_FILTER_SINGLE) {
        $this->_filterMode = (int) $filterMode;
        parent::__construct($inputType, $outputType);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30PersonStringFilter($inputType, $outputType, $filterMode = PERSON_STRING_FILTER_SINGLE) {
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
     * Get the filter mode
     * @return int
     */
    public function getFilterMode() {
        return $this->_filterMode;
    }

    //
    // Protected helper methods
    //
    /**
     * Remove et-al entries from input/output which are valid but do not
     * conform to the canonical transformation type definition.
     * [WIZDAM FIX] Replaced create_function with closure & removed reference return
     * @param mixed $personDescriptions
     * @return mixed false if more than one et-al string was found
     * otherwise the filtered person description list.
     */
    public function removeEtAlEntries($personDescriptions) {
        if ($this->getFilterMode() == PERSON_STRING_FILTER_MULTIPLE && is_array($personDescriptions)) {
            // Remove et-al strings
            // [WIZDAM FIX] create_function removed
            $result = array_filter($personDescriptions, function($pd) {
                return $pd instanceof MetadataDescription;
            });

            // There can be exactly one et-al string
            if (count($result) < count($personDescriptions)-1) {
                return false;
            }
        } else {
            $result = $personDescriptions;
        }

        return $result;
    }
}
?>