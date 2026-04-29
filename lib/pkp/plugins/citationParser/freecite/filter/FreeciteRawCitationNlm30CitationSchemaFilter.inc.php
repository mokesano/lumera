<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationParser_freecite_filter
 */

/**
 * @file plugins/citationParser/freecite/filter/FreeciteRawCitationNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FreeciteRawCitationNlm30CitationSchemaFilter
 * @ingroup plugins_citationParser_freecite_filter
 *
 * @brief Parsing filter implementation that uses the Freecite web service.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Modern Constructor
 * - Removed reference operators
 * - Explicit Type Hints
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaFilter');

if (!defined('FREECITE_WEBSERVICE')) {
    define('FREECITE_WEBSERVICE', 'http://freecite.library.brown.edu/citations/create');
}

class FreeciteRawCitationNlm30CitationSchemaFilter extends Nlm30CitationSchemaFilter {
    
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('FreeCite');

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
        return 'lib.pkp.plugins.citationParser.freecite.filter.FreeciteRawCitationNlm30CitationSchemaFilter';
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

        // Freecite requires a post request
        $postData = ['citation' => $citationString];
        
        $resultDOM = $this->callWebService(FREECITE_WEBSERVICE, $postData, XSL_TRANSFORMER_DOCTYPE_DOM, 'POST');
        if ($resultDOM === null) {
            return null;
        }

        // Transform the result into an array of meta-data
        $metadata = $this->transformWebServiceResults($resultDOM, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'freecite.xsl');
        if ($metadata === null) {
            return null;
        }

        // Extract a publisher from the place string if possible
        $metadata = $this->fixPublisherNameAndLocation($metadata);

        // Convert the genre
        if (isset($metadata['genre'])) {
            $genre = $metadata['genre'];
            import('lib.pkp.plugins.metadata.nlm30.filter.Openurl10Nlm30CitationSchemaCrosswalkFilter');
            $genreMap = Openurl10Nlm30CitationSchemaCrosswalkFilter::_getOpenurl10GenreTranslationMapping();
            $metadata['[@publication-type]'] = $genreMap[$genre] ?? $genre;
            unset($metadata['genre']);
        }

        // Convert article title to source for dissertations
        if (isset($metadata['[@publication-type]']) && 
            $metadata['[@publication-type]'] == NLM30_PUBLICATION_TYPE_THESIS && 
            isset($metadata['article-title'])) {
            
            $metadata['source'] = $metadata['article-title'];
            unset($metadata['article-title']);
        }

        unset($metadata['raw_string']);

        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
    }
}
?>