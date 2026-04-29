<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationLookup_worldcat_filter
 */

/**
 * @file plugins/citationLookup/worldcat/filter/WorldcatNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorldcatNlm30CitationSchemaFilter
 * @ingroup plugins_citationLookup_worldcat_filter
 * @see CitationMangager
 *
 * @brief Citation lookup filter that uses the OCLC Worldcat Search API
 * and xISBN services to search for book citation metadata.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Fixed typo 'ibsn' -> 'isbn'
 * - Modernized DOM handling
 * - Removed reference operators
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaFilter');
import('lib.pkp.classes.filter.FilterSetting');
import('lib.pkp.classes.metadata.MetadataDescription');

// Define constants safely
if (!defined('WORLDCAT_WEBSERVICE_SEARCH')) {
    define('WORLDCAT_WEBSERVICE_SEARCH', 'http://www.worldcat.org/search');
}
if (!defined('WORLDCAT_WEBSERVICE_OCLC')) {
    define('WORLDCAT_WEBSERVICE_OCLC', 'http://xisbn.worldcat.org/webservices/xid/oclcnum/');
}
// Lookup in MARCXML which has better granularity than Dublin Core
if (!defined('WORLDCAT_WEBSERVICE_EXTRACT')) {
    define('WORLDCAT_WEBSERVICE_EXTRACT', 'http://www.worldcat.org/webservices/catalog/content/');
}
if (!defined('WORLDCAT_WEBSERVICE_XISBN')) {
    define('WORLDCAT_WEBSERVICE_XISBN', 'http://xisbn.worldcat.org/webservices/xid/isbn/');
}

class WorldcatNlm30CitationSchemaFilter extends Nlm30CitationSchemaFilter {
    
    /**
     * Constructor
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('WorldCat');

        // Instantiate the settings of this filter
        $apiKeySetting = new FilterSetting(
            'apiKey',
            'metadata.filters.worldcat.settings.apiKey.displayName',
            'metadata.filters.worldcat.settings.apiKey.validationMessage',
            FORM_VALIDATOR_OPTIONAL_VALUE
        );
        $this->addSetting($apiKeySetting);

        parent::__construct($filterGroup, [NLM30_PUBLICATION_TYPE_BOOK]);
    }

    //
    // Getters and Setters
    //
    
    /**
     * Get the apiKey
     * @return string|null
     */
    public function getApiKey(): ?string {
        return $this->getData('apiKey');
    }

    //
    // Implement template methods from PersistableFilter
    //
    
    /**
     * @see PersistableFilter::getClassName()
     * @return string
     */
    public function getClassName(): string {
        return 'lib.pkp.plugins.citationLookup.worldcat.filter.WorldcatNlm30CitationSchemaFilter';
    }

    //
    // Implement template methods from Filter
    //
    
    /**
     * @see Filter::process()
     * @param MetadataDescription $citationDescription
     * @return MetadataDescription|null
     */
    public function process($citationDescription) {
        // Get the search strings
        $searchTemplates = $this->_getSearchTemplates();
        $searchStrings = $this->constructSearchStrings($searchTemplates, $citationDescription);

        // Run the searches, in order, until we have a result
        $searchParams = ['qt' => 'worldcat_org_all'];
        $oclcMatches = [];

        foreach ($searchStrings as $searchString) {
            $searchParams['q'] = $searchString;
            
            // Worldcat Web search; results are (mal-formed) XHTML
            // [WIZDAM NOTE] Using XSL_TRANSFORMER_DOCTYPE_STRING to handle loose HTML
            $result = $this->callWebService(WORLDCAT_WEBSERVICE_SEARCH, $searchParams, XSL_TRANSFORMER_DOCTYPE_STRING);

            if ($result === null) {
                return null;
            }

            // parse the OCLC numbers from search results
            PKPString::regexp_match_all('/id="itemid_(\d+)"/', $result, $matches);
            if (!empty($matches[1])) {
                $oclcMatches = $matches[1];
                break;
            }
        }

        // If we don't have an OCLC number, then we cannot get any metadata
        if (empty($oclcMatches)) {
            return null;
        }

        // use xISBN because it's free
        $isbns = null;
        foreach ($oclcMatches as $oclcId) {
            $isbns = $this->_oclcToIsbns($oclcId);
            if (is_array($isbns)) {
                break;
            }
        }

        if ($isbns === null) {
            return null;
        }

        $apiKey = $this->getApiKey();
        
        // Scenario 1: No API Key, use xISBN fallback (First ISBN)
        if (empty($apiKey)) {
            if (!empty($isbns[0])) {
                return $this->_lookupXIsbn($isbns[0]);
            }
            return null;
        } 
        
        // Scenario 2: API Key exists, try Worldcat Lookup
        if (!empty($oclcMatches[0])) {
            $citationDescription = $this->_lookupWorldcat($oclcMatches[0]);
            
            if ($citationDescription === null) {
                return null;
            }

            // Prefer ISBN from xISBN if possible
            if (!empty($isbns[0])) {
                // [WIZDAM FIX] Typo 'ibsn' -> 'isbn'
                $citationDescription->addStatement('isbn', $isbns[0], null, true);
            }
            return $citationDescription;
        }

        return null;
    }

    //
    // Private methods
    //
    
    /**
     * Take an OCLC number and return the associated ISBNs as an array
     * @return array|null an array of ISBNs or null if none found
     */
    private function _oclcToIsbns(string $oclcId): ?array {
        $lookupParams = [
            'method' => 'getMetadata',
            'format' => 'xml',
            'fl'     => '*'
        ];
        
        $resultDOM = $this->callWebService(WORLDCAT_WEBSERVICE_OCLC . urlencode($oclcId), $lookupParams);
        
        if ($resultDOM === null) {
            return null;
        }

        // Extract ISBN from response
        $oclcnumNodes = $resultDOM->getElementsByTagName('oclcnum');
        
        if ($oclcnumNodes->length > 0) {
            $oclcnumFirstNode = $oclcnumNodes->item(0);
            if ($oclcnumFirstNode instanceof DOMElement) {
                return explode(' ', $oclcnumFirstNode->getAttribute('isbn'));
            }
        }
        
        return null;
    }

    /**
     * Fills the given citation description with
     * meta-data retrieved from Worldcat
     * @return MetadataDescription|null
     */
    private function _lookupWorldcat(string $oclcId) {
        $lookupParams = ['wskey' => $this->getApiKey()];
        
        $resultDOM = $this->callWebService(WORLDCAT_WEBSERVICE_EXTRACT . urlencode($oclcId), $lookupParams);
        
        if ($resultDOM === null) {
            return null;
        }

        // [WIZDAM NOTE] Using XSL transformation
        $metadata = $this->transformWebServiceResults($resultDOM, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'worldcat.xsl');
        
        if ($metadata === null) {
            return null;
        }
        
        // FIXME: Use MARC parsed author field in XSL rather than full name

        // Clean non-numerics from ISBN
        if (!empty($metadata['isbn'])) {
            $metadata['isbn'] = PKPString::regexp_replace('/[^\dX]*/', '', $metadata['isbn']);
        }

        // Clean non-numerics from issued date (year)
        if (!empty($metadata['date'])) {
            $metadata['date'] = PKPString::regexp_replace('/,.*/', ', ', $metadata['date']);
            $metadata['date'] = PKPString::regexp_replace('/[^\d{4}]/', '', $metadata['date']);
        }

        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
    }

    /**
     * Fills the given citation object with
     * meta-data retrieved from xISBN
     * @return MetadataDescription|null
     */
    private function _lookupXIsbn(string $isbn) {
        $lookupParams = [
            'method' => 'getMetadata',
            'format' => 'xml',
            'fl'     => '*'
        ];
        
        $resultDOM = $this->callWebService(WORLDCAT_WEBSERVICE_XISBN . urlencode($isbn), $lookupParams);
        
        if ($resultDOM === null) {
            return null;
        }

        // Extract metadata from response
        $recordNodes = $resultDOM->getElementsByTagName('isbn');
        
        if ($recordNodes->length === 0) {
            return null;
        }

        $recordNode = $recordNodes->item(0);
        if (!$recordNode instanceof DOMElement) {
            return null;
        }

        $metadata = [];
        $metadata['isbn'] = $isbn;
        $metadata['date'] = $recordNode->getAttribute('year');
        $metadata['edition'] = $recordNode->getAttribute('ed');
        $metadata['source'] = $recordNode->getAttribute('title');
        $metadata['publisher-name'] = $recordNode->getAttribute('publisher');
        $metadata['publisher-loc'] = $recordNode->getAttribute('city');
        // Authors are of low quality in xISBN compared to Worldcat's MARC records
        $metadata['author'] = $recordNode->getAttribute('author');

        // Clean and process the meta-data
        $metadata = $this->postProcessMetadataArray($metadata);
        
        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
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
            '%isbn%',
            '%aulast% %title% %date%',
            '%title% %date%',
            '%aulast% %date%',
            '%aulast% %title%',
        ];
    }
}
?>