<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30CitationSchemaCitationOutputFormatFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaCitationOutputFormatFilter
 * @ingroup plugins_metadata_nlm30_filter
 *
 * @brief Abstract base class for all filters that transform
 * NLM citation metadata descriptions into citation output formats
 * via smarty template.
 */

import('lib.pkp.classes.filter.TemplateBasedFilter');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema');

// This is a brand name so doesn't have to be translated...
define('GOOGLE_SCHOLAR_TAG', '[Google Scholar]');

class Nlm30CitationSchemaCitationOutputFormatFilter extends TemplateBasedFilter {
    /** @var array */
    protected $_supportedPublicationTypes;

    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup) {
        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30CitationSchemaCitationOutputFormatFilter($filterGroup) {
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
     * Set the supported publication types.
     * @param array $supportedPublicationTypes
     */
    public function setSupportedPublicationTypes($supportedPublicationTypes) {
        $this->_supportedPublicationTypes = $supportedPublicationTypes;
    }

    /**
     * Get the supported publication types.
     * @return array
     */
    public function getSupportedPublicationTypes() {
        if (is_null($this->_supportedPublicationTypes)) {
            // Set default supported publication types.
            $this->_supportedPublicationTypes = [
                NLM30_PUBLICATION_TYPE_BOOK, NLM30_PUBLICATION_TYPE_JOURNAL, NLM30_PUBLICATION_TYPE_CONFPROC
            ];
        }
        return $this->_supportedPublicationTypes;
    }

    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::process()
     * @param MetadataDescription $input the NLM meta-data description
     * to be transformed
     * @return string the rendered citation output
     */
    public function process($input) {
        // Check whether the incoming publication type is supported by this
        // output filter.
        $supportedPublicationTypes = $this->getSupportedPublicationTypes();
        $inputPublicationType = $input->getStatement('[@publication-type]');
        if (!in_array($inputPublicationType, $supportedPublicationTypes)) {
            $this->addError(__('submission.citations.filter.unsupportedPublicationType'));
            return '';
        }

        return parent::process($input);
    }

    //
    // Implement template methods from TemplateBasedFilter
    //
    /**
     * Get the citation template
     * @return string
     */
    public function getTemplateName() {
        return 'nlm-citation.tpl';
    }

    /**
     * @see TemplateBasedFilter::addTemplateVars()
     * @param TemplateManager $templateMgr
     * @param MetadataDescription $input the NLM meta-data description
     * to be transformed
     * @param Request $request
     * @param string $locale AppLocale
     */
    public function addTemplateVars($templateMgr, $input, $request, $locale) {
        // Loop over the statements in the schema and add them
        // to the template
        $propertyNames = $input->getPropertyNames();
        $setProperties = [];
        foreach($propertyNames as $propertyName) {
            $templateVariable = $input->getNamespacedPropertyId($propertyName);
            if ($input->hasStatement($propertyName)) {
                $property = $input->getProperty($propertyName);
                $propertyLocale = $property->getTranslated() ? $locale : null;
                // Assign by reference not strictly required for scalar/simple values but often used in older Smarty integration
                // We keep assign_by_ref or change to assign depending on framework version. Assuming OJS 2.x/3.x legacy uses assign_by_ref.
                // However, getting statement returns value, not reference usually.
                // If getStatement returns by ref in core, we might need &
                $value = $input->getStatement($propertyName, $propertyLocale);
                $templateMgr->assign($templateVariable, $value); 
                unset($property);
            } else {
                // Delete potential leftovers from previous calls
                $templateMgr->clear_assign($templateVariable);
            }
        }
    }
}
?>