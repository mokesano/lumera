<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationLookup_isbndb_filter
 */

/**
 * @file plugins/citationLookup/isbndb/filter/IsbndbNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IsbndbNlm30CitationSchemaFilter
 * @ingroup plugins_citationLookup_isbndb_filter
 *
 * @brief Abstract filter that wraps the ISBNdb web service.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Typed Properties & Methods
 * - Cleaned up constructor logic
 */

if (!defined('ISBNDB_WEBSERVICE_URL')) {
    define('ISBNDB_WEBSERVICE_URL', 'http://isbndb.com/api/books.xml');
}

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaFilter');
import('lib.pkp.classes.filter.FilterSetting');

class IsbndbNlm30CitationSchemaFilter extends Nlm30CitationSchemaFilter {
    
    /**
     * Constructor
     */
    public function __construct(FilterGroup $filterGroup) {
        // Instantiate the settings of this filter
        $apiKeySetting = new FilterSetting(
            'apiKey',
            'metadata.filters.isbndb.settings.apiKey.displayName',
            'metadata.filters.isbndb.settings.apiKey.validationMessage'
        );
        $this->addSetting($apiKeySetting);

        parent::__construct($filterGroup, [NLM30_PUBLICATION_TYPE_BOOK]);
    }

    //
    // Getters and Setters
    //
    
    /**
     * Get the apiKey
     * @return string
     */
    public function getApiKey(): string {
        return (string) $this->getData('apiKey');
    }

    //
    // Protected helper methods
    //
    
    /**
     * Checks whether the given string is an ISBN.
     * [WIZDAM NOTE] Using mixed input to safely handle non-string dirty inputs 
     * by returning false instead of throwing TypeError.
     * @param mixed $isbn
     */
    protected function isValidIsbn($isbn): bool {
        return is_string($isbn) 
            && is_numeric($isbn) 
            && PKPString::strlen($isbn) === 13;
    }
}
?>