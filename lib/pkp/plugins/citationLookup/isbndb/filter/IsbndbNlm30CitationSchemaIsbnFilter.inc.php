<?php
declare(strict_types=1);

/**
 * @file plugins/citationLookup/isbndb/filter/IsbndbNlm30CitationSchemaIsbnFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IsbndbNlm30CitationSchemaIsbnFilter
 * @ingroup plugins_citationLookup_isbndb_filter
 *
 * @brief Filter that uses the ISBNdb web service to identify an ISBN for a given citation.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - DOM Handling modernized (instanceof)
 * - Removed obsolete reference operators
 */

import('lib.pkp.plugins.citationLookup.isbndb.filter.IsbndbNlm30CitationSchemaFilter');

class IsbndbNlm30CitationSchemaIsbnFilter extends IsbndbNlm30CitationSchemaFilter {
    
    /**
     * Constructor
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('ISBNdb (from NLM)');
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
        return 'lib.pkp.plugins.citationLookup.isbndb.filter.IsbndbNlm30CitationSchemaIsbnFilter';
    }

    //
    // Implement template methods from Filter
    //
    
    /**
     * @see Filter::supports()
     * @param mixed $input
     * @param mixed $output
     */
    public function supports($input, $output): bool {
        // [WIZDAM NOTE] Modern boolean logic simplification
        if ($output !== null && !$this->isValidIsbn($output)) {
            return false;
        }
        // [WIZDAM FIX] Removing extra legacy arguments
        return parent::supports($input, $output);
    }

    /**
     * @see Filter::process()
     * @param MetadataDescription $citationDescription
     * @return string|null an ISBN or null
     */
    public function process($citationDescription) {
        // Get the search strings
        $searchTemplates = $this->_getSearchTemplates();
        $searchStrings = $this->constructSearchStrings($searchTemplates, $citationDescription);

        // Run the searches, in order, until we have a result
        $searchParams = [
            'access_key' => $this->getApiKey(),
            'index1'     => 'combined'
        ];

        $resultDOM = null;
        $found = false;

        foreach ($searchStrings as $searchString) {
            $searchParams['value1'] = $searchString;
            $resultDOM = $this->callWebService(ISBNDB_WEBSERVICE_URL, $searchParams);

            if ($resultDOM === null) {
                return null;
            }

            // Did we get a search hit?
            $numResults = '';
            $bookList = $resultDOM->getElementsByTagName('BookList');

            if ($bookList instanceof DOMNodeList && $bookList->length > 0) {
                $bookListFirstItem = $bookList->item(0);
                if ($bookListFirstItem instanceof DOMElement) {
                    $numResults = $bookListFirstItem->getAttribute('total_results');
                }
            }

            if (!empty($numResults)) {
                $found = true;
                break;
            }
        }

        if (!$found || $resultDOM === null) {
            return null;
        }

        // Retrieve the first search hit
        $bookDataNodes = $resultDOM->getElementsByTagName('BookData');
        $bookDataFirstNode = null;

        if ($bookDataNodes instanceof DOMNodeList && $bookDataNodes->length > 0) {
            $bookDataFirstNode = $bookDataNodes->item(0);
        }

        // If no book data present, then abort
        if (!$bookDataFirstNode instanceof DOMElement) {
            return null;
        }

        $isbn = $bookDataFirstNode->getAttribute('isbn13');

        // If we have no ISBN then abort
        if (empty($isbn)) {
            return null;
        }

        return $isbn;
    }

    //
    // Private methods
    //
    
    /**
     * Return an array of search templates.
     * @return array
     */
    private function _getSearchTemplates(): array {
        return [
            '%au% %title% %date%',
            '%aulast% %title% %date%',
            '%au% %title% c%date%',
            '%aulast% %title% c%date%',
            '%au% %title%',
            '%aulast% %title%',
            '%title% %date%',
            '%title% c%date%',
            '%au% %date%',
            '%aulast% %date%',
            '%au% c%date%',
            '%aulast% c%date%'
        ];
    }
}
?>