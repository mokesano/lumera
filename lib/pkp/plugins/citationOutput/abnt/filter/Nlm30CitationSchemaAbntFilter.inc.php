<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationOutput_abnt_filter
 */

/**
 * @file plugins/citationOutput/abnt/filter/Nlm30CitationSchemaAbntFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaAbntFilter
 * @ingroup plugins_citationOutput_abnt_filter
 *
 * @brief Filter that transforms NLM citation metadata descriptions into
 * ABNT citation output.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Modern Constructor
 * - Explicit Type Hints
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaCitationOutputFormatFilter');

class Nlm30CitationSchemaAbntFilter extends Nlm30CitationSchemaCitationOutputFormatFilter {
    
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('ABNT Citation Output');

        // FIXME: Implement conference proceedings support for ABNT.
        $this->setSupportedPublicationTypes([
            NLM30_PUBLICATION_TYPE_BOOK, 
            NLM30_PUBLICATION_TYPE_JOURNAL
        ]);

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
        return 'lib.pkp.plugins.citationOutput.abnt.filter.Nlm30CitationSchemaAbntFilter';
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