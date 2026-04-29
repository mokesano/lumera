<?php
declare(strict_types=1);

/**
 * @file classes/metadata/XSLTransformationFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XSLTransformationFilter
 * @ingroup xslt
 *
 * @brief Class that transforms XML via XSL.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility)
 */

import('lib.pkp.classes.filter.PersistableFilter');
import('lib.pkp.classes.xslt.XSLTransformer');

class XSLTransformationFilter extends PersistableFilter {
    
    /**
     * Constructor
     */
    public function __construct($filterGroup, $displayName = 'XSL Transformation') {
        // Check that we only get xml input, the output type is arbitrary.
        if (!substr($filterGroup->getInputType(), 0, 5) == 'xml::') fatalError('XSL filters need XML as input.');

        // Instantiate the settings of this filter
        import('lib.pkp.classes.filter.FilterSetting');
        $this->addSetting(new FilterSetting('xsl', null, null));
        $this->addSetting(new FilterSetting('xslType', null, null));
        $this->addSetting(new FilterSetting('resultType', null, null, FORM_VALIDATOR_OPTIONAL_VALUE));

        $this->setDisplayName($displayName);

        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XSLTransformationFilter($filterGroup, $displayName = 'XSL Transformation') {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::XSLTransformationFilter(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($filterGroup, $displayName);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the XSL
     * @return DOMDocument|string a document, xsl string or file name
     */
    public function getXSL() {
        return $this->getData('xsl');
    }

    /**
     * Get the XSL Type
     * @return integer
     */
    public function getXSLType() {
        return $this->getData('xslType');
    }

    /**
     * Set the XSL
     * @param $xsl DOMDocument|string
     */
    public function setXSL($xsl) {
        // Determine the xsl type
        if (is_string($xsl)) {
            $this->setData('xslType', XSL_TRANSFORMER_DOCTYPE_STRING);
        } elseif (is_a($xsl, 'DOMDocument')) {
            $this->setData('xslType', XSL_TRANSFORMER_DOCTYPE_DOM);
        } else assert(false);

        $this->setData('xsl', $xsl);
    }

    /**
     * Set the XSL as a file name
     * @param unknown_type $xslFile
     */
    public function setXSLFilename($xslFile) {
        $this->setData('xslType', XSL_TRANSFORMER_DOCTYPE_FILE);
        $this->setData('xsl', $xslFile);
    }

    /**
     * Get the result type
     * @return integer
     */
    public function getResultType() {
        return $this->getData('resultType');
    }

    /**
     * Set the result type
     * @param $resultType integer
     */
    public function setResultType($resultType) {
        $this->setData('resultType', $resultType);
    }


    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @see PersistableFilter::getClassName()
     */
    public function getClassName() {
        return 'lib.pkp.classes.xslt.XSLTransformationFilter';
    }


    //
    // Implement template methods from Filter
    //
    /**
     * Process the given XML with the configured XSL
     * @see Filter::process()
     * @param $xml DOMDocument|string
     * @return DOMDocument|string
     */
    public function process($xml) {
        // Determine the input type
        if (is_string($xml)) {
            $xmlType = XSL_TRANSFORMER_DOCTYPE_STRING;
        } elseif (is_a($xml, 'DOMDocument')) {
            $xmlType = XSL_TRANSFORMER_DOCTYPE_DOM;
        } else assert(false);

        // Determine the result type based on
        // the input type if it has not been
        // set explicitly.
        if (is_null($this->getResultType())) {
            $this->setResultType($xmlType);
        }

        // Transform the input
        $xslTransformer = new XSLTransformer();
        // WIZDAM FIX: Removed reference assignment
        $result = $xslTransformer->transform($xml, $xmlType, $this->getXsl(), $this->getXslType(), $this->getResultType());
        return $result;
    }
}
?>