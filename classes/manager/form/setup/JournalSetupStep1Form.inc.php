<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/setup/JournalSetupStep1Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSetupStep1Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 1 of journal setup.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.manager.form.setup.JournalSetupForm');

class JournalSetupStep1Form extends JournalSetupForm {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            1,
            [
                'title' => 'string',
                'initials' => 'string',
                'abbreviation' => 'string',
                'printIssn' => 'string',
                'onlineIssn' => 'string',
                'mailingAddress' => 'string',
                'categories' => 'object',
                'useEditorialBoard' => 'bool',
                'contactName' => 'string',
                'contactTitle' => 'string',
                'contactAffiliation' => 'string',
                'contactEmail' => 'string',
                'contactPhone' => 'string',
                'contactFax' => 'string',
                'contactMailingAddress' => 'string',
                'supportName' => 'string',
                'supportEmail' => 'string',
                'supportPhone' => 'string',
                'sponsorNote' => 'string',
                'sponsors' => 'object',
                'publisherInstitution' => 'string',
                'publisherUrl' => 'string',
                'publisherNote' => 'string',
                'contributorNote' => 'string',
                'contributors' => 'object',
                'history' => 'string',
                'envelopeSender' => 'string',
                'emailSignature' => 'string',
                'searchDescription' => 'string',
                'searchKeywords' => 'string',
                'customHeaders' => 'string'
            ]
        );

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.setup.form.journalTitleRequired'));
        $this->addCheck(new FormValidatorLocale($this, 'initials', 'required', 'manager.setup.form.journalInitialsRequired'));
        $this->addCheck(new FormValidator($this, 'contactName', 'required', 'manager.setup.form.contactNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'contactEmail', 'required', 'manager.setup.form.contactEmailRequired'));
        $this->addCheck(new FormValidator($this, 'supportName', 'required', 'manager.setup.form.supportNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'supportEmail', 'required', 'manager.setup.form.supportEmailRequired'));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSetupStep1Form() {
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
            'title', 
            'initials', 
            'abbreviation', 
            'contactTitle', 
            'contactAffiliation', 
            'contactMailingAddress', 
            'sponsorNote', 
            'publisherNote', 
            'contributorNote', 
            'history', 
            'searchDescription', 
            'searchKeywords', 
            'customHeaders'
        ];
    }

    /**
     * Execute the form, but first:
     * Make sure we're not saving an empty entry for sponsors. (This would
     * result in a possibly empty heading for the Sponsors section in About
     * the Journal.)
     * @param object|null $object
     */
    public function execute($object = null) {
        foreach (['sponsors', 'contributors'] as $element) {
            $elementValue = (array) $this->getData($element);
            foreach (array_keys($elementValue) as $key) {
                $values = array_values((array) $elementValue[$key]);
                $isEmpty = true;
                foreach ($values as $value) {
                    if (!empty($value)) $isEmpty = false;
                }
                if ($isEmpty) unset($elementValue[$key]);
            }
            $this->setData($element, $elementValue);
        }

        // In case the category list changed, flush the cache.
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $categoryDao->rebuildCache();

        return parent::execute($object);
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();
        
        $templateMgr = TemplateManager::getManager();
        if (Config::getVar('email', 'allow_envelope_sender'))
            $templateMgr->assign('envelopeSenderEnabled', true);

        // If Categories are enabled by Site Admin, make selection
        // tools available to Journal Manager
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $categories = $categoryDao->getCategories();
        $site = $request->getSite();
        
        if ($site->getSetting('categoriesEnabled') && !empty($categories)) {
            $templateMgr->assign('categoriesEnabled', true);
            $templateMgr->assign('allCategories', $categories);
        }

        parent::display($request, $template);
    }
}
?>