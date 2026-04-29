<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30CitationSchemaFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaFilter
 * @ingroup plugins_metadata_nlm30_filter
 *
 * @brief Abstract base class for all filters that transform
 * NLM citation metadata descriptions.
 */

import('lib.pkp.classes.filter.PersistableFilter');
import('lib.pkp.classes.filter.BooleanFilterSetting');

import('lib.pkp.classes.metadata.MetadataDescription');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30NameSchema');
import('lib.pkp.plugins.metadata.nlm30.filter.PersonStringNlm30NameSchemaFilter');
import('lib.pkp.classes.metadata.DateStringNormalizerFilter');

import('lib.pkp.classes.webservice.XmlWebService');

import('lib.pkp.classes.xml.XMLHelper');
import('lib.pkp.classes.xslt.XSLTransformationFilter');

class Nlm30CitationSchemaFilter extends PersistableFilter {
    /** @var array */
    protected $_supportedPublicationTypes;

    /**
     * Constructor
     * @param FilterGroup $filterGroup
     * @param array $supportedPublicationTypes
     */
    public function __construct($filterGroup, $supportedPublicationTypes = []) {
        $this->setData('phpVersionMin', '7.4.0'); // Updated per Protocol V3 assumption
        $this->_supportedPublicationTypes = $supportedPublicationTypes;

        $isOptional = new BooleanFilterSetting('isOptional',
                'metadata.filters.settings.isOptional.displayName',
                'metadata.filters.settings.isOptional.validationMessage');
        $this->addSetting($isOptional);

        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30CitationSchemaFilter($filterGroup, $supportedPublicationTypes = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Setters and Getters
    //
    /**
     * Get the supported publication types
     * @return array
     */
    public function getSupportedPublicationTypes() {
        return $this->_supportedPublicationTypes;
    }

    /**
     * Whether this filter is optional within its
     * context (journal, conference, press, etc.)
     * @return bool
     */
    public function getIsOptional() {
        return $this->getData('isOptional');
    }

    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::supports()
     * @param mixed $input
     * @param mixed $output
     * @return bool
     */
    public function supports($input, $output) {
        if (!parent::supports($input, $output)) return false;

        if ($this->getInputType() instanceof MetadataTypeDescription) {
            $publicationType = $input->getStatement('[@publication-type]');
            if (!empty($publicationType) && !in_array($publicationType, $this->getSupportedPublicationTypes())) return false;
        }

        if (!is_null($output) && $output instanceof MetadataDescription) {
            $statements = $output->getStatements();
            if (empty($statements)) return false;
        }

        return true;
    }

    //
    // Protected helper methods
    //
    /**
     * Construct an array of search strings from a citation
     * description and an array of search templates.
     * The templates may contain the placeholders
     * %aulast%: the first author's surname
     * %au%:     the first author full name
     * %title%:  the article-title (if it exists),
     * otherwise the source
     * %date%:   the publication year
     * %isbn%:   ISBN
     * @param array $searchTemplates an array of templates (Reference)
     * @param MetadataDescription $citationDescription (Reference)
     * @return array
     */
    public function constructSearchStrings($searchTemplates, $citationDescription) {
        import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30NameSchemaPersonStringFilter');
        $personStringFilter = new Nlm30NameSchemaPersonStringFilter();

        $firstAuthorSurname = $firstAuthor = '';
        $authors = $citationDescription->getStatement('person-group[@person-group-type="author"]');
        if (is_array($authors) && count($authors)) {
            $firstAuthorSurname = (string)$authors[0]->getStatement('surname');
            $firstAuthor = $personStringFilter->execute($authors[0]);
        }

        $firstEditorSurname = $firstEditor = '';
        $editors = $citationDescription->getStatement('person-group[@person-group-type="editor"]');
        if (is_array($editors) && count($editors)) {
            $firstEditorSurname = (string)$editors[0]->getStatement('surname');
            $firstEditor = $personStringFilter->execute($editors[0]);
        }

        $title = (string)($citationDescription->hasStatement('article-title') ?
                $citationDescription->getStatement('article-title') :
                $citationDescription->getStatement('source'));

        $year = (string)$citationDescription->getStatement('date');
        $year = (PKPString::strlen($year) > 4 ? PKPString::substr($year, 0, 4) : $year);

        $isbn = (string)$citationDescription->getStatement('isbn');

        $searchStrings = [];
        foreach($searchTemplates as $searchTemplate) {
            $searchStrings[] = str_replace(
                    ['%aulast%', '%au%', '%title%', '%date%', '%isbn%'],
                    [$firstAuthorSurname, $firstAuthor, $title, $year, $isbn],
                    $searchTemplate
                );
            $searchStrings[] = str_replace(
                    ['%aulast%', '%au%', '%title%', '%date%', '%isbn%'],
                    [$firstEditorSurname, $firstEditor, $title, $year, $isbn],
                    $searchTemplate
                );
        }

        $searchStrings = array_map(['String', 'trimPunctuation'], $searchStrings);
        $searchStrings = array_unique($searchStrings);
        return arrayClean($searchStrings);
    }

    /**
     * Call web service with the given parameters
     * @param string $url
     * @param array $params GET or POST parameters (Reference)
     * @param int $returnType
     * @param string $method
     * @return DOMDocument|null in case of error
     */
    public function callWebService($url, $params, $returnType = XSL_TRANSFORMER_DOCTYPE_DOM, $method = 'GET') {
        $webServiceRequest = new WebServiceRequest($url, $params, $method);

        $xmlWebService = new XmlWebService();
        $xmlWebService->setReturnType($returnType);
        $result = $xmlWebService->call($webServiceRequest);

        if (is_null($result)) {
            if ($xmlWebService->getLastResponseStatus() >= 500 || $xmlWebService->getLastResponseStatus() <= 599) {
                $this->setData('serverError', true);
            }

            // Construct error info
            $webserviceUrl = $url;
            if ($method == 'GET') {
                $keyValuePairs = [];
                foreach ($params as $key => $value) {
                    $keyValuePairs[] = $key.'='.$value;
                }
                $webserviceUrl .= '?'.implode('&', $keyValuePairs);
            }

            $translationParams = [
                'filterName' => $this->getDisplayName(),
                'webserviceUrl' => $webserviceUrl,
                'httpMethod' => $method
            ];
            
            // Protocol V3: Use NotificationManager directly
            $request = Application::getRequest();
            if ($request && $request->getUser()) {
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_ERROR,
                    ['contents' => __('submission.citations.filter.webserviceError', $translationParams)]
                );
            }
        }

        return $result;
    }

    /**
     * Takes the raw xml result of a web service and
     * transforms it via XSL to a (preliminary) XML similar
     * to NLM which is then re-encoded into an array. Finally
     * some typical post-processing is performed.
     * FIXME: Rewrite parser/lookup filter XSL to produce real NLM
     * element-citation XML and factor this code into an NLM XML to
     * NLM description filter.
     * @param mixed $xmlResult string or DOMDocument (Reference)
     * @param string $xslFileName
     * @return array|null a metadata array
     */
    public function transformWebServiceResults($xmlResult, $xslFileName) {
        $xslFilter = new XSLTransformationFilter(
                PersistableFilter::tempGroup('xml::*', 'xml::*'),
                'Web Service Transformation');
        $xslFilter->setXSLFilename($xslFileName);
        $xslFilter->setResultType(XSL_TRANSFORMER_DOCTYPE_DOM);
        
        $preliminaryNlm30DOM = $xslFilter->execute($xmlResult);
        
        if (is_null($preliminaryNlm30DOM) || is_null($preliminaryNlm30DOM->documentElement)) {
            $translationParams = ['filterName' => $this->getDisplayName()];
            
            // Protocol V3: Notification
            $request = Application::getRequest();
            if ($request && $request->getUser()) {
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_ERROR,
                    ['contents' => __('submission.citations.filter.webserviceResultTransformationError', $translationParams)]
                );
            }
            return null;
        }

        $xmlHelper = new XMLHelper();
        $preliminaryNlm30Array = $xmlHelper->xmlToArray($preliminaryNlm30DOM->documentElement);

        // Protocol V3: No assignment by reference here
        $preliminaryNlm30Array = $this->postProcessMetadataArray($preliminaryNlm30Array);

        return $preliminaryNlm30Array;
    }

    /**
     * Post processes an NLM meta-data array
     * @param array $preliminaryNlm30Array (Reference)
     * @return array
     */
    public function postProcessMetadataArray($preliminaryNlm30Array) {
        $preliminaryNlm30Array = arrayClean($preliminaryNlm30Array);
        $preliminaryNlm30Array = $this->_recursivelyTrimPunctuation($preliminaryNlm30Array);

        foreach(['author' => ASSOC_TYPE_AUTHOR, 'editor' => ASSOC_TYPE_EDITOR] as $personType => $personAssocType) {
            if (isset($preliminaryNlm30Array[$personType])) {
                $personStrings = $preliminaryNlm30Array[$personType];
                unset($preliminaryNlm30Array[$personType]);

                if (is_scalar($personStrings)) {
                    $personStringFilter = new PersonStringNlm30NameSchemaFilter($personAssocType, PERSON_STRING_FILTER_MULTIPLE);
                    $persons = $personStringFilter->execute($personStrings);
                } else {
                    $personStringFilter = new PersonStringNlm30NameSchemaFilter($personAssocType, PERSON_STRING_FILTER_SINGLE);
                    // array_map returns array by value
                    $persons = array_map([$personStringFilter, 'execute'], $personStrings);
                }

                $preliminaryNlm30Array['person-group[@person-group-type="'.$personType.'"]'] = $persons;
            }
        }

        if (isset($preliminaryNlm30Array['comment']) && is_array($preliminaryNlm30Array['comment'])) {
            $preliminaryNlm30Array['comment'] = implode("\n", $preliminaryNlm30Array['comment']);
        }

        foreach(['date', 'conf-date', 'access-date'] as $dateProperty) {
            if (isset($preliminaryNlm30Array[$dateProperty])) {
                $dateFilter = new DateStringNormalizerFilter();
                $preliminaryNlm30Array[$dateProperty] = $dateFilter->execute($preliminaryNlm30Array[$dateProperty]);
            }
        }

        foreach(['fpage', 'lpage', 'size'] as $integerProperty) {
            if (isset($preliminaryNlm30Array[$integerProperty]) && is_numeric($preliminaryNlm30Array[$integerProperty])) {
                $preliminaryNlm30Array[$integerProperty] = (int)$preliminaryNlm30Array[$integerProperty];
            }
        }

        $elementToAttributeMap = [
            'access-date' => 'date-in-citation[@content-type="access-date"]',
            'issn-ppub' => 'issn[@pub-type="ppub"]',
            'issn-epub' => 'issn[@pub-type="epub"]',
            'pub-id-doi' => 'pub-id[@pub-id-type="doi"]',
            'pub-id-publisher-id' => 'pub-id[@pub-id-type="publisher-id"]',
            'pub-id-coden' => 'pub-id[@pub-id-type="coden"]',
            'pub-id-sici' => 'pub-id[@pub-id-type="sici"]',
            'pub-id-pmid' => 'pub-id[@pub-id-type="pmid"]',
            'publication-type' => '[@publication-type]'
        ];
        
        foreach($elementToAttributeMap as $elementName => $nlm30PropertyName) {
            if (isset($preliminaryNlm30Array[$elementName])) {
                $preliminaryNlm30Array[$nlm30PropertyName] = $preliminaryNlm30Array[$elementName];
                unset($preliminaryNlm30Array[$elementName]);
            }
        }

        // Pass by value to helper, get modified result back (helper logic below also updated)
        $preliminaryNlm30Array = $this->_guessPublicationType($preliminaryNlm30Array);

        if (isset($preliminaryNlm30Array['[@publication-type]']) && $preliminaryNlm30Array['[@publication-type]'] == 'book') {
            if (isset($preliminaryNlm30Array['article-title']) && !isset($preliminaryNlm30Array['source'])) {
                $preliminaryNlm30Array['source'] = $preliminaryNlm30Array['article-title'];
                unset($preliminaryNlm30Array['article-title']);
            }
        }

        return $preliminaryNlm30Array;
    }

    /**
     * Creates a new NLM citation description and adds the data
     * of an array of property/value pairs as statements.
     * @param array $metadataArray (Reference)
     * @return MetadataDescription|null
     */
    public function getNlm30CitationDescriptionFromMetadataArray($metadataArray) {
        $citationDescription = new MetadataDescription('lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema', ASSOC_TYPE_CITATION);

        $metadataArray = arrayClean($metadataArray);
        if (!$citationDescription->setStatements($metadataArray)) {
            $translationParams = ['filterName' => $this->getDisplayName()];
            
            // Protocol V3: Notification
            $request = Application::getRequest();
            if ($request && $request->getUser()) {
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_ERROR,
                    ['contents' => __('submission.citations.filter.invalidMetadata', $translationParams)]
                );
            }
            return null;
        }

        $citationDescription->setDisplayName($this->getDisplayName());
        return $citationDescription;
    }

    /**
     * Take an NLM preliminary meta-data array and fix publisher-loc
     * and publisher-name entries:
     * - If there is a location but no name then try to extract a
     * publisher name from the location string.
     * - Make sure that location and name are not the same.
     * - Copy institution to publisher if no publisher is set,
     * otherwise leave the institution.
     * @param array $metadata (Reference)
     * @return array
     */
    public function fixPublisherNameAndLocation($metadata) {
        if (isset($metadata['publisher-loc'])) {
            if (empty($metadata['publisher-name'])) {
                $metadata['publisher-name'] = PKPString::regexp_replace('/.*:([^,]+),?.*/', '\1', $metadata['publisher-loc']);
            }
            $metadata['publisher-loc'] = PKPString::regexp_replace('/^(.+):.*/', '\1', $metadata['publisher-loc']);

            if (!empty($metadata['publisher-name']) && $metadata['publisher-name'] == $metadata['publisher-loc']) {
                unset($metadata['publisher-name']);
            }
        }

        if (isset($metadata['institution']) && (!isset($metadata['publisher-name']) || empty($metadata['publisher-name']))) {
            $metadata['publisher-name'] = $metadata['institution'];
        }

        foreach(['publisher-name', 'publisher-loc'] as $publisherProperty) {
            if (isset($metadata[$publisherProperty])) {
                $metadata[$publisherProperty] = PKPString::trimPunctuation($metadata[$publisherProperty]);
            }
        }

        return $metadata;
    }

    //
    // Private helper methods
    //
    /**
     * Try to guess a citation's publication type based on detected elements
     * @param array $metadataArray (Reference)
     */
    protected function _guessPublicationType($metadataArray) {
        if (isset($metadataArray['[@publication-type]'])) return $metadataArray;

        $typicalPropertyNames = [
            'volume' => NLM30_PUBLICATION_TYPE_JOURNAL,
            'issue' => NLM30_PUBLICATION_TYPE_JOURNAL,
            'season' => NLM30_PUBLICATION_TYPE_JOURNAL,
            'issn[@pub-type="ppub"]' => NLM30_PUBLICATION_TYPE_JOURNAL,
            'issn[@pub-type="epub"]' => NLM30_PUBLICATION_TYPE_JOURNAL,
            'pub-id[@pub-id-type="pmid"]' => NLM30_PUBLICATION_TYPE_JOURNAL,
            'person-group[@person-group-type="editor"]' => NLM30_PUBLICATION_TYPE_BOOK,
            'edition' => NLM30_PUBLICATION_TYPE_BOOK,
            'chapter-title' => NLM30_PUBLICATION_TYPE_BOOK,
            'isbn' => NLM30_PUBLICATION_TYPE_BOOK,
            'publisher-name' => NLM30_PUBLICATION_TYPE_BOOK,
            'publisher-loc' => NLM30_PUBLICATION_TYPE_BOOK,
            'conf-date' => NLM30_PUBLICATION_TYPE_CONFPROC,
            'conf-loc' => NLM30_PUBLICATION_TYPE_CONFPROC,
            'conf-name' => NLM30_PUBLICATION_TYPE_CONFPROC,
            'conf-sponsor' => NLM30_PUBLICATION_TYPE_CONFPROC
        ];

        $hitCounters = [
            NLM30_PUBLICATION_TYPE_JOURNAL => 0,
            NLM30_PUBLICATION_TYPE_BOOK => 0,
            NLM30_PUBLICATION_TYPE_CONFPROC => 0
        ];
        $highestCounterValue = 0;
        $probablePublicationType = null;
        
        foreach($typicalPropertyNames as $typicalPropertyName => $currentProbablePublicationType) {
            if (isset($metadataArray[$typicalPropertyName])) {
                $hitCounters[$currentProbablePublicationType]++;
                if ($hitCounters[$currentProbablePublicationType] > $highestCounterValue) {
                    $highestCounterValue = $hitCounters[$currentProbablePublicationType];
                    $probablePublicationType = $currentProbablePublicationType;
                } elseif ($hitCounters[$currentProbablePublicationType] == $highestCounterValue) {
                    $probablePublicationType = null;
                }
            }
        }

        if (!is_null($probablePublicationType)) {
            $metadataArray['[@publication-type]'] = $probablePublicationType;
        }
        
        return $metadataArray;
    }

    /**
     * Recursively trim punctuation from a metadata array.
     * @param array $metadataArray (Reference)
     * @return array
     */
    protected function _recursivelyTrimPunctuation($metadataArray) {
        assert(is_array($metadataArray));
        foreach($metadataArray as $metadataKey => $metadataValue) {
            if (is_array($metadataValue)) {
                $metadataArray[$metadataKey] = $this->_recursivelyTrimPunctuation($metadataValue);
            }
            if (is_string($metadataValue)) {
                $metadataArray[$metadataKey] = PKPString::trimPunctuation($metadataValue);
            }
        }
        return $metadataArray;
    }

    /**
     * Static method that returns a list of permitted
     * publication types.
     * @return array
     */
    public static function _allowedPublicationTypes() {
        static $allowedPublicationTypes = [
            NLM30_PUBLICATION_TYPE_JOURNAL,
            NLM30_PUBLICATION_TYPE_CONFPROC,
            NLM30_PUBLICATION_TYPE_BOOK,
            NLM30_PUBLICATION_TYPE_THESIS
        ];
        return $allowedPublicationTypes;
    }
}

?>