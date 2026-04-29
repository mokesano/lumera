<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationLookup_crossref_filter
 */

/**
 * @file plugins/citationLookup/crossref/filter/CrossrefNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossrefNlm30CitationSchemaFilter
 * @ingroup plugins_citationLookup_crossref_filter
 *
 * @brief Filter that uses the Crossref web service to identify a DOI and 
 * corresponding meta-data for a given NLM citation.
 *
 * [WIZDAM TOTAL REFACTOR]
 * - PHP 8.1+ Strict Typing
 * - Full Type Hinting & Return Types
 * - Removal of deprecated reference operators
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaFilter');
import('lib.pkp.classes.filter.EmailFilterSetting');

// Asumsikan konstanta ini didefinisikan di global atau config, 
// tapi jika tidak, define di sini tidak masalah.
if (!defined('CROSSREF_WEBSERVICE_URL')) {
    define('CROSSREF_WEBSERVICE_URL', 'http://www.crossref.org/openurl/');
}

class CrossrefNlm30CitationSchemaFilter extends Nlm30CitationSchemaFilter {
    
    /**
     * Constructor
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('CrossRef');

        $emailSetting = new EmailFilterSetting(
            'email',
            'metadata.filters.crossref.settings.email.displayName',
            'metadata.filters.crossref.settings.email.validationMessage'
        );
        $this->addSetting($emailSetting);

        parent::__construct(
            $filterGroup,
            [
                NLM30_PUBLICATION_TYPE_JOURNAL,
                NLM30_PUBLICATION_TYPE_CONFPROC,
                NLM30_PUBLICATION_TYPE_BOOK,
                NLM30_PUBLICATION_TYPE_THESIS
            ]
        );
    }

    //
    // Getters and Setters
    //
    
    public function setEmail(string $email): void {
        $this->setData('email', $email);
    }

    public function getEmail(): string {
        return (string) $this->getData('email');
    }

    //
    // Implement template methods from PersistableFilter
    //
    
    public function getClassName(): string {
        return 'lib.pkp.plugins.citationLookup.crossref.filter.CrossrefNlm30CitationSchemaFilter';
    }

    //
    // Implement template methods from Filter
    //
    
    /**
     * @param MetadataDescription $citationDescription
     * @return MetadataDescription|null
     */
    public function process($citationDescription) {
        // Catatan: Parameter di atas tidak saya type-hint 'MetadataDescription' 
        // secara keras di signature jika parent class belum direfactor total.
        // Tapi jika parent class (Filter) method process() abstract-nya sudah Anda ubah,
        // tambahkan: public function process(MetadataDescription $citationDescription): ?MetadataDescription
        
        $email = $this->getEmail();
        
        // Assert dev only
        // assert(!empty($email)); 

        $searchParams = [
            'pid' => $email,
            'noredirect' => 'true',
            'format' => 'unixref'
        ];

        $doi = $citationDescription->getStatement('pub-id[@pub-id-type="doi"]');
        
        if (!empty($doi)) {
            // Directly look up the DOI with OpenURL 0.1.
            $searchParams['id'] = 'doi:' . $doi;
        } else {
            // Use OpenURL meta-data to search for the entry.
            $openurl10Metadata = $this->_prepareOpenurl10Search($citationDescription);
            if ($openurl10Metadata === null) {
                return null;
            }
            $searchParams += $openurl10Metadata;
        }

        // Call the CrossRef web service
        $resultXml = $this->callWebService(
            CROSSREF_WEBSERVICE_URL, 
            $searchParams, 
            XSL_TRANSFORMER_DOCTYPE_STRING
        );

        if ($resultXml === null || PKPString::substr(trim($resultXml), 0, 6) === '<html>') {
            return null;
        }

        // Remove default name spaces
        $resultXml = PKPString::regexp_replace('/ xmlns="[^"]+"/', '', $resultXml);

        // Transform using XSLT
        $metadata = $this->transformWebServiceResults(
            $resultXml, 
            __DIR__ . DIRECTORY_SEPARATOR . 'crossref.xsl'
        );
        
        if ($metadata === null) {
            return null;
        }

        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
    }

    //
    // Private methods
    //
    
    /**
     * Prepare a search with the CrossRef OpenURL resolver
     * @return array|null
     */
    private function _prepareOpenurl10Search(MetadataDescription $citationDescription): ?array {
        // Crosswalk to OpenURL.
        import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaOpenurl10CrosswalkFilter');
        
        $nlm30Openurl10Filter = new Nlm30CitationSchemaOpenurl10CrosswalkFilter();
        $openurl10Citation = $nlm30Openurl10Filter->execute($citationDescription);
        
        if ($openurl10Citation === null) {
            return null;
        }

        // Prepare the search.
        $searchParams = ['url_ver' => 'Z39.88-2004'];

        // Configure the meta-data schema.
        $openurl10CitationSchema = $openurl10Citation->getMetadataSchema();

        // Modern Instanceof Check
        if ($openurl10CitationSchema instanceof Openurl10JournalSchema) {
            $searchParams['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
        } elseif ($openurl10CitationSchema instanceof Openurl10BookSchema) {
            $searchParams['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:book';
        } elseif ($openurl10CitationSchema instanceof Openurl10DissertationSchema) {
            $searchParams['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:dissertation';
        } else {
            // Strict fail if schema unknown
            return null;
        }

        // Add all OpenURL meta-data to the search parameters.
        $searchProperties = [
            'aufirst', 'aulast', 'btitle', 'jtitle', 'atitle', 'issn',
            'artnum', 'date', 'volume', 'issue', 'spage', 'epage'
        ];
        
        foreach ($searchProperties as $property) {
            if ($openurl10Citation->hasStatement($property)) {
                $searchParams['rft.' . $property] = $openurl10Citation->getStatement($property);
            }
        }

        return $searchParams;
    }
}
?>