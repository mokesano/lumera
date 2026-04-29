<?php
declare(strict_types=1);

/**
 * @file plugins/citationLookup/pubmed/filter/PubmedNlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubmedNlm30CitationSchemaFilter
 * @ingroup plugins_citationLookup_pubmed_filter
 *
 * @brief Filter that uses the Pubmed web service to identify a PMID and corresponding
 * meta-data for a given NLM citation.
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Removed legacy references (&)
 * - Enhanced DOM safety checks
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaFilter');
import('lib.pkp.classes.filter.EmailFilterSetting');
import('lib.pkp.classes.metadata.MetadataDescription');
import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30NameSchemaPersonStringFilter'); // Dynamic import moved up
import('lib.pkp.classes.metadata.DateStringNormalizerFilter');

// Define constants safely
if (!defined('PUBMED_WEBSERVICE_ESEARCH')) {
    define('PUBMED_WEBSERVICE_ESEARCH', 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi');
}
if (!defined('PUBMED_WEBSERVICE_EFETCH')) {
    define('PUBMED_WEBSERVICE_EFETCH', 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi');
}
if (!defined('PUBMED_WEBSERVICE_ELINK')) {
    define('PUBMED_WEBSERVICE_ELINK', 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi');
}

class PubmedNlm30CitationSchemaFilter extends Nlm30CitationSchemaFilter {
    
    /**
     * Constructor
     */
    public function __construct(FilterGroup $filterGroup) {
        $this->setDisplayName('PubMed');

        // Instantiate the settings of this filter
        $emailSetting = new EmailFilterSetting(
            'email',
            'metadata.filters.pubmed.settings.email.displayName',
            'metadata.filters.pubmed.settings.email.validationMessage',
            FORM_VALIDATOR_OPTIONAL_VALUE
        );
        $this->addSetting($emailSetting);

        parent::__construct(
            $filterGroup,
            [
                NLM30_PUBLICATION_TYPE_JOURNAL,
                NLM30_PUBLICATION_TYPE_CONFPROC
            ]
        );
    }

    //
    // Getters and Setters
    //
    
    /**
     * Get the email
     * @return string|null
     */
    public function getEmail(): ?string {
        return $this->getData('email');
    }

    //
    // Implement template methods from PersistableFilter
    //
    
    /**
     * @see PersistableFilter::getClassName()
     * @return string
     */
    public function getClassName(): string {
        return 'lib.pkp.plugins.citationLookup.pubmed.filter.PubmedNlm30CitationSchemaFilter';
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
        $pmid = $citationDescription->getStatement('pub-id[@pub-id-type="pmid"]');

        // If the citation does not have a PMID, try to get one from eSearch
        if (empty($pmid)) {
            $pmid = $this->resolvePmidFromCitation($citationDescription);
        }

        // If we have a PMID (either from source or resolved), get metadata
        if (!empty($pmid)) {
            return $this->_lookup($pmid);
        }

        return null;
    }

    /**
     * Helper to resolve PMID using various search strategies
     * [WIZDAM ADDITION] Extracted complexity from process()
     * @param MetadataDescription $citationDescription
     * @return string The resolved PMID or empty string if not found
     */
    protected function resolvePmidFromCitation(MetadataDescription $citationDescription): string {
        $pmidArrayFromAuthorsSearch = [];
        $pmidArrayFromTitleSearch = [];
        $pmidArrayFromStrictSearch = [];

        // 1) "Loose" search based on author list
        $authors = $citationDescription->getStatement('person-group[@person-group-type="author"]');
        if (is_array($authors) && !empty($authors)) {
            $personNameFilter = new Nlm30NameSchemaPersonStringFilter(
                PERSON_STRING_FILTER_MULTIPLE, 
                '%firstname%%initials%%prefix% %surname%%suffix%', 
                ', '
            );
            $authorsString = (string) $personNameFilter->execute($authors);
            
            if (!empty($authorsString)) {
                $pmidArrayFromAuthorsSearch = $this->_search($authorsString);
            }
        }

        // 2) "Loose" search based on article title
        $articleTitle = (string) $citationDescription->getStatement('article-title');
        if (!empty($articleTitle)) {
            $pmidArrayFromTitleSearch = $this->_search($articleTitle);
        }

        // 3) "Strict" search based on detailed info
        $searchTerms = $this->buildStrictSearchTerms($citationDescription);
        if (!empty($searchTerms)) {
            $pmidArrayFromStrictSearch = $this->_search($searchTerms);
        }

        

        // Logic to narrow down to one PMID using intersections
        
        // A. Strict search has exactly one result
        if (count($pmidArrayFromStrictSearch) === 1) {
            return $pmidArrayFromStrictSearch[0];
        }

        // B. 3-way intersection (Title & Authors & Strict)
        $intersect3 = array_intersect($pmidArrayFromTitleSearch, $pmidArrayFromAuthorsSearch, $pmidArrayFromStrictSearch);
        if (count($intersect3) === 1) {
            return current($intersect3);
        }

        // C. 2-way intersection: Title & Strict
        $intersectTitleStrict = array_intersect($pmidArrayFromTitleSearch, $pmidArrayFromStrictSearch);
        if (count($intersectTitleStrict) === 1) {
            return current($intersectTitleStrict);
        }

        // D. 2-way intersection: Authors & Strict
        $intersectAuthorsStrict = array_intersect($pmidArrayFromAuthorsSearch, $pmidArrayFromStrictSearch);
        if (count($intersectAuthorsStrict) === 1) {
            return current($intersectAuthorsStrict);
        }

        // E. 2-way intersection: Authors & Title
        $intersectAuthorsTitle = array_intersect($pmidArrayFromAuthorsSearch, $pmidArrayFromTitleSearch);
        if (count($intersectAuthorsTitle) === 1) {
            return current($intersectAuthorsTitle);
        }

        // F. Single result fallbacks
        if (count($pmidArrayFromTitleSearch) === 1) {
            return $pmidArrayFromTitleSearch[0];
        }

        if (count($pmidArrayFromAuthorsSearch) === 1) {
            return $pmidArrayFromAuthorsSearch[0];
        }

        return '';
    }

    /**
     * Helper to build strict search string
     * @param MetadataDescription $citationDescription
     * @return string The constructed search terms
     */
    private function buildStrictSearchTerms(MetadataDescription $citationDescription): string {
        $searchProperties = [
            'article-title' => '',
            'person-group[@person-group-type="author"]' => '[Auth]',
            'source' => '[Jour]',
            'date' => '[DP]',
            'volume' => '[VI]',
            'issue' => '[IP]',
            'fpage' => '[PG]'
        ];

        $searchTerms = '';
        $statements = $citationDescription->getStatements();

        foreach ($searchProperties as $nlm30Property => $pubmedProperty) {
            if (!isset($statements[$nlm30Property])) {
                continue;
            }

            if (!empty($searchTerms)) {
                $searchTerms .= ' AND ';
            }

            // Special treatment for authors
            if ($nlm30Property === 'person-group[@person-group-type="author"]') {
                $firstAuthor = $statements[$nlm30Property][0] ?? null;
                if ($firstAuthor instanceof MetadataDescription) {
                    $searchTerms .= (string) $firstAuthor->getStatement('surname');
                    $givenNames = $firstAuthor->getStatement('given-names');
                    if (is_array($givenNames) && isset($givenNames[0])) {
                        $searchTerms .= ' ' . PKPString::substr($givenNames[0], 0, 1);
                    }
                }
            } else {
                $searchTerms .= $citationDescription->getStatement($nlm30Property);
            }

            $searchTerms .= $pubmedProperty;
        }
        return $searchTerms;
    }

    //
    // Private methods
    //
    
    /**
     * Searches the given search terms with the pubmed
     * eSearch and returns the found PMIDs as an array.
     * @return array
     * @param string $searchTerms
     */
    private function _search(string $searchTerms): array {
        $searchParams = [
            'db' => 'pubmed',
            'tool' => 'pkp-wal',
            'term' => $searchTerms
        ];

        $email = $this->getEmail();
        if ($email !== null) {
            $searchParams['email'] = $email;
        }

        $resultDOM = $this->callWebService(PUBMED_WEBSERVICE_ESEARCH, $searchParams);

        if ($resultDOM === null) {
            return [];
        }

        $pmidArray = [];
        // [WIZDAM FIX] Safe iteration
        $idNodes = $resultDOM->getElementsByTagName('Id');
        if ($idNodes) {
            foreach ($idNodes as $idNode) {
                $pmidArray[] = $idNode->textContent;
            }
        }

        return $pmidArray;
    }

    /**
     * Fills the given citation object with
     * meta-data retrieved from PubMed.
     * @return MetadataDescription|null
     * @param string $pmid
     */
    private function _lookup(string $pmid) {
        // Use eFetch to get XML metadata for the given PMID
        $lookupParams = [
            'db' => 'pubmed',
            'mode' => 'xml',
            'tool' => 'pkp-wal',
            'id' => $pmid
        ];

        $email = $this->getEmail();
        if ($email !== null) {
            $lookupParams['email'] = $email;
        }

        $resultDOM = $this->callWebService(PUBMED_WEBSERVICE_EFETCH, $lookupParams);
        if ($resultDOM === null) {
            return null;
        }

        // [WIZDAM FIX] Safe DOM Access
        $articleTitle = '';
        $list = $resultDOM->getElementsByTagName("ArticleTitle");
        if ($list->length > 0) {
            $articleTitle = $list->item(0)->textContent;
        }

        $source = '';
        $list = $resultDOM->getElementsByTagName("MedlineTA");
        if ($list->length > 0) {
            $source = $list->item(0)->textContent;
        }

        $metadata = [
            'pub-id[@pub-id-type="pmid"]' => $pmid,
            'article-title' => $articleTitle,
            'source' => $source,
        ];

        $list = $resultDOM->getElementsByTagName("Volume");
        if ($list->length > 0) {
            $metadata['volume'] = $list->item(0)->textContent;
        }

        $list = $resultDOM->getElementsByTagName("Issue");
        if ($list->length > 0) {
            $metadata['issue'] = $list->item(0)->textContent;
        }

        // Get list of author full names
        $authors = $resultDOM->getElementsByTagName("Author");
        foreach ($authors as $authorNode) {
            if (!isset($metadata['person-group[@person-group-type="author"]'])) {
                $metadata['person-group[@person-group-type="author"]'] = [];
            }

            $authorDescription = new MetadataDescription('lib.pkp.plugins.metadata.nlm30.schema.Nlm30NameSchema', ASSOC_TYPE_AUTHOR);

            // Surname
            $lastNameNodes = $authorNode->getElementsByTagName("LastName");
            if ($lastNameNodes->length > 0) {
                $authorDescription->addStatement('surname', $lastNameNodes->item(0)->textContent);
            }

            // Given names
            $givenNamesString = '';
            $firstNameNodes = $authorNode->getElementsByTagName("FirstName");
            if ($firstNameNodes->length > 0) {
                $givenNamesString = $firstNameNodes->item(0)->textContent;
            } else {
                $foreNameNodes = $authorNode->getElementsByTagName("ForeName");
                if ($foreNameNodes->length > 0) {
                    $givenNamesString = $foreNameNodes->item(0)->textContent;
                }
            }
            
            if (!empty($givenNamesString)) {
                foreach (explode(' ', $givenNamesString) as $givenName) {
                    $authorDescription->addStatement('given-names', PKPString::trimPunctuation($givenName));
                }
            }

            // Suffix
            $suffixNodes = $authorNode->getElementsByTagName("Suffix");
            if ($suffixNodes->length > 0) {
                $authorDescription->addStatement('suffix', $suffixNodes->item(0)->textContent);
            }

            $metadata['person-group[@person-group-type="author"]'][] = $authorDescription;
        }

        // Extract pagination
        $medlinePgnNodes = $resultDOM->getElementsByTagName("MedlinePgn");
        if ($medlinePgnNodes->length > 0) {
            $medlinePgnFirstNode = $medlinePgnNodes->item(0);
            $pages = [];
            if (PKPString::regexp_match_get("/^[:p\.\s]*(?P<fpage>[Ee]?\d+)(-(?P<lpage>\d+))?/", $medlinePgnFirstNode->textContent, $pages)) {
                $fPage = (int) $pages['fpage'];
                $metadata['fpage'] = $fPage;
                
                if (!empty($pages['lpage'])) {
                    $lPage = (int) $pages['lpage'];
                    // Deal with shortcuts like '382-7'
                    if ($lPage < $fPage) {
                        $fPageStr = (string)$pages['fpage'];
                        $lPageStr = (string)$pages['lpage'];
                        $lPage = (int) (PKPString::substr($fPageStr, 0, -PKPString::strlen($lPageStr)) . $lPageStr);
                    }
                    $metadata['lpage'] = $lPage;
                }
            }
        }

        // Get publication date
        $dateNode = null;
        $articleDateNodes = $resultDOM->getElementsByTagName("ArticleDate");
        if ($articleDateNodes->length > 0) {
            $dateNode = $articleDateNodes->item(0);
        } else {
            $pubDateNodes = $resultDOM->getElementsByTagName("PubDate");
            if ($pubDateNodes->length > 0) {
                $dateNode = $pubDateNodes->item(0);
            }
        }

        if ($dateNode !== null) {
            $publicationDate = '';
            $requiresNormalization = false;
            foreach (['Year' => 4, 'Month' => 2, 'Day' => 2] as $dateElement => $padding) {
                $dateElementNodes = $dateNode->getElementsByTagName($dateElement);
                if ($dateElementNodes->length > 0) {
                    if (!empty($publicationDate)) $publicationDate .= '-';
                    $content = $dateElementNodes->item(0)->textContent;
                    $datePart = str_pad($content, $padding, '0', STR_PAD_LEFT);
                    
                    if (!is_numeric($datePart)) {
                        $requiresNormalization = true;
                    }
                    $publicationDate .= $datePart;
                } else {
                    break;
                }
            }

            if ($requiresNormalization) {
                $dateFilter = new DateStringNormalizerFilter();
                $publicationDate = $dateFilter->execute($publicationDate);
            }

            if (!empty($publicationDate)) {
                $metadata['date'] = $publicationDate;
            }
        }

        // Get publication type
        $publicationTypeNodes = $resultDOM->getElementsByTagName("PublicationType");
        foreach ($publicationTypeNodes as $publicationType) {
            if (PKPString::strpos(PKPString::strtolower($publicationType->textContent), 'article') !== false) {
                $metadata['[@publication-type]'] = NLM30_PUBLICATION_TYPE_JOURNAL;
                break;
            }
        }

        // Get DOI
        $articleIdNodes = $resultDOM->getElementsByTagName("ArticleId");
        foreach ($articleIdNodes as $idNode) {
            if ($idNode->getAttribute('IdType') === 'doi') {
                $metadata['pub-id[@pub-id-type="doi"]'] = $idNode->textContent;
            }
        }

        // Use eLink utility
        $this->_appendLinks($pmid, $metadata);

        return $this->getNlm30CitationDescriptionFromMetadataArray($metadata);
    }

    /**
     * Helper to append links via eLink service
     * @param string $pmid
     * @param array $metadata
     */
    private function _appendLinks(string $pmid, array &$metadata): void {
        $lookupParams = [
            'dbfrom' => 'pubmed',
            'cmd' => 'llinks',
            'tool' => 'pkp-wal',
            'id' => $pmid
        ];

        $resultDOM = $this->callWebService(PUBMED_WEBSERVICE_ELINK, $lookupParams);
        if ($resultDOM === null) {
            return;
        }

        $links = [];
        $objUrls = $resultDOM->getElementsByTagName("ObjUrl");
        
        foreach ($objUrls as $linkOut) {
            $attributes = '';
            foreach ($linkOut->getElementsByTagName("Attribute") as $attribute) {
                $attributes .= PKPString::strtolower($attribute->textContent) . ' / ';
            }

            // Only add links to open access resources
            if (PKPString::strpos($attributes, "subscription") === false && 
                PKPString::strpos($attributes, "membership") === false &&
                PKPString::strpos($attributes, "fee") === false && 
                $attributes !== "") {
                
                $urlNodes = $linkOut->getElementsByTagName("Url");
                if ($urlNodes->length > 0) {
                    $links[] = $urlNodes->item(0)->textContent;
                }
            }
        }

        if (isset($links[0])) {
            $metadata['uri'] = $links[0];
        }
    }
}
?>