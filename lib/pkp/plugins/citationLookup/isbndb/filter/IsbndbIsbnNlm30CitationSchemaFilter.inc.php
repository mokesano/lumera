<?php
declare(strict_types=1);

/**
 * @file plugins/citationLookup/isbndb/filter/IsbndbIsbnNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IsbndbIsbnNlm30CitationSchemaFilter
 * @ingroup plugins_citationLookup_isbndb_filter
 *
 * @brief Filter that uses the ISBNdb web service to look up
 * an ISBN and create a NLM citation description from the result.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Removed obsolete reference operators (&)
 * - Modernized directory handling (__DIR__)
 */

import('lib.pkp.plugins.citationLookup.isbndb.filter.IsbndbNlm30CitationSchemaFilter');

class IsbndbIsbnNlm30CitationSchemaFilter extends IsbndbNlm30CitationSchemaFilter {
    
    /**
     * Constructor
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('ISBNdb');
        parent::__construct($filterGroup);
    }

    //
    // Implement template methods from PersistableFilter
    //
    
    /**
     * @see PersistableFilter::getClassName()
     */
    public function getClassName(): string {
        return 'lib.pkp.plugins.citationLookup.isbndb.filter.IsbndbIsbnNlm30CitationSchemaFilter';
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
        // [WIZDAM NOTE] Assuming isValidIsbn is defined in parent/trait
        if (!$this->isValidIsbn($input)) {
            return false;
        }
        
        // [WIZDAM FIX] Removed 3rd argument 'true' which is legacy specific.
        // Modern Filter::supports signature usually only accepts input/output.
        return parent::supports($input, $output);
    }

    /**
     * @see Filter::process()
     * @param string $isbn
     * @return MetadataDescription|null
     */
    public function process($isbn) {
        // Ensure input is string (defensive coding for strict type safety if caller is loose)
        if (!is_string($isbn)) {
            return null;
        }

        // Instantiate the web service request
        $lookupParams = [
            'access_key' => $this->getApiKey(),
            'index1'     => 'isbn',
            'results'    => 'details,authors',
            'value1'     => $isbn
        ];

        // Call the web service
        // [WIZDAM FIX] Removed assignment inside condition & reference operator
        $resultDOM = $this->callWebService(ISBNDB_WEBSERVICE_URL, $lookupParams);
        
        if ($resultDOM === null) {
            return null;
        }

        // Transform and pre-process the web service result
        // [WIZDAM FIX] Use __DIR__ and remove reference operator
        $metadata = $this->transformWebServiceResults(
            $resultDOM, 
            __DIR__ . DIRECTORY_SEPARATOR . 'isbndb.xsl'
        );

        if ($metadata === null) {
            return null;
        }

        // Extract place and publisher from the combined entry.
        if (isset($metadata['place-publisher'])) {
            $metadata['publisher-loc'] = PKPString::trimPunctuation(
                PKPString::regexp_replace('/^(.+):.*/', '\1', $metadata['place-publisher'])
            );
            $metadata['publisher-name'] = PKPString::trimPunctuation(
                PKPString::regexp_replace('/.*:([^,]+),?.*/', '\1', $metadata['place-publisher'])
            );
            unset($metadata['place-publisher']);
        }

        // Reformat the publication date
        if (isset($metadata['date'])) {
            $metadata['date'] = PKPString::regexp_replace('/^[^\d{4}]+(\d{4}).*/', '\1', $metadata['date']);
        }

        // Clean non-numerics from ISBN
        $metadata['isbn'] = PKPString::regexp_replace('/[^\dX]*/', '', $isbn);

        // Set the publicationType
        $metadata['[@publication-type]'] = NLM30_PUBLICATION_TYPE_BOOK;

        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
    }
}
?>