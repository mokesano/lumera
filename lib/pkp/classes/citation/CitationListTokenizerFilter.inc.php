<?php
declare(strict_types=1);

/**
 * @file classes/citation/CitationListTokenizerFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CitationListTokenizerFilter
 * @ingroup classes_citation
 *
 * @brief Class that takes an unformatted list of citations
 * and returns an array of raw citation strings.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.filter.Filter');

class CitationListTokenizerFilter extends Filter {
    /**
     * Constructor
     */
    public function __construct() {
        $this->setDisplayName('Split a reference list into separate citations');

        parent::__construct('primitive::string', 'primitive::string[]');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CitationListTokenizerFilter() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::process()
     * @param string|mixed $input
     * @return array
     */
    public function process($input) {
        // [WIZDAM FIX] Ensure input is string to prevent Fatal Error on trim(null)
        $input = (string) $input;

        // The default implementation assumes that raw citations are
        // separated with line endings.
        
        // 1) Remove empty lines and normalize line endings.
        $input = PKPString::regexp_replace('/[\r\n]+/s', "\n", $input);
        
        // 2) Remove trailing/leading line breaks.
        $input = trim($input, "\n");
        
        // 3) Break up at line endings.
        if (empty($input)) {
            $citations = [];
        } else {
            $citations = explode("\n", $input);
        }
        
        // 4) Remove numbers from the beginning of each citation.
        foreach($citations as $index => $citation) {
            $citations[$index] = PKPString::regexp_replace('/^\s*[\[#]?[0-9]+[.)\]]?\s*/', '', $citation);
        }

        return $citations;
    }
}
?>