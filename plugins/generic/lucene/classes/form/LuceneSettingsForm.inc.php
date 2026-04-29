<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/classes/form/LuceneSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LuceneSettingsForm
 * @ingroup plugins_generic_lucene_classes_form
 *
 * @brief Form to configure Lucene/Solr search.
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.form.validation.FormValidatorBoolean');

// These are the first few letters of an md5 of '##placeholder##'.
// FIXME: Any better idea how to prevent a password clash?
define('LUCENE_PLUGIN_PASSWORD_PLACEHOLDER', '##5ca39841ab##');

class LuceneSettingsForm extends Form {

    /** @var LucenePlugin */
    protected $_plugin;

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->_plugin = $plugin;
        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        // Server configuration.
        $this->addCheck(new FormValidatorUrl($this, 'searchEndpoint', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.searchEndpointRequired'));
        // The username is used in HTTP basic authentication and according to RFC2617 it therefore may not contain a colon.
        $this->addCheck(new FormValidatorRegExp($this, 'username', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.usernameRequired', '/^[^:]+$/'));
        $this->addCheck(new FormValidator($this, 'password', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.passwordRequired'));
        $this->addCheck(new FormValidator($this, 'instId', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.instIdRequired'));

        // Search feature configuration.
        $this->addCheck(new FormValidatorInSet($this, 'autosuggestType', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.internalError', array_keys($this->_getAutosuggestTypes())));
        
        $binaryFeatureSwitches = $this->_getFormFields(true);
        foreach($binaryFeatureSwitches as $binaryFeatureSwitch) {
            $this->addCheck(new FormValidatorBoolean($this, $binaryFeatureSwitch, 'plugins.generic.lucene.settings.internalError'));
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LuceneSettingsForm($plugin) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::LuceneSettingsForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }


    //
    // Implement template methods from Form.
    //
    /**
     * Initialize form data from the plugin settings.
     * @see Form::initData()
     */
    public function initData() {
        $plugin = $this->_plugin;
        foreach ($this->_getFormFields() as $fieldName) {
            $this->setData($fieldName, $plugin->getSetting(0, $fieldName));
        }
        // We do not echo back the real password.
        $this->setData('password', LUCENE_PLUGIN_PASSWORD_PLACEHOLDER);
    }

    /**
     * Read user input data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars($this->_getFormFields());
        $request = PKPApplication::getRequest();
        $password = $request->getUserVar('password');
        
        if ($password === LUCENE_PLUGIN_PASSWORD_PLACEHOLDER) {
            $plugin = $this->_plugin;
            $password = $plugin->getSetting(0, 'password');
        }
        $this->setData('password', $password);
    }

    /**
     * Fetch the form template and display it.
     * @see Form::fetch()
     * @param PKPRequest $request
     * @param string $template
     * @param boolean $display
     */
    public function fetch($request, $template = null, $display = false) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('autosuggestTypes', $this->_getAutosuggestTypes());
        parent::fetch($request, $template, $display);
    }

    /**
     * Execute the form: persist the plugin settings.
     * @see Form::execute()
     */
    public function execute($object = null) {
        $plugin = $this->_plugin;
        $formFields = $this->_getFormFields();
        $formFields[] = 'password';
        foreach($formFields as $formField) {
            $plugin->updateSetting(0, $formField, $this->getData($formField), 'string');
        }
    }


    //
    // Private helper methods
    //
    /**
     * Return the field names of this form.
     * @param boolean $booleanOnly Return only binary switches.
     * @return array
     */
    protected function _getFormFields($booleanOnly = false) {
        $booleanFormFields = [
            'autosuggest', 'spellcheck', 'pullIndexing',
            'simdocs', 'highlighting', 'facetCategoryDiscipline',
            'facetCategorySubject', 'facetCategoryType',
            'facetCategoryCoverage', 'facetCategoryJournalTitle',
            'facetCategoryAuthors', 'facetCategoryPublicationDate',
            'customRanking', 'useProxySettings'
        ];
        $otherFormFields = [
            'searchEndpoint', 'username', 'instId',
            'autosuggestType'
        ];
        
        if ($booleanOnly) {
            return $booleanFormFields;
        } else {
            return array_merge($booleanFormFields, $otherFormFields);
        }
    }

    /**
     * Return a list of auto-suggest types.
     * @return array
     */
    protected function _getAutosuggestTypes() {
        return [
            SOLR_AUTOSUGGEST_SUGGESTER => __('plugins.generic.lucene.settings.autosuggestTypeSuggester'),
            SOLR_AUTOSUGGEST_FACETING => __('plugins.generic.lucene.settings.autosuggestTypeFaceting')
        ];
    }
}
?>