<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30CitationSchemaNlm30XmlFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaNlm30XmlFilter
 * @ingroup plugins_metadata_nlm30_filter
 *
 * @brief Filter that transforms NLM citation metadata descriptions into
 * NLM 3.0 XML citation output.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaCitationOutputFormatFilter');

class Nlm30CitationSchemaNlm30XmlFilter extends Nlm30CitationSchemaCitationOutputFormatFilter {
    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup) {
        $this->setDisplayName('NLM 3.0 XML Citation Output');
        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30CitationSchemaNlm30XmlFilter($filterGroup) {
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
    // Implement template methods from PersistableFilter
    //
    /**
     * @see PersistableFilter::getClassName()
     * @return string
     */
    public function getClassName() {
        return 'lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaNlm30XmlFilter';
    }

    //
    // Implement abstract template methods from TemplateBasedFilter
    //
    /**
     * @see TemplateBasedFilter::addTemplateVars()
     * [WIZDAM FIX] Removed references (&) to match parent signature compatibility in PHP 8
     * @param TemplateManager $templateMgr
     * @param mixed $input
     * @param PKPRequest $request
     * @param string $locale
     */
    public function addTemplateVars($templateMgr, $input, $request, &$locale) {
        // Assign the full meta-data description.
        $templateMgr->assign('metadataDescription', $input);

        parent::addTemplateVars($templateMgr, $input, $request, $locale);
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