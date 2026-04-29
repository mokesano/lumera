<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationParser_parscit_filter
 */

/**
 * @file plugins/citationParser/parscit/filter/ParscitRawCitationNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ParscitRawCitationNlm30CitationSchemaFilter
 * @ingroup plugins_citationParser_parscit_filter
 *
 * @brief Parsing filter implementation that uses the Parscit web service.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Modern Constructor
 * - Removed reference operators
 * - Explicit Type Hints
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaFilter');

if (!defined('PARSCIT_WEBSERVICE')) {
    define('PARSCIT_WEBSERVICE', 'http://aye.comp.nus.edu.sg/parsCit/parsCit.cgi');
}

class ParscitRawCitationNlm30CitationSchemaFilter extends Nlm30CitationSchemaFilter {
    
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('ParsCit');

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
        return 'lib.pkp.plugins.citationParser.parscit.filter.ParscitRawCitationNlm30CitationSchemaFilter';
    }

    //
    // Implement template methods from Filter
    //
    
    /**
     * @see Filter::process()
     * @param string $input
     * @return MetadataDescription|null
     */
    public function process($input) {
        $citationString = (string) $input;
        
        $queryParams = [
            'demo' => '3',
            'textlines' => $citationString
        ];

        // Parscit web form - the result is (mal-formed) HTML
        $result = $this->callWebService(PARSCIT_WEBSERVICE, $queryParams, XSL_TRANSFORMER_DOCTYPE_STRING, 'POST');
        if ($result === null) {
            return null;
        }

        $result = html_entity_decode($result);

        // Detect errors.
        if (!PKPString::regexp_match('/.*<algorithm[^>]+>.*<\/algorithm>.*/s', $result)) {
            $translationParams = ['filterName' => $this->getDisplayName()];
            $this->addError(__('submission.citations.filter.webserviceResultTransformationError', $translationParams));
            return null;
        }

        // Screen-scrape the tagged portion and turn it into XML.
        $xmlResult = PKPString::regexp_replace('/.*<algorithm[^>]+>(.*)<\/algorithm>.*/s', '\1', $result);
        $xmlResult = PKPString::regexp_replace('/&/', '&amp;', $xmlResult);

        // Transform the result into an array of meta-data.
        $metadata = $this->transformWebServiceResults($xmlResult, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'parscit.xsl');
        if ($metadata === null) {
            return null;
        }

        // Extract a publisher from the place string if possible.
        $metadata = $this->fixPublisherNameAndLocation($metadata);

        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
    }
}
?>