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
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.form.validation.FormValidatorBoolean');

define('LUCENE_PLUGIN_PASSWORD_PLACEHOLDER', '##5ca39841ab##');

class LuceneSettingsForm extends Form {

    /** @var LucenePlugin */
    protected $_plugin;

    /**
     * Constructor.
     * @param LucenePlugin $plugin
     */
    public function __construct($plugin) {
        $this->_plugin = $plugin;
        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        $this->addCheck(new FormValidatorUrl($this, 'searchEndpoint', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.searchEndpointRequired'));
        $this->addCheck(new FormValidatorRegExp($this, 'username', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.usernameRequired', '/^[^:]+$/'));
        $this->addCheck(new FormValidator($this, 'password', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.passwordRequired'));
        $this->addCheck(new FormValidator($this, 'instId', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.instIdRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'autosuggestType', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.lucene.settings.internalError', array_keys($this->_getAutosuggestTypes())));
        
        $binaryFeatureSwitches = $this->_getFormFields(true);
        foreach ($binaryFeatureSwitches as $binaryFeatureSwitch) {
            $this->addCheck(new FormValidatorBoolean($this, $binaryFeatureSwitch, 'plugins.generic.lucene.settings.internalError'));
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param LucenePlugin $plugin
     */
    public function LuceneSettingsForm($plugin) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Initialize form data.
     * @return void
     * @see Form::initData()
     */
    public function initData() {
        $plugin = $this->_plugin;
        foreach ($this->_getFormFields() as $fieldName) {
            $this->setData($fieldName, $plugin->getSetting(0, $fieldName));
        }
        $this->setData('password', LUCENE_PLUGIN_PASSWORD_PLACEHOLDER);
    }

    /**
     * Read user input data.
     * @return void
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars($this->_getFormFields());
        $request = PKPApplication::getRequest();
        $password = (string) $request->getUserVar('password');
        
        if ($password === LUCENE_PLUGIN_PASSWORD_PLACEHOLDER) {
            $password = (string) $this->_plugin->getSetting(0, 'password');
        }
        $this->setData('password', $password);
    }

    /**
     * Fetch the form template.
     * @param PKPRequest $request
     * @param string|null $template
     * @param bool $display
     * @return string
     * @see Form::fetch()
     */
    public function fetch($request, $template = null, $display = false) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('autosuggestTypes', $this->_getAutosuggestTypes());
        return parent::fetch($request, $template, $display);
    }

    /**
     * Execute the form.
     * @param mixed $object
     * @return void
     * @see Form::execute()
     */
    public function execute($object = null) {
        $plugin = $this->_plugin;
        $formFields = $this->_getFormFields();
        $formFields[] = 'password';
        foreach ($formFields as $formField) {
            $plugin->updateSetting(0, $formField, $this->getData($formField), 'string');
        }
    }

    /**
     * Get form field names.
     * @param bool $booleanOnly
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
        
        return $booleanOnly ? $booleanFormFields : array_merge($booleanFormFields, $otherFormFields);
    }

    /**
     * Get auto-suggest types.
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