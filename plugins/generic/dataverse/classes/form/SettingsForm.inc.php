<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/form/SettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup plugins_generic_dataverse
 *
 * @brief Plugin settings form for Dataverse integration.
 */

import('lib.pkp.classes.form.Form');
import('plugins.generic.tinymce.TinyMCEPlugin');

class SettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var DataversePlugin */
    protected $_plugin;
    
    /** @var array */
    protected $_citationFormats;
    
    /** @var array */
    protected $_studyReleaseOptions;
    
    /** @var array */
    protected $_pubIdTypes;

    /**
     * Constructor.
     * @param DataversePlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;
        
        $this->_citationFormats = [
            DATAVERSE_PLUGIN_CITATION_FORMAT_APA => __('plugins.generic.dataverse.settings.citationFormat.apa'),
        ];
        
        $this->_studyReleaseOptions = [
            DATAVERSE_PLUGIN_RELEASE_ARTICLE_ACCEPTED => __('plugins.generic.dataverse.settings.studyReleaseSubmissionAccepted'),
            DATAVERSE_PLUGIN_RELEASE_ARTICLE_PUBLISHED => __('plugins.generic.dataverse.settings.studyReleaseArticlePublished')
        ];        

        $this->_pubIdTypes = [];
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $this->_journalId);
        if (is_array($pubIdPlugins)) {
            foreach ($pubIdPlugins as $pubIdPlugin) {
                $this->_pubIdTypes[$pubIdPlugin->getName()] = $pubIdPlugin->getDisplayName();
            }
        }
        
        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidator($this, 'dataAvailability', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.dataAvailabilityRequired'));
        
        // [LUMERA FIX] Use closures to match PHP 8 FormValidatorCustom signature expectations
        $this->addCheck(new FormValidatorCustom($this, 'termsOfUse', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.termsOfUseRequired', function($value) {
            return $this->_validateTermsOfUse($value);
        }));
        
        $this->addCheck(new FormValidatorCustom($this, 'termsOfUse', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.dataverseTermsOfUseError', function($value) {
            return $this->_validateDataverseTermsOfUse($value);
        }));        
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param DataversePlugin $plugin
     * @param int $journalId
     */
    public function SettingsForm($plugin, $journalId) {
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
     * Initialize form data from current settings.
     * @return void
     */
    public function initData() {
        $plugin = $this->_plugin;
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? $journal->getId() : $this->_journalId;

        $defaultDataAvailability = __('plugins.generic.dataverse.settings.default.dataAvailabilityPolicy', ['journal' => $journal ? $journal->getLocalizedTitle() : '']);
        $this->setData('dataAvailability', $plugin->getSetting($journalId, 'dataAvailability') ?? $defaultDataAvailability);

        $this->setData('fetchTermsOfUse', $plugin->getSetting($journalId, 'fetchTermsOfUse'));
        $this->setData('termsOfUse', $plugin->getSetting($journalId, 'termsOfUse'));
        $this->setData('requireData', $plugin->getSetting($journalId, 'requireData'));
        
        $this->setData('citationFormats', $this->_citationFormats);
        $citationFormat = $plugin->getSetting($journalId, 'citationFormat');
        if ($citationFormat !== null && array_key_exists($citationFormat, $this->_citationFormats)) {
            $this->setData('citationFormat', $citationFormat);
        }
        
        $this->setData('pubIdTypes', $this->_pubIdTypes);
        $pubIdPlugin = $plugin->getSetting($journalId, 'pubIdPlugin');
        if ($pubIdPlugin !== null && array_key_exists($pubIdPlugin, $this->_pubIdTypes)) {
            $this->setData('pubIdPlugin', $pubIdPlugin);
        } 

        $this->setData('studyReleaseOptions', $this->_studyReleaseOptions);
        $studyRelease = (int) ($plugin->getSetting($journalId, 'studyRelease') ?? 0);
        if (array_key_exists($studyRelease, $this->_studyReleaseOptions)) {
            $this->setData('studyRelease', $studyRelease);
        }
    }

    /**
     * Read user input data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'dataAvailability',
            'fetchTermsOfUse',
            'termsOfUse',
            'citationFormat',
            'pubIdPlugin',
            'requireData',
            'studyRelease'
        ]);        
    }
    
    /**
     * Fetch the form.
     * @param object $request
     * @param string|null $template
     * @param bool $display
     * @return string
     */
    public function fetch($request, $template = null, $display = false) {
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections = $sectionDao->getJournalSections($this->_journalId);
        $sectionsArray = $sections ? $sections->toArray() : [];
        
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('sections', $sectionsArray);
        $templateMgr->assign('citationFormats', $this->_citationFormats);
        $templateMgr->assign('pubIdTypes', $this->_pubIdTypes); 
        $templateMgr->assign('studyReleaseOptions', $this->_studyReleaseOptions);
        
        $journalTitle = $journal ? $journal->getLocalizedTitle() : '';
        $templateMgr->assign('authorGuidelinesContent', __('plugins.generic.dataverse.settings.default.authorGuidelines', ['journal' => $journalTitle]));
        $templateMgr->assign('checklistContent', __('plugins.generic.dataverse.settings.default.checklist', ['journal' => $journalTitle]));          
        $templateMgr->assign('reviewPolicyContent', __('plugins.generic.dataverse.settings.default.reviewPolicy'));
        $templateMgr->assign('reviewGuidelinesContent', __('plugins.generic.dataverse.settings.default.reviewGuidelines'));          
        $templateMgr->assign('copyeditInstructionsContent', __('plugins.generic.dataverse.settings.default.copyeditInstructions'));
        
        return parent::fetch($request, $template, $display);
    }      

    /**
     * Save settings.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) { 
        $plugin = $this->_plugin;
        $journalId = $this->_journalId;

        $plugin->updateSetting($journalId, 'dataAvailability', (string) ($this->getData('dataAvailability') ?? ''), 'string');        
        $plugin->updateSetting($journalId, 'fetchTermsOfUse', (bool) $this->getData('fetchTermsOfUse'), 'bool');        
        $plugin->updateSetting($journalId, 'termsOfUse', (string) ($this->getData('termsOfUse') ?? ''), 'string');        
        $plugin->updateSetting($journalId, 'citationFormat', (string) ($this->getData('citationFormat') ?? ''), 'string');        
        $plugin->updateSetting($journalId, 'pubIdPlugin', (string) ($this->getData('pubIdPlugin') ?? ''), 'string');        
        $plugin->updateSetting($journalId, 'requireData', (bool) $this->getData('requireData'), 'bool');        
        $plugin->updateSetting($journalId, 'studyRelease', (int) ($this->getData('studyRelease') ?? 0), 'int');          
        
        $dvTermsOfUse = $this->getData('dvTermsOfUse');
        if ($dvTermsOfUse !== null) {
            $plugin->updateSetting($journalId, 'dvTermsOfUse', (string) $dvTermsOfUse, 'string');
        }
    }
    
    /**
     * Validator Terms of Use.
     * @param mixed $value
     * @return bool
     */
    public function _validateTermsOfUse($value) {
        return $this->getData('fetchTermsOfUse') === "1" || !empty($this->getData('termsOfUse'));
    }
    
    /**
     * Validator for Dataverse Terms of Use.
     * @param mixed $value
     * @return bool
     */
    public function _validateDataverseTermsOfUse($value) {
        if ($this->getData('fetchTermsOfUse') !== "1") {
            return true;
        }

        $this->_plugin->import('classes.api.DataverseApiClient');
        $apiClient = new DataverseApiClient($this->_plugin);

        $dataverseAlias = (string) ($this->_plugin->getSetting($this->_journalId, 'dataverseAlias') ?? '');
        if (empty($dataverseAlias)) {
            return false;
        }
        
        $dvTermsOfUse = $apiClient->getTermsOfUse($this->_journalId, $dataverseAlias);
        
        if (empty($dvTermsOfUse)) {
            return false;
        }
        
        $this->setData('dvTermsOfUse', $dvTermsOfUse);
        return true;
    }
    
}
?>