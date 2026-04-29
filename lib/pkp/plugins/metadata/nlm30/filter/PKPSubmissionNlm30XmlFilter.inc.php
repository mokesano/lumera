<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/PKPSubmissionNlm30XmlFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPSubmissionNlm30XmlFilter
 * @ingroup plugins_metadata_nlm30_filter
 *
 * @brief Class that converts a submission to an NLM Journal Publishing
 * Tag Set 3.0 XML document.
 *
 * FIXME: This class currently only generates partial (citation) NLM XML output.
 * Full NLM journal publishing tag set support still has to be added, see #5648
 * and the L8X development roadmap.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.citation.TemplateBasedReferencesListFilter');

class PKPSubmissionNlm30XmlFilter extends TemplateBasedReferencesListFilter {
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup) {
        $this->setDisplayName('NLM Journal Publishing V3.0 ref-list');

        parent::__construct($filterGroup);

        // Set the output filter.
        $this->setData('citationOutputFilterName', 'lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaNlm30XmlFilter');
        // Set the metadata schema.
        $this->setData('metadataSchemaName', 'lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPSubmissionNlm30XmlFilter($filterGroup) {
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
    // Implement template methods from TemplateBasedReferencesListFilter
    //
    /**
     * @see TemplateBasedReferencesListFilter::getCitationOutputFilterTypeDescriptions()
     * @return array
     */
    public function getCitationOutputFilterTypeDescriptions() {
        // FIXME: Add NLM citation-element + name validation (requires partial NLM DTD, XSD or RelaxNG), see #5648.
        return [
            'metadata::lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema(CITATION)',
            'xml::*'
        ];
    }

    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @see PersistableFilter::getClassName()
     * @return string
     */
    public function getClassName() {
        return 'lib.pkp.plugins.metadata.nlm30.filter.PKPSubmissionNlm30XmlFilter';
    }

    //
    // Implement template methods from TemplateBasedFilter
    //
    /**
     * @see TemplateBasedFilter::getTemplateName()
     * @return string
     */
    public function getTemplateName() {
        return 'nlm30-ref-list.tpl';
    }

    /**
     * @see TemplateBasedFilter::getBasePath()
     * @return string
     */
    public function getBasePath() {
        return __DIR__;
    }
}
?>