<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationOutput_apa_filter
 */

/**
 * @file plugins/citationOutput/apa/filter/Nlm30CitationSchemaApaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaApaFilter
 * @ingroup plugins_citationOutput_apa_filter
 *
 * @brief Filter that transforms NLM citation metadata descriptions into
 * APA citation output.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Modern Constructor
 * - Explicit Visibility & Type Hints
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaCitationOutputFormatFilter');

class Nlm30CitationSchemaApaFilter extends Nlm30CitationSchemaCitationOutputFormatFilter {
    
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('APA Citation Output');

        parent::__construct($filterGroup);
    }

    //
    // Implement template methods from PersistableFilter
    //
    
    /**
     * @see PersistableFilter::getClassName()
     * @return string
     */
    public function getClassName(): string {
        return 'lib.pkp.plugins.citationOutput.apa.filter.Nlm30CitationSchemaApaFilter';
    }

    //
    // Implement abstract template methods from TemplateBasedFilter
    //
    
    /**
     * @see TemplateBasedFilter::getBasePath()
     * @return string
     */
    public function getBasePath(): string {
        return dirname(__FILE__);
    }
}
?>