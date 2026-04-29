<?php
declare(strict_types=1);

/**
 * @file classes/citation/CitationDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CitationDAO
 * @ingroup citation
 * @see Citation
 *
 * @brief Operations for retrieving and modifying Citation objects
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

// FIXME: We currently have direct dependencies on specific filter groups.
// We have to make this configurable if we want to support different meta-data
// standards in the citation assistant (e.g. MODS).
define('CITATION_PARSER_FILTER_GROUP', 'plaintext=>nlm30-element-citation');
define('CITATION_LOOKUP_FILTER_GROUP', 'nlm30-element-citation=>nlm30-element-citation');

import('lib.pkp.classes.citation.Citation');

class CitationDAO extends DAO {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CitationDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Insert a new citation.
     * @param Citation $citation
     * @return int the new citation id
     */
    public function insertObject($citation) {
        $seq = $citation->getSeq();
        if (!(is_numeric($seq) && $seq > 0)) {
            // Find the latest sequence number
            $result = $this->retrieve(
                'SELECT MAX(seq) AS lastseq FROM citations
                 WHERE assoc_type = ? AND assoc_id = ?',
                [
                    (int) $citation->getAssocType(),
                    (int) $citation->getAssocId(),
                ]
            );

            if ($result->RecordCount() != 0) {
                $row = $result->GetRowAssoc(false);
                $seq = $row['lastseq'] + 1;
            } else {
                $seq = 1;
            }
            $citation->setSeq($seq);
        }

        $this->update(
            sprintf('INSERT INTO citations
                (assoc_type, assoc_id, citation_state, raw_citation, seq)
                VALUES
                (?, ?, ?, ?, ?)'),
            [
                (int) $citation->getAssocType(),
                (int) $citation->getAssocId(),
                (int) $citation->getCitationState(),
                $citation->getRawCitation(),
                (int) $seq
            ]
        );
        $citation->setId($this->getInsertId());
        $this->_updateObjectMetadata($citation);
        $this->updateCitationSourceDescriptions($citation);
        return $citation->getId();
    }

    /**
     * Retrieve a citation by id.
     * @param int $citationId
     * @return Citation|null
     */
    public function getObjectById($citationId) {
        $result = $this->retrieve(
            'SELECT * FROM citations WHERE citation_id = ?', 
            (int) $citationId
        );

        $citation = null;
        if ($result->RecordCount() != 0) {
            $citation = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $citation;
    }

    /**
     * Import citations from a raw citation list to the object
     * described by the given association type and id.
     * @param PKPRequest $request
     * @param int $assocType
     * @param int $assocId
     * @param string $rawCitationList
     * @return int the number of spawned citation checking processes
     */
    public function importCitations($request, $assocType, $assocId, $rawCitationList) {
        $assocType = (int) $assocType;
        $assocId = (int) $assocId;

        // Remove existing citations.
        $this->deleteObjectsByAssocId($assocType, $assocId);

        // Tokenize raw citations
        import('lib.pkp.classes.citation.CitationListTokenizerFilter');
        $citationTokenizer = new CitationListTokenizerFilter();
        $citationStrings = $citationTokenizer->execute($rawCitationList);

        // Instantiate and persist citations
        $citations = [];
        if (is_array($citationStrings)) {
            foreach($citationStrings as $seq => $citationString) {
                $citation = new Citation($citationString);

                // Initialize the citation with the raw
                // citation string.
                $citation->setRawCitation($citationString);

                // Set the object association
                $citation->setAssocType($assocType);
                $citation->setAssocId($assocId);

                // Set the counter
                $citation->setSeq($seq + 1);

                $this->insertObject($citation);
                $citations[$citation->getId()] = $citation;
                unset($citation);
            }
        }

        // Check new citations in parallel.
        $noOfProcesses = (int) Config::getVar('general', 'citation_checking_max_processes');
        $processDao = DAORegistry::getDAO('ProcessDAO');
        return $processDao->spawnProcesses($request, 'api.citation.CitationApiHandler', 'checkAllCitations', PROCESS_TYPE_CITATION_CHECKING, $noOfProcesses);
    }

    /**
     * Parses and looks up the given citation.
     *
     * NB: checking the citation will not automatically
     * persist the changes. This has to be done by the caller.
     *
     * @param PKPRequest $request
     * @param Citation $originalCitation
     * @param array $filterIds a custom selection of filters to be applied
     * @return Citation the checked citation. If checking
     * was not successful then the original citation
     * will be returned unchanged.
     */
    public function checkCitation($request, $originalCitation, $filterIds = []) {
        assert($originalCitation instanceof Citation);

        // Only parse the citation if it has not been parsed before.
        // Otherwise we risk to overwrite manual user changes.
        $filteredCitation = $originalCitation;
        if ($filteredCitation->getCitationState() < CITATION_PARSED) {
            // Parse the requested citation
            $filterCallback = [$this, '_instantiateParserFilters'];
            $filteredCitation = $this->_filterCitation($request, $filteredCitation, $filterCallback, CITATION_PARSED, $filterIds);
        }

        // Always re-lookup the citation even if it's been looked-up
        // before. The user asked us to re-check so there's probably
        // additional manual information in the citation fields.
        $filterCallback = [$this, '_instantiateLookupFilters'];
        $filteredCitation = $this->_filterCitation($request, $filteredCitation, $filterCallback, CITATION_LOOKED_UP, $filterIds);

        // Return the filtered citation.
        return $filteredCitation;
    }

    /**
     * Claims (locks) the next raw (unparsed) citation found in the
     * database and checks it. This method is idempotent and parallelisable.
     * It uses an atomic locking strategy to avoid race conditions.
     *
     * @param PKPRequest $request
     * @param string $lockId a globally unique id that identifies the calling process.
     * @return bool true if a citation was found and checked, otherwise false.
     */
    public function checkNextRawCitation($request, $lockId) {
        // NB: We implement an atomic locking strategy to make
        // sure that no two parallel background processes can claim the
        // same citation.
        $rawCitation = null;
        for ($try = 0; $try < 3; $try++) {
            // We use three statements (read, write, read) rather than
            // MySQL's UPDATE ... LIMIT ... to guarantee compatibility
            // with ANSI SQL.

            // Get the ID of the next raw citation.
            $result = $this->retrieve(
                'SELECT citation_id
                FROM citations
                WHERE citation_state = ?
                LIMIT 1',
                CITATION_RAW
            );
            
            $nextRawCitationId = null;
            if ($result->RecordCount() > 0) {
                $nextRawCitation = $result->GetRowAssoc(false);
                $nextRawCitationId = $nextRawCitation['citation_id'];
            } else {
                // Nothing to do.
                $result->Close();
                return false;
            }
            $result->Close();
            unset($result);

            // Lock the citation.
            $this->update(
                'UPDATE citations
                SET citation_state = ?, lock_id = ?
                WHERE citation_id = ? AND citation_state = ?',
                [CITATION_CHECKED, $lockId, (int) $nextRawCitationId, CITATION_RAW]
            );

            // Make sure that no other concurring process
            // has claimed this citation before we could
            // lock it.
            $result = $this->retrieve(
                'SELECT *
                FROM citations
                WHERE lock_id = ?',
                $lockId
            );
            if ($result->RecordCount() > 0) {
                $rawCitation = $this->_fromRow($result->GetRowAssoc(false));
                $result->Close(); // [WIZDAM FIX] Close result before break
                break;
            }
            $result->Close();
        }

        if (!($rawCitation instanceof Citation)) return false;

        // Check the citation.
        $filteredCitation = $this->checkCitation($request, $rawCitation);

        // Updating the citation will also release the lock.
        $this->updateObject($filteredCitation);

        return true;
    }

    /**
     * Retrieve an array of citations matching a particular association id.
     * @param int $assocType
     * @param int $assocId
     * @param int $minCitationState one of the CITATION_* constants
     * @param int $maxCitationState one of the CITATION_* constants
     * @param DBResultRange $rangeInfo
     * @return DAOResultFactory containing matching Citations
     */
    public function getObjectsByAssocId($assocType, $assocId, $minCitationState = 0, $maxCitationState = CITATION_APPROVED, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT *
            FROM citations
            WHERE assoc_type = ? AND assoc_id = ? AND citation_state >= ? AND citation_state <= ?
            ORDER BY seq, citation_id',
            [(int)$assocType, (int)$assocId, (int)$minCitationState, (int)$maxCitationState],
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_fromRow', ['id']);
        return $returner;
    }

    /**
     * Instantiate citation filters according to
     * the given selection rules.
     *
     * @param int $contextId
     * @param string|array $filterGroups
     * @param array $fromFilterIds
     * @param bool $includeOptionalFilters
     * @return array an array of PersistableFilters
     */
    public function getCitationFilterInstances($contextId, $filterGroups, $fromFilterIds = [], $includeOptionalFilters = false) {
        $filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
        $filterList = [];

        // Retrieve the requested filter group(s).
        if (is_scalar($filterGroups)) $filterGroups = [$filterGroups];
        foreach($filterGroups as $filterGroupSymbolic) {
            $filterList = array_merge($filterList, $filterDao->getObjectsByGroup($filterGroupSymbolic, $contextId));
        }

        // Filter the result list:
        $finalFilterList = [];
        if (empty($fromFilterIds)) {
            if ($includeOptionalFilters) {
                // Return all filters including optional filters.
                $finalFilterList = $filterList;
            } else {
                // Only return default filters.
                foreach($filterList as $filter) {
                    if (!$filter->getData('isOptional')) $finalFilterList[] = $filter;
                    unset($filter);
                }
            }
        } else {
            // If specific filter ids are given then only filters in that
            // list will be returned (even if they are non-default filters).
            foreach($filterList as $filter) {
                if (in_array($filter->getId(), $fromFilterIds)) $finalFilterList[] = $filter;
                unset($filter);
            }
        }

        return $finalFilterList;
    }

    /**
     * Update an existing citation.
     * @param Citation $citation
     */
    public function updateObject($citation) {
        // Update the citation and release the lock
        // on it (if one is present).
        $this->update(
            'UPDATE citations
            SET assoc_type = ?,
                assoc_id = ?,
                citation_state = ?,
                raw_citation = ?,
                seq = ?,
                lock_id = NULL
            WHERE citation_id = ?',
            [
                (int) $citation->getAssocType(),
                (int) $citation->getAssocId(),
                (int) $citation->getCitationState(),
                $citation->getRawCitation(),
                (int) $citation->getSeq(),
                (int) $citation->getId()
            ]
        );
        $this->_updateObjectMetadata($citation);
        $this->updateCitationSourceDescriptions($citation);
    }

    /**
     * Delete a citation.
     * @param Citation $citation
     * @return bool
     */
    public function deleteObject($citation) {
        return $this->deleteObjectById($citation->getId());
    }

    /**
     * Delete a citation by id.
     * @param int $citationId
     * @return bool
     */
    public function deleteObjectById($citationId) {
        assert(!empty($citationId));

        // Delete citation sources
        $metadataDescriptionDao = DAORegistry::getDAO('MetadataDescriptionDAO');
        $metadataDescriptionDao->deleteObjectsByAssocId(ASSOC_TYPE_CITATION, (int) $citationId);

        // Delete citation
        $params = [(int) $citationId];
        $this->update('DELETE FROM citation_settings WHERE citation_id = ?', $params);
        return $this->update('DELETE FROM citations WHERE citation_id = ?', $params);
    }

    /**
     * Delete all citations matching a particular association id.
     * @param int $assocType
     * @param int $assocId
     * @return bool
     */
    public function deleteObjectsByAssocId($assocType, $assocId) {
        $citations = $this->getObjectsByAssocId($assocType, $assocId);
        while (($citation = $citations->next())) {
            $this->deleteObjectById($citation->getId());
            unset($citation);
        }
        return true;
    }

    /**
     * Update the source descriptions of an existing citation.
     * @param Citation $citation
     */
    public function updateCitationSourceDescriptions($citation) {
        $metadataDescriptionDao = DAORegistry::getDAO('MetadataDescriptionDAO');

        // Clear all existing citation sources first
        $citationId = $citation->getId();
        assert(!empty($citationId));
        $metadataDescriptionDao->deleteObjectsByAssocId(ASSOC_TYPE_CITATION, (int) $citationId);

        // Now add the new citation sources
        foreach ($citation->getSourceDescriptions() as $sourceDescription) {
            // Make sure that this source description is correctly associated
            // with the citation so that we can recover it later.
            assert($sourceDescription->getAssocType() == ASSOC_TYPE_CITATION);
            $sourceDescription->setAssocId($citationId);
            $metadataDescriptionDao->insertObject($sourceDescription);
        }
    }

    /**
     * Instantiates the citation output format filter currently
     * configured for the context.
     * @param DataObject $context journal, press or conference
     * @return PersistableFilter
     */
    public function instantiateCitationOutputFilter($context) {
        // The filter is stateless so we can instantiate
        // it once for all requests.
        static $citationOutputFilter = null;
        if ($citationOutputFilter === null) {
            // Retrieve the currently selected citation output
            // filter from the database.
            $citationOutputFilterId = $context->getSetting('metaCitationOutputFilterId');
            $filterDao = DAORegistry::getDAO('FilterDAO');
            $citationOutputFilter = $filterDao->getObjectById($citationOutputFilterId);
            assert($citationOutputFilter instanceof PersistableFilter);

            // We expect a string as output type.
            $filterGroup = $citationOutputFilter->getFilterGroup();
            assert($filterGroup->getOutputType() == 'primitive::string');
        }

        return $citationOutputFilter;
    }

    //
    // Protected helper methods
    //
    /**
     * Get the id of the last inserted citation.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return parent::getInsertId('citations', 'citation_id', $callHooks);
    }


    //
    // Private helper methods
    //
    /**
     * Construct a new citation object.
     * @return Citation
     */
    public function _newDataObject() {
        return new Citation();
    }

    /**
     * Internal function to return a citation object from a row.
     * @param array $row
     * @return Citation
     */
    public function _fromRow($row) {
        $citation = $this->_newDataObject();
        $citation->setId((int)$row['citation_id']);
        $citation->setAssocType((int)$row['assoc_type']);
        $citation->setAssocId((int)$row['assoc_id']);
        $citation->setCitationState($row['citation_state']);
        $citation->setRawCitation($row['raw_citation']);
        $citation->setSeq((int)$row['seq']);

        $this->getDataObjectSettings('citation_settings', 'citation_id', $row['citation_id'], $citation);

        // Add citation source descriptions
        $sourceDescriptions = $this->_getCitationSourceDescriptions($citation->getId());
        while ($sourceDescription = $sourceDescriptions->next()) {
            $citation->addSourceDescription($sourceDescription);
        }

        return $citation;
    }

    /**
     * Update the citation meta-data
     * @param Citation $citation
     */
    public function _updateObjectMetadata($citation) {
        // Persist citation meta-data
        $this->updateDataObjectSettings('citation_settings', $citation,
                ['citation_id' => $citation->getId()]);
    }

    /**
     * Get the source descriptions of an existing citation.
     * @param int $citationId
     * @return DAOResultFactory
     */
    public function _getCitationSourceDescriptions($citationId) {
        $metadataDescriptionDao = DAORegistry::getDAO('MetadataDescriptionDAO');
        $sourceDescriptions = $metadataDescriptionDao->getObjectsByAssocId(ASSOC_TYPE_CITATION, (int) $citationId);
        return $sourceDescriptions;
    }

    /**
     * Instantiates filters that can parse a citation.
     * @param Citation $citation
     * @param MetadataDescription $metadataDescription
     * @param int $contextId
     * @param array $fromFilterIds restrict results to those with the given ids
     * @return array transformation definition
     */
    public function _instantiateParserFilters($citation, $metadataDescription, $contextId, $fromFilterIds) {
        $displayName = 'Citation Parser Filters'; // Only for internal debugging, no display to user.

        // Extract the raw citation string from the citation
        $inputData = $citation->getRawCitation();

        // Instantiate parser filters.
        $filterList = $this->getCitationFilterInstances($contextId, CITATION_PARSER_FILTER_GROUP, $fromFilterIds);

        $transformationDefinition = compact('displayName', 'inputData', 'filterList');
        return $transformationDefinition;
    }

    /**
     * Instantiates filters that can validate and amend citations
     * with information from external data sources.
     * @param Citation $citation
     * @param MetadataDescription $metadataDescription
     * @param int $contextId
     * @param array $fromFilterIds restrict results to those with the given ids
     * @return array transformation definition
     */
    public function _instantiateLookupFilters($citation, $metadataDescription, $contextId, $fromFilterIds) {
        $displayName = 'Citation Lookup Filters'; // Only for internal debugging, no display to user.

        // Define the input for this transformation.
        $inputData = $metadataDescription;

        // Instantiate lookup filters.
        $filterList = $this->getCitationFilterInstances($contextId, CITATION_LOOKUP_FILTER_GROUP, $fromFilterIds);

        $transformationDefinition = compact('displayName', 'inputData', 'filterList');
        return $transformationDefinition;
    }

    /**
     * Call the callback to filter the citation. If errors occur
     * they'll be added to the citation form.
     * @param PKPRequest $request
     * @param Citation $citation
     * @param callable $filterCallback
     * @param int $citationStateAfterFiltering
     * @param array $fromFilterIds only use filters with the given ids
     * @return Citation|null the filtered citation or null if an error occurred
     */
    public function _filterCitation($request, $citation, $filterCallback, $citationStateAfterFiltering, $fromFilterIds = []) {
        // Get the context.
        $router = $request->getRouter();
        $context = $router->getContext($request);
        assert(is_object($context));

        // Make sure that the citation implements only one
        // meta-data schema.
        $supportedMetadataSchemas = $citation->getSupportedMetadataSchemas();
        assert(count($supportedMetadataSchemas) == 1);
        $metadataSchema = $supportedMetadataSchemas[0];

        // Extract the meta-data description from the citation.
        $originalDescription = $citation->extractMetadata($metadataSchema);

        // Let the callback configure the transformation.
        $transformationDefinition = call_user_func($filterCallback, $citation, $originalDescription, $context->getId(), $fromFilterIds);
        $filterList = $transformationDefinition['filterList'];
        
        $filteredCitation = null;
        $citationMultiplexer = null;
        $citationFilterNet = null;

        if (!empty($filterList)) {
            // Get the input into the transformation.
            $muxInputData = $transformationDefinition['inputData'];

            // Get the filter group.
            $filterGroup = $filterList[0]->getFilterGroup(); /* @var $filterGroup FilterGroup */

            // The filter group must be adapted to return an array rather
            // than a scalar value.
            $filterGroup->setOutputType($filterGroup->getOutputType().'[]');

            // Instantiate the citation multiplexer filter.
            import('lib.pkp.classes.filter.GenericMultiplexerFilter');
            $citationMultiplexer = new GenericMultiplexerFilter($filterGroup, $transformationDefinition['displayName']);

            // Don't fail just because one of the web services
            // fails. They are much too unstable to rely on them.
            $citationMultiplexer->setTolerateFailures(true);

            // Add sub-filters to the multiplexer.
            $nullVar = null;
            foreach($filterList as $citationFilter) {
                if ($citationFilter->supports($muxInputData, $nullVar)) {
                    $citationMultiplexer->addFilter($citationFilter);
                    unset($citationFilter);
                }
            }

            // Instantiate the citation de-multiplexer filter.
            import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationDemultiplexerFilter');
            $citationDemultiplexer = new Nlm30CitationDemultiplexerFilter();
            $citationDemultiplexer->setOriginalDescription($originalDescription);
            $citationDemultiplexer->setOriginalRawCitation($citation->getRawCitation());
            $citationDemultiplexer->setCitationOutputFilter($this->instantiateCitationOutputFilter($context));

            // Combine multiplexer and de-multiplexer to form the
            // final citation filter network.
            import('lib.pkp.classes.filter.GenericSequencerFilter');
            $citationFilterNet = new GenericSequencerFilter(
                    PersistableFilter::tempGroup(
                            $filterGroup->getInputType(),
                            'class::lib.pkp.classes.citation.Citation'),
                    'Citation Filter Network');
            $citationFilterNet->addFilter($citationMultiplexer);
            $citationFilterNet->addFilter($citationDemultiplexer);

            // Send the input through the citation filter network.
            $filteredCitation = $citationFilterNet->execute($muxInputData);
        }

        if ($filteredCitation === null) {
            // Return the original citation if the filters
            // did not produce any results and add an error message.
            $filteredCitation = $citation;
            if (!empty($transformationDefinition['filterList'])) {
                $filteredCitation->addError(__('submission.citations.filter.noResultFromFilterError'));
            }
        } else {
            // Copy data from the original citation to the filtered citation.
            $filteredCitation->setId($citation->getId());
            $filteredCitation->setSeq($citation->getSeq());
            $filteredCitation->setRawCitation($citation->getRawCitation());
            $filteredCitation->setAssocId($citation->getAssocId());
            $filteredCitation->setAssocType($citation->getAssocType());
            foreach($citation->getErrors() as $errorMessage) {
                $filteredCitation->addError($errorMessage);
            }
            foreach($citation->getSourceDescriptions() as $sourceDescription) {
                $filteredCitation->addSourceDescription($sourceDescription);
                unset($sourceDescription);
            }
        }

        // Set the target citation state.
        $filteredCitation->setCitationState($citationStateAfterFiltering);

        if ($citationMultiplexer instanceof CompositeFilter) {
            // Retrieve the results of intermediate filters and add
            // them to the citation for inspection by the end user.
            $lastOutput = $citationMultiplexer->getLastOutput();
            if (is_array($lastOutput)) {
                foreach($lastOutput as $sourceDescription) {
                    $filteredCitation->addSourceDescription($sourceDescription);
                    unset($sourceDescription);
                }
            }
        }

        if ($citationFilterNet instanceof CompositeFilter) {
            // Add filtering errors (if any) to the citation's error list.
            foreach($citationFilterNet->getErrors() as $filterError) {
                $filteredCitation->addError($filterError);
            }
        }

        return $filteredCitation;
    }
}

?>