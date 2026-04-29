<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/setup/JournalSetupStep3Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSetupStep3Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 3 of journal setup.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.manager.form.setup.JournalSetupForm');

class JournalSetupStep3Form extends JournalSetupForm {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            3,
            [
                'authorGuidelines' => 'string',
                'submissionChecklist' => 'object',
                'copyrightNotice' => 'string',
                'includeCopyrightStatement' => 'bool',
                'licenseURL' => 'string',
                'includeLicense' => 'bool',
                'copyrightNoticeAgree' => 'bool',
                'copyrightHolderType' => 'string',
                'copyrightHolderOther' => 'string',
                'copyrightYearBasis' => 'string',
                'requireAuthorCompetingInterests' => 'bool',
                'requireReviewerCompetingInterests' => 'bool',
                'competingInterestGuidelines' => 'string',
                'metaDiscipline' => 'bool',
                'metaDisciplineExamples' => 'string',
                'metaSubjectClass' => 'bool',
                'metaSubjectClassTitle' => 'string',
                'metaSubjectClassUrl' => 'string',
                'metaSubject' => 'bool',
                'metaSubjectExamples' => 'string',
                'metaCoverage' => 'bool',
                'metaCoverageGeoExamples' => 'string',
                'metaCoverageChronExamples' => 'string',
                'metaCoverageResearchSampleExamples' => 'string',
                'metaType' => 'bool',
                'metaTypeExamples' => 'string',
                'metaCitations' => 'bool',
                'metaCitationOutputFilterId' => 'int',
                'copySubmissionAckPrimaryContact' => 'bool',
                'copySubmissionAckSpecified' => 'bool',
                'copySubmissionAckAddress' => 'string'
            ]
        );

        $this->addCheck(new FormValidatorEmail($this, 'copySubmissionAckAddress', 'optional', 'user.profile.form.emailRequired'));
        
        // Only check the subject classification URL if the subject classification is enabled
        // [WIZDAM] Replaced create_function with Anonymous Function
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'metaSubjectClassUrl', 
            'optional', 
            'manager.setup.subjectClassificationURLValid', 
            function($localeUrl, $form, $field, $type, $message) {
                if (!$form->getData("metaSubjectClass")) return true; 
                $f = new FormValidatorLocaleUrl($form, $field, $type, $message); 
                return $f->isValid();
            }, 
            [$this, 'metaSubjectClassUrl', 'optional', 'manager.setup.subjectClassificationURLValid']
        ));
        
        $this->addCheck(new FormValidatorURL($this, 'licenseURL', 'optional', 'submission.licenseURLValid'));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSetupStep3Form() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the list of field names for which localized settings are used.
     * @return array
     */
    public function getLocaleFieldNames() {
        return [
            'authorGuidelines', 
            'submissionChecklist', 
            'copyrightNotice', 
            'metaDisciplineExamples', 
            'metaSubjectClassTitle', 
            'metaSubjectClassUrl', 
            'metaSubjectExamples', 
            'metaCoverageGeoExamples', 
            'metaCoverageChronExamples', 
            'metaCoverageResearchSampleExamples', 
            'metaTypeExamples', 
            'competingInterestGuidelines', 
            'copyrightHolderOther'
        ];
    }

    /**
     * Display the form.
     * [WIZDAM MODERNIZED]
     * - Removed obsolete PHP 5.0 check.
     * - Fixed Logic: Unconditional execution of Filter Grids.
     * - Type Safe: Handles Dispatcher correctly to generate URLs.
     *
     * @param PKPRequest|null $request 
     * @param Dispatcher|null $dispatcher 
     */
    public function display($request = null, $dispatcher = null) {
        // 1. Singleton Fallback (Modern PHP 7.4+ Coalescing)
        $request = $request ?? Application::get()->getRequest();
        $dispatcher = $dispatcher ?? Application::get()->getDispatcher();

        $templateMgr = TemplateManager::getManager($request);
        
        // Load Assets
        $templateMgr->addStyleSheet($request->getBaseUrl().'/styles/ojs.css');
        $templateMgr->addJavaScript('lib/pkp/js/functions/modal.js');
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/validate/jquery.validate.min.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/jqueryValidatorI18n.js');

        // Mail Logic
        import('classes.mail.MailTemplate');
        $mail = new MailTemplate('SUBMISSION_ACK');
        if ($mail->isEnabled()) {
            $templateMgr->assign('submissionAckEnabled', true);
        }

        // 1. Assign URLs for the Grids (Citation Parsing & Lookup)
        if ($dispatcher) {
            $parserFilterGridUrl = $dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.filter.ParserFilterGridHandler', 'fetchGrid');
            $lookupFilterGridUrl = $dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.filter.LookupFilterGridHandler', 'fetchGrid');
            
            $templateMgr->assign('parserFilterGridUrl', $parserFilterGridUrl);
            $templateMgr->assign('lookupFilterGridUrl', $lookupFilterGridUrl);
        }

        // 2. Load Citation Output Filters
        $router = $request->getRouter();
        $context = $router->getContext($request);
        
        if ($context) {
            $filterDao = DAORegistry::getDAO('FilterDAO');
            // Ambil filter nlm30
            $metaCitationOutputFilterObjects = $filterDao->getObjectsByGroup('nlm30-element-citation=>plaintext', $context->getId());
            
            $metaCitationOutputFilters = [];
            foreach($metaCitationOutputFilterObjects as $filterObject) {
                $metaCitationOutputFilters[$filterObject->getId()] = $filterObject->getDisplayName();
            }
            $templateMgr->assign('metaCitationOutputFilters', $metaCitationOutputFilters);
        }

        $templateMgr->assign('ccLicenseOptions', Application::getCCLicenseOptions());

        // [WIZDAM FINAL] Template null agar tidak konflik dengan parent
        $template = null;
        parent::display($request, $template);
    }
}
?>