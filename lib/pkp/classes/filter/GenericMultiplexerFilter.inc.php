<?php
declare(strict_types=1);

/**
 * @file classes/filter/GenericMultiplexerFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GenericMultiplexerFilter
 * @ingroup filter
 *
 * @brief A generic filter that is configured with a number of
 * equal type filters. It takes the input argument, applies all
 * given filters to it and returns an array of outputs as a result.
 *
 * The result can then be sent to either an iterator filter or
 * to a de-multiplexer filter.
 */

import('lib.pkp.classes.filter.CompositeFilter');

class GenericMultiplexerFilter extends CompositeFilter {
    /**
     * @var boolean whether some sub-filters can fail as long as at least one
     * filter returns a result.
     */
    protected $_tolerateFailures = false;

    /**
     * Constructor
     * @param $filterGroup FilterGroup
     * @param $displayName string
     */
    public function __construct($filterGroup, $displayName = null) {
        // Menghilangkan reference (&) pada parameter $filterGroup
        parent::__construct($filterGroup, $displayName);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $filterGroup FilterGroup
     * @param $displayName string
     */
    public function GenericMultiplexerFilter($filterGroup, $displayName = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::GenericMultiplexerFilter(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($filterGroup, $displayName);
    }


    //
    // Setters and Getters
    //
    /**
     * Set to true if sub-filters can fail as long as
     * at least one filter returns a result.
     * @param $tolerateFailures boolean
     */
    public function setTolerateFailures($tolerateFailures) {
        $this->_tolerateFailures = $tolerateFailures;
    }

    /**
     * Returns true when sub-filters can fail as long
     * as at least one filter returns a result.
     * @return boolean
     */
    public function getTolerateFailures() {
        return $this->_tolerateFailures;
    }


    //
    // Implementing abstract template methods from PersistentFilter
    //
    /**
     * @see PersistentFilter::getClassName()
     */
    public function getClassName() {
        return 'lib.pkp.classes.filter.GenericMultiplexerFilter';
    }


    //
    // Implementing abstract template methods from Filter
    //
    /**
     * @see Filter::process()
     * @param $input mixed
     * @return array
     */
    public function process($input) {
        // Menghilangkan reference (&) pada return dan parameter $input
        
        // Iterate over all filters and return the results
        // as an array.
        $output = array();
        foreach($this->getFilters() as $filter) {
            // Make a copy of the input so that the filters don't interfere
            // with each other.
            if (is_object($input)) {
                // Menghilangkan reference (&) pada penugasan
                $clonedInput = cloneObject($input);
            } else {
                $clonedInput = $input;
            }

            // Execute the filter
            // Menghilangkan reference (&) pada penugasan
            $intermediateOutput = $filter->execute($clonedInput);

            // Propagate errors of sub-filters (if any)
            foreach($filter->getErrors() as $errorMessage) $this->addError($errorMessage);

            // Handle sub-filter failure.
            if (is_null($intermediateOutput)) { // Sintaks if/else diperbaiki.
                if ($this->getTolerateFailures()) {
                    continue;
                } else {
                    // No need to go on as the filter will fail
                    // anyway out output validation so we better
                    // safe time and return immediately.
                    $output = null;
                    break;
                }
            } else {
                // Add the output to the output array.
                // Menghilangkan reference (&) pada penambahan array
                $output[] = $intermediateOutput;
            }
            unset($clonedInput, $intermediateOutput);
        }

        // Fail in any case if all sub-filters failed.
        if (empty($output)) $output = null;

        return $output;
    }
}
?>