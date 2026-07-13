<?php
declare(strict_types=1);

/**
 * @file classes/filter/TemplateBasedFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemplateBasedFilter
 * @ingroup classes_filter
 *
 * @brief Abstract base class for all filters that transform
 * their input via smarty templates.
 */

import('lib.pkp.classes.filter.PersistableFilter');

class TemplateBasedFilter extends PersistableFilter {
    
    /**
     * Constructor
     * @param mixed $filterGroup FilterGroup
     */
    public function __construct($filterGroup) {
        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $filterGroup
     */
    public function TemplateBasedFilter($filterGroup) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::TemplateBasedFilter(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($filterGroup);
    }

    //
    // Abstract template methods
    //

    /**
     * Return the base path of the filter so that we can find the filter templates.
     *
     * @return string
     */
    public function getBasePath() {
        assert(false);
    }

    /**
     * Return the template name to be used by this filter.
     * 
     * @return string
     */
    public function getTemplateName() {
        assert(false);
    }

    /**
     * Sub-classes must implement this method to add template variables to the template.
     * 
     * @param mixed $templateMgr TemplateManager
     * @param mixed $input the filter input
     * @param mixed $request Request
     * @param mixed $locale AppLocale
     */
    public function addTemplateVars($templateMgr, $input, $request, $locale) {
        assert(false);
    }

    //
    // Implement template methods from Filter
    //
    
    /**
     * Transform the input using a Smarty template and return the rendered output.
     * 
     * @see Filter::process()
     * @param mixed $input The data to be processed by the template.
     * @return string The rendered template output.
     */
    public function process($input) {
        $locale = AppLocale::getLocale();
        $application = PKPApplication::getApplication();
        $request = $application->getRequest();
        
        /** @var TemplateManager $templateMgr */
        $templateMgr = TemplateManager::getManager($request);

        $basePath = (string) $this->getBasePath();

        // Internal coercion: Ensure template_dir is an array to prevent PHP 8 TypeError and resolve Intelephense P1006
        $templateDirs = is_array($templateMgr->template_dir) ? $templateMgr->template_dir : [(string) $templateMgr->template_dir];
        array_unshift($templateDirs, $basePath);
        $templateMgr->template_dir = $templateDirs;

        $this->addTemplateVars($templateMgr, $input, $request, $locale);

        $previousCompileId = $templateMgr->compile_id;
        $templateMgr->compile_id = md5($basePath);

        $templateName = (string) $this->getTemplateName();
        $output = (string) $templateMgr->fetch($templateName);

        array_shift($templateMgr->template_dir);
        $templateMgr->compile_id = $previousCompileId;

        return $output;
    }

}
?>