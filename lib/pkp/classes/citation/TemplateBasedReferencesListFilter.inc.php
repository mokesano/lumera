<?php
declare(strict_types=1);

/**
 * @file classes/citation/TemplateBasedReferencesListFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemplateBasedReferencesListFilter
 * @ingroup classes_citation
 *
 * @brief Abstract base class for filters that create a references
 * list for a submission.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.filter.TemplateBasedFilter');

class TemplateBasedReferencesListFilter extends TemplateBasedFilter {
    
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup) {
        // Add the persistable filter settings.
        import('lib.pkp.classes.filter.FilterSetting');
        $this->addSetting(new FilterSetting('citationOutputFilterName', null, null));
        $this->addSetting(new FilterSetting('metadataSchemaName', null, null));

        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TemplateBasedReferencesListFilter() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the metadata schema being used to extract
     * data from the citations.
     * @return MetadataSchema
     */
    public function getMetadataSchema() {
        $metadataSchemaName = $this->getData('metadataSchemaName');
        assert($metadataSchemaName !== null);
        $metadataSchema = instantiate($metadataSchemaName, 'MetadataSchema');
        return $metadataSchema;
    }

    /**
     * Retrieve the citation output filter that will be
     * used to transform citations.
     * @return TemplateBasedFilter
     */
    public function getCitationOutputFilterInstance() {
        $citationOutputFilterName = $this->getData('citationOutputFilterName');
        assert($citationOutputFilterName !== null);
        
        // [WIZDAM FIX] Ensure list() receives array
        $typeDescriptions = $this->getCitationOutputFilterTypeDescriptions();
        list($inputTypeDescription, $outputTypeDescription) = $typeDescriptions;
        
        $filterGroup = PersistableFilter::tempGroup($inputTypeDescription, $outputTypeDescription);
        $citationOutputFilter = instantiate($citationOutputFilterName, 'TemplateBasedFilter', null, null, $filterGroup);
        return $citationOutputFilter;
    }


    //
    // Abstract template methods to be implemented by sub-classes.
    //
    /**
     * Return an input and output type description that
     * describes the transformation implemented by the citation
     * output filter.
     * @return array
     */
    public function getCitationOutputFilterTypeDescriptions() {
        assert(false);
        return []; // Fail-safe return
    }


    //
    // Implement template methods from TemplateBasedFilter
    //
    /**
     * @see TemplateBasedFilter::addTemplateVars()
     * @param PKPTemplateManager $templateMgr
     * @param Submission $submission
     * @param PKPRequest $request
     * @param AppLocale $locale
     */
    public function addTemplateVars($templateMgr, $submission, $request, $locale) {
        // Retrieve assoc type and id of the submission.
        $assocId = (int) $submission->getId();
        $assocType = (int) $submission->getAssocType();

        // Retrieve approved citations for this assoc object.
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $citationResults = $citationDao->getObjectsByAssocId($assocType, $assocId, CITATION_APPROVED);
        $citations = $citationResults->toAssociativeArray('seq');

        // Create citation output for these citations.
        $metadataSchema = $this->getMetadataSchema();
        assert($metadataSchema instanceof MetadataSchema);
        
        $citationOutputFilter = $this->getCitationOutputFilterInstance();
        $citationsOutput = [];
        
        foreach($citations as $seq => $citation) {
            $citationMetadata = $citation->extractMetadata($metadataSchema);
            $citationsOutput[$seq] = $citationOutputFilter->execute($citationMetadata);
        }

        // Add citation mark-up and submission to template.
        // [WIZDAM FIX] Changed assign_by_ref to assign for better compatibility
        $templateMgr->assign('citationsOutput', $citationsOutput);
        $templateMgr->assign('submission', $submission);
    }
}

?>