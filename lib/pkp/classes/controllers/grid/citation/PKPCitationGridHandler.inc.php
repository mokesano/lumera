<?php
declare(strict_types=1);

/**
 * @file controllers/grid/citation/PKPCitationGridHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPCitationGridHandler
 * @ingroup controllers_grid_citation
 *
 * @brief Handle generic parts of citation grid requests.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.citation.PKPCitationGridCellProvider');

// import citation grid specific classes
import('lib.pkp.classes.controllers.grid.citation.PKPCitationGridRow');

class PKPCitationGridHandler extends GridHandler {
    /** @var DataObject|null */
    protected ?DataObject $assocObject = null;

    /** @var int */
    protected int $assocType = 0;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPCitationGridHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }


    //
    // Getters and Setters
    //
    
    /**
     * Set the object that citations are associated to
     * This object must implement the getId() and getCitations() methods.
     *
     * @param DataObject $assocObject
     */
    public function setAssocObject($assocObject) {
        $this->assocObject = $assocObject;
    }

    /**
     * Get the object that citations are associated to.
     * @return DataObject|null
     */
    public function getAssocObject(): ?DataObject {
        return $this->assocObject;
    }

    /**
     * Set the type of the object that citations are associated to.
     * @param int $assocType one of the ASSOC_TYPE_* constants
     */
    public function setAssocType($assocType) {
        $this->assocType = (int)$assocType;
    }

    /**
     * Get the type of the object that citations are associated to.
     * @return int one of the ASSOC_TYPE_* constants
     */
    public function getAssocType(): int {
        return $this->assocType;
    }

    /**
     * Get the assoc id
     * @return int|mixed
     */
    public function getAssocId() {
        $assocObject = $this->getAssocObject();
        return $assocObject ? $assocObject->getId() : 0;
    }


    //
    // Overridden methods from PKPHandler
    //
    
    /**
     * Configure the grid
     * @see PKPHandler::initialize()
     */
    public function initialize($request, $args = null) {
        parent::initialize($request, $args);

        // Load submission-specific translations
        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

        // Basic grid configuration
        $this->setTitle('submission.citations.editor.citationlist.title');

        // Retrieve the associated citations to be displayed in the grid.
        // Only citations that have already been parsed will be displayed.
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $data = $citationDao->getObjectsByAssocId($this->getAssocType(), $this->getAssocId(), CITATION_PARSED);
        $this->setGridDataElements($data);

        // If the refresh flag is set in the request then trigger
        // citation parsing.
        if (isset($args['refresh'])) {
            $noOfProcesses = (int)Config::getVar('general', 'citation_checking_max_processes');
            $processDao = DAORegistry::getDAO('ProcessDAO');
            $processDao->spawnProcesses($request, 'api.citation.CitationApiHandler', 'checkAllCitations', PROCESS_TYPE_CITATION_CHECKING, $noOfProcesses);
        }

        // Grid actions
        $router = $request->getRouter();
        $this->addAction(
            new LegacyLinkAction(
                'addCitation',
                LINK_ACTION_MODE_AJAX,
                LINK_ACTION_TYPE_GET,
                $router->url(
                    $request, null, null, 'addCitation', null,
                    ['assocId' => $this->getAssocId()]
                ),
                'submission.citations.editor.citationlist.newCitation', null, 'add', null,
                'citationEditorDetailCanvas'
            ),
            GRID_ACTION_POSITION_LASTCOL
        );

        // Columns
        $cellProvider = new PKPCitationGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'rawCitation',
                null,
                false,
                'controllers/grid/citation/citationGridCell.tpl',
                $cellProvider,
                ['multiline' => true]
            )
        );
    }


    //
    // Overridden methods from GridHandler
    //
    
    /**
     * @see GridHandler::getRowInstance()
     */
    protected function getRowInstance(): GridRow {
        // Return a citation row
        return new PKPCitationGridRow();
    }

    /**
     * @see GridHandler::getIsSubcomponent()
     */
    public function getIsSubcomponent(): bool {
        return true;
    }


    //
    // Public grid actions
    //
    
    /**
     * Export a list of formatted citations
     * @param array $args
     * @param Request $request
     * @param string $noCitationsFoundMessage an app-specific help message
     * @return string a serialized JSON message
     */
    public function exportCitations($args, $request, $noCitationsFoundMessage) {
        $router = $request->getRouter();
        // Context not strictly used but good for consistency
        // $context = $router->getContext($request); 
        $templateMgr = TemplateManager::getManager($request);

        $errorMessage = null;
        $citations = $this->getGridDataElements($request);
        
        if (empty($citations)) {
            $errorMessage = $noCitationsFoundMessage;
        } else {
            // Check whether we have any unapproved citations.
            foreach($citations as $citation) {
                // Retrieve NLM citation meta-data
                if ($citation->getCitationState() < CITATION_APPROVED) {
                    $errorMessage = __('submission.citations.editor.export.foundUnapprovedCitationsMessage');
                    break;
                }
            }

            // Only go on when we've no error so far
            if ($errorMessage === null) {
                // Provide the assoc id to the template.
                $templateMgr->assign('assocId', $this->getAssocId());

                // Identify export filters.
                $filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
                $allowedFilterIds = [];

                // Retrieve export filters.
                $exportFilter = null;
                $exportFilters = [];
                $exportFilterConfiguration = $this->_getExportFilterConfiguration();
                
                foreach($exportFilterConfiguration as $selectListHeading => $outputType) {
                    // All filters that take a submission and one of the supported output types
                    $exportFilterObjects = $filterDao->getObjectsByTypeDescription('class::lib.pkp.classes.submission.Submission', $outputType);

                    // Build the array for the template.
                    $exportFilters[$selectListHeading] = [];
                    foreach($exportFilterObjects as $exportFilterObject) { /* @var $exportFilterObject PersistableFilter */
                        $filterId = $exportFilterObject->getId();

                        // Use the first filter as default export filter.
                        if ($exportFilter === null) {
                            $exportFilter = $exportFilterObject;
                            $exportFilterId = $filterId;
                        }

                        // FIXME: Move &nbsp; to the template.
                        $exportFilters[$selectListHeading][$filterId] = '&nbsp;'.$exportFilterObject->getDisplayName();
                        $allowedFilterIds[$filterId] = $outputType;
                    }
                }
                $templateMgr->assign('exportFilters', $exportFilters);

                // Did the user choose a custom filter?
                if (isset($args['filterId'])) {
                    $exportFilterId = (int)$args['filterId'];
                    if (isset($allowedFilterIds[$exportFilterId])) {
                        $exportFilter = $filterDao->getObjectById($exportFilterId);
                    }
                }

                // Prepare the export output if a filter has been identified.
                $exportOutputString = '';
                if ($exportFilter instanceof Filter) {
                    // Make the template aware of the selected filter.
                    $templateMgr->assign('exportFilterId', $exportFilterId);

                    // Save the export filter type to the template.
                    $exportType = $allowedFilterIds[$exportFilterId];
                    $templateMgr->assign('exportFilterType', $exportType);

                    // Apply the citation output format filter.
                    $exportOutput = $exportFilter->execute($this->getAssocObject());

                    // Generate an error message if the export was not successful.
                    if (empty($exportOutput)) {
                        $errorMessage = __('submission.citations.editor.export.noExportOutput', ['filterName' => $exportFilter->getDisplayName()]);
                    }

                    if ($errorMessage === null) {
                        switch (substr($exportType, 0, 5)) {
                            case 'xml::':
                                // Pretty-format XML output.
                                $xmlDom = new DOMDocument();
                                $xmlDom->preserveWhiteSpace = false;
                                $xmlDom->formatOutput = true;
                                $xmlDom->loadXml($exportOutput);
                                // saveXml requires the node if we want strictness, but documentElement is standard
                                $exportOutputString = $xmlDom->saveXml($xmlDom->documentElement);
                                break;

                            default:
                                assert($exportOutput instanceof PlainTextReferencesList);
                                $exportOutputString = $exportOutput->getListContent();
                        }
                    }
                }
                $templateMgr->assign('exportOutput', $exportOutputString);
            }
        }

        // Render the citation list
        $templateMgr->assign('errorMessage', $errorMessage);
        return $templateMgr->fetchJson('controllers/grid/citation/citationExport.tpl');
    }

    /**
     * An action to manually add a new citation
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function addCitation($args, $request) {
        // Calling editCitation() with an empty row id will add a new citation.
        return $this->editCitation($args, $request);
    }

    /**
     * Edit a citation
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function editCitation($args, $request) {
        // Identify the citation to be edited
        $citation = $this->getCitationFromArgs($request, $args, true);

        // Form handling
        import('lib.pkp.classes.controllers.grid.citation.form.CitationForm');
        $citationForm = new CitationForm($request, $citation, $this->getAssocObject());
        if ($citationForm->isLocaleResubmit()) {
            $citationForm->readInputData();
        } else {
            $citationForm->initData();
        }
        $json = new JSONMessage(true, $citationForm->fetch($request));
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Change the raw text of a citation and re-process it.
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function updateRawCitation($args, $request) {
        // Retrieve the citation to be changed from the database.
        $citation = $this->getCitationFromArgs($request, $args, true);

        // Now retrieve the raw citation from the request.
        // [SECURITY FIX] Amankan 'rawCitation' dengan trim() sebelum strip_tags
        $rawCitation = trim((string) $request->getUserVar('rawCitation'));
        $citation->setRawCitation(strip_tags($rawCitation));

        // Resetting the citation state to "raw" will trigger re-parsing.
        $citation->setCitationState(CITATION_RAW);

        return $this->_recheckCitation($request, $citation, false);
    }

    /**
     * Check (parse and lookup) a citation
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function checkCitation($args, $request) {
        if ($request->isPost()) {
            // We update the citation with the user's manual settings
            $citationForm = $this->_handleCitationForm($args, $request);

            if (!$citationForm->isValid()) {
                // The citation cannot be persisted, so we cannot process it.
                $json = new JSONMessage(false, $citationForm->fetch($request));
                header('Content-Type: application/json');
                return $json->getString();
            }

            // We retrieve the citation to be checked from the form.
            $originalCitation = $citationForm->getCitation();
            unset($citationForm);
        } else {
            // We retrieve the citation to be checked from the database.
            $originalCitation = $this->getCitationFromArgs($request, $args, true);
        }

        return $this->_recheckCitation($request, $originalCitation, false);
    }

    /**
     * Update a citation
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function updateCitation($args, $request) {
        // Try to persist the data in the request.
        $citationForm = $this->_handleCitationForm($args, $request);
        
        if (!$citationForm->isValid()) {
            // Re-display the citation form with error messages
            $json = new JSONMessage(false, $citationForm->fetch($request));
        } else {
            // Get the persisted citation from the form.
            $savedCitation = $citationForm->getCitation();

            // If the citation is not yet parsed then parse it now
            if ($savedCitation->getCitationState() < CITATION_PARSED) {
                // Assert that this is a new citation.
                assert(!isset($args['citationId']));
                $savedCitation = $this->_recheckCitation($request, $savedCitation, true);
                assert($savedCitation instanceof Citation);
            }

            // Update the citation's grid row.
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($savedCitation->getId());
            $row->setData($savedCitation);
            if (isset($args['remainsCurrentItem']) && $args['remainsCurrentItem'] == 'yes') {
                $row->setIsCurrentItem(true);
            }
            $row->initialize($request);

            // Render the row into a JSON response
            $json = new JSONMessage(true, $this->renderRowInternally($request, $row));
        }

        // Return the serialized JSON response
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Delete a citation
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function deleteCitation($args, $request) {
        // Identify the citation to be deleted
        $citation = $this->getCitationFromArgs($request, $args);

        $citationDao = DAORegistry::getDAO('CitationDAO');
        $result = $citationDao->deleteObject($citation);

        if ($result) {
            $json = new JSONMessage(true);
        } else {
            $json = new JSONMessage(false, __('submission.citations.editor.citationlist.errorDeletingCitation'));
        }
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Fetch the posted citation as a citation string with
     * calculated differences between the field based and the
     * raw version.
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function fetchCitationFormErrorsAndComparison($args, $request) {
        // Read the data in the request into the form without persisting the data.
        $citationForm = $this->_handleCitationForm($args, $request, false);

        // Render the form with the citation diff.
        $output = $citationForm->fetch($request, CITATION_FORM_COMPARISON_TEMPLATE);

        // Render the row into a JSON response
        $json = new JSONMessage(true, $output);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Send an author query based on the posted data.
     * @param array $args
     * @param Request $request
     * @return string a serialized JSON message
     */
    public function sendAuthorQuery($args, $request) {
        // Instantiate the email to the author.
        import('lib.pkp.classes.mail.Mail');
        $mail = new Mail();

        // Recipient
        $assocObject = $this->getAssocObject();
        $author = $assocObject->getUser();
        $mail->addRecipient($author->getEmail(), $author->getFullName());

        // The message
        // [SECURITY FIX] Amankan 'authorQuerySubject' dengan trim() sebelum strip_tags (Line 501)
        $subjectInput = trim((string) $request->getUserVar('authorQuerySubject'));
        $mail->setSubject(strip_tags($subjectInput));
        
        // [SECURITY FIX] Amankan 'authorQueryBody' dengan trim() sebelum strip_tags (Line 502)
        $bodyInput = trim((string) $request->getUserVar('authorQueryBody'));
        $mail->setBody(strip_tags($bodyInput));

        $mail->send();

        // In principle we should use a template here but this seems exaggerated
        // for such a small message.
        $json = new JSONMessage(true,
            '<div id="authorQueryResult"><span class="pkp_form_error">'
            .__('submission.citations.editor.details.sendAuthorQuerySuccess')
            .'</span></div>');
        header('Content-Type: application/json');
        return $json->getString();
    }


    //
    // Protected helper methods
    //
    
    /**
     * This will retrieve a citation object from the
     * grids data source based on the request arguments.
     * @param Request $request
     * @param array $args
     * @param bool $createIfMissing
     * @return Citation|null
     */
    protected function getCitationFromArgs($request, $args, $createIfMissing = false) {
        // Identify the citation id and retrieve the
        // corresponding element from the grid's data source.
        if (isset($args['citationId'])) {
            $citation = $this->getRowDataElement($request, $args['citationId']);
            if ($citation === null) fatalError('Invalid citation id!');
        } else {
            if ($createIfMissing) {
                // It seems that a new citation is being edited/updated
                import('lib.pkp.classes.citation.Citation');
                $citation = new Citation();
                $citation->setAssocType($this->getAssocType());
                $citation->setAssocId($this->getAssocId());
            } else {
                fatalError('Missing citation id!');
            }
        }
        return $citation;
    }

    //
    // Private helper methods
    //
    
    /**
     * This method returns the texts and filter groups that should be
     * presented for citation reference list export.
     * @return array
     */
    private function _getExportFilterConfiguration(): array {
        return [
            'submission.citations.editor.export.pleaseSelectXmlFilter' => 'xml::%',
            'submission.citations.editor.export.pleaseSelectPlaintextFilter' => 'class::lib.pkp.classes.citation.PlainTextReferencesList'
        ];
    }

    /**
     * Create and validate a citation form with POST
     * request data and (optionally) persist the citation.
     * @param array $args
     * @param Request $request
     * @param bool $persist
     * @return CitationForm the citation form for further processing
     */
    private function _handleCitationForm($args, $request, $persist = true) {
        if(!$request->isPost()) fatalError('Cannot update citation via GET request!');

        // Identify the citation to be updated
        $citation = $this->getCitationFromArgs($request, $args, true);

        // Form initialization
        import('lib.pkp.classes.controllers.grid.citation.form.CitationForm');
        $citationForm = new CitationForm($request, $citation, $this->getAssocObject());
        $citationForm->readInputData();

        // Form validation
        if ($citationForm->validate() && $persist) {
            // Persist the citation.
            $citationForm->execute();
        } else {
            // Mark the citation form "dirty".
            $citationForm->setUnsavedChanges(true);
        }
        return $citationForm;
    }


    /**
     * Internal method that re-checks the given citation and
     * returns a rendered citation editing form with the changes.
     * @param Request $request
     * @param Citation $originalCitation
     * @param bool $persist whether to save (true) or render (false)
     * @return string|Citation|DataObject a serialized JSON message or object
     */
    private function _recheckCitation($request, $originalCitation, $persist = true) {
        // Extract filters to be applied from request
        $requestedFilters = $request->getUserVar('citationFilters');
        $filterIds = [];
        if (is_array($requestedFilters)) {
            foreach($requestedFilters as $filterId => $value) {
                // [SECURITY FIX] Amankan KEY ARRAY ($filterId) dengan trim() sebelum (int)
                $filterIds[] = (int) trim((string)$filterId); 
            }
        }

        // Do the actual filtering of the citation.
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $filteredCitation = $citationDao->checkCitation($request, $originalCitation, $filterIds);

        // Crate a new form for the filtered (but yet unsaved) citation data
        import('lib.pkp.classes.controllers.grid.citation.form.CitationForm');
        $citationForm = new CitationForm($request, $filteredCitation, $this->getAssocObject());

        // Transport filtering errors to form (if any).
        foreach($filteredCitation->getErrors() as $index => $errorMessage) {
            $citationForm->addError('rawCitation['.$index.']', $errorMessage);
        }

        if ($persist) {
            // Persist the checked citation.
            $citationDao->updateObject($filteredCitation);

            // Return the persisted citation.
            return $filteredCitation;
        } else {
            // Only persist intermediate results.
            $citationDao->updateCitationSourceDescriptions($filteredCitation);

            // Mark the citation form "dirty".
            $citationForm->setUnsavedChanges(true);

            // Return the rendered form.
            $citationForm->initData();
            $json = new JSONMessage(true, $citationForm->fetch($request));
            header('Content-Type: application/json');
            return $json->getString();
        }
    }
}

?>