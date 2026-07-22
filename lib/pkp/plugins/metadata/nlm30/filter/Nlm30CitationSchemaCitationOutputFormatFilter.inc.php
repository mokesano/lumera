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
 * @brief Base class for filters that transform NLM citation metadata 
 * descriptions into citation output formats via Smarty templates.
 */

import('lib.pkp.classes.filter.TemplateBasedFilter');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema');

// This is a brand name so doesn't have to be translated...
define('GOOGLE_SCHOLAR_TAG', '[Google Scholar]');

class Nlm30CitationSchemaCitationOutputFormatFilter extends TemplateBasedFilter {
    
    /** @var array|null */
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
     * @param FilterGroup $filterGroup
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
        // [WIZDAM] Modernization: Gunakan strict comparison === null
        if ($this->_supportedPublicationTypes === null) {
            $this->_supportedPublicationTypes = [
                NLM30_PUBLICATION_TYPE_BOOK, 
                NLM30_PUBLICATION_TYPE_JOURNAL, 
                NLM30_PUBLICATION_TYPE_CONFPROC
            ];
        }
        return $this->_supportedPublicationTypes;
    }

    //
    // Implement template methods from Filter
    //
    /**
     * Process the input of NLM meta-data description to be transformed.
     * @see Filter::process()
     * @param MetadataDescription $input
     * @return string
     */
    public function process($input) {
        $supportedPublicationTypes = $this->getSupportedPublicationTypes();
        $inputPublicationType = $input->getStatement('[@publication-type]');
        
        if (!in_array($inputPublicationType, $supportedPublicationTypes, true)) {
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
     * Add template variabels
     * @see TemplateBasedFilter::addTemplateVars()
     * @param TemplateManager $templateMgr
     * @param MetadataDescription $input
     * @param Request $request
     * @param string $locale
     */
    public function addTemplateVars($templateMgr, $input, $request, $locale) {
        $propertyNames = $input->getPropertyNames();
        
        foreach ($propertyNames as $propertyName) {
            $templateVariable = $input->getNamespacedPropertyId($propertyName);
            
            if ($input->hasStatement($propertyName)) {
                $property = $input->getProperty($propertyName);
                $propertyLocale = $property->getTranslated() ? $locale : null;
                
                $value = $input->getStatement($propertyName, $propertyLocale);
                $templateMgr->assign($templateVariable, $value); 
            } else {
                $templateMgr->clear_assign($templateVariable);
            }
        }
    }
    
}
?>