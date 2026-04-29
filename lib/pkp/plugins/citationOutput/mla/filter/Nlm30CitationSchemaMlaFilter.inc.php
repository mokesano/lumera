<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationOutput_mla_filter
 */

/**
 * @file plugins/citationOutput/mla/filter/Nlm30CitationSchemaMlaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaMlaFilter
 * @ingroup plugins_citationOutput_mla_filter
 *
 * @brief Filter that transforms NLM citation metadata descriptions into
 * MLA citation output.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Modern Constructor
 * - Explicit Visibility & Type Hints
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaCitationOutputFormatFilter');

class Nlm30CitationSchemaMlaFilter extends Nlm30CitationSchemaCitationOutputFormatFilter {
    
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('MLA Citation Output');

        parent::__construct($filterGroup);
    }


    //
    // Implement template methods from PersistableFilter
    //
    
    /**
     * @see PersistableFilter::getClassName()
     * @return string the class name of this filter
     */
    public function getClassName(): string {
        return 'lib.pkp.plugins.citationOutput.mla.filter.Nlm30CitationSchemaMlaFilter';
    }


    //
    // Implement abstract template methods from TemplateBasedFilter
    //
    
    /**
     * @see TemplateBasedFilter::getBasePath()
     * @return string the base path for templates
     */
    public function getBasePath(): string {
        return dirname(__FILE__);
    }
}
?>