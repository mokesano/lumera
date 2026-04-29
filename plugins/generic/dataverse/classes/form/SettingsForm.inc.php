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
 * @brief Plugin settings: set data policies, define terms of use, configure workflows. 
 * [WIZDAM EDITION] Modernized for PHP 8.4 with Dependency Injection.
 */

import('lib.pkp.classes.form.Form');
import('plugins.generic.tinymce.TinyMCEPlugin');

class SettingsForm extends Form {

    /** @var int */
    public $_journalId;

    /** @var DataversePlugin */
    public $_plugin;
    
    /** @var array */
    public $_citationFormats;
    
    /** @var array */
    public $_studyReleaseOptions;
    
    /** @var array */
    public $_pubIdTypes;

    /**
     * Constructor. 
     * @param $plugin DataversePlugin
     * @param $journalId int
     * @see Form::Form()
     */
    public function __construct($plugin, $journalId) {
        // [WIZDAM FIX] Force Integer Cast
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;
        
        // Citation formats
        $this->_citationFormats = [
            DATAVERSE_PLUGIN_CITATION_FORMAT_APA => __('plugins.generic.dataverse.settings.citationFormat.apa'),
        ];
        
        // Study release options
        $this->_studyReleaseOptions = [
            DATAVERSE_PLUGIN_RELEASE_ARTICLE_ACCEPTED => __('plugins.generic.dataverse.settings.studyReleaseSubmissionAccepted'),
            DATAVERSE_PLUGIN_RELEASE_ARTICLE_PUBLISHED => __('plugins.generic.dataverse.settings.studyReleaseArticlePublished')
        ];        

        // Public id plugins
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
        // [WIZDAM FIX] Modern Array Callback Syntax
        $this->addCheck(new FormValidatorCustom($this, 'termsOfUse', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.termsOfUseRequired', [$this, '_validateTermsOfUse']));
        $this->addCheck(new FormValidatorCustom($this, 'termsOfUse', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.dataverseTermsOfUseError', [$this, '_validateDataverseTermsOfUse'])); 
        $this->addCheck(new FormValidatorPost($this));        
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $plugin DataversePlugin
     * @param $journalId int
     */
    public function SettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::SettingsForm(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($plugin, $journalId);
    }

    /**
     * Initialize form data from current settings.
     * @see Form::initData()
     */
    public function initData() {
        $plugin = $this->_plugin;
        $journal = Registry::get('request')->getJournal(); // [WIZDAM FIX] No static call

        $this->setData('dataAvailability', 
            $plugin->getSetting($journal->getId(), 'dataAvailability') ?: 
            __('plugins.generic.dataverse.settings.default.dataAvailabilityPolicy', ['journal' => $journal->getLocalizedTitle()])
        );

        $this->setData('fetchTermsOfUse', $plugin->getSetting($journal->getId(), 'fetchTermsOfUse'));
        $this->setData('termsOfUse',      $plugin->getSetting($journal->getId(), 'termsOfUse'));
        $this->setData('requireData',     $plugin->getSetting($journal->getId(), 'requireData'));
        
        $this->setData('citationFormats', $this->_citationFormats);
        $citationFormat = $plugin->getSetting($journal->getId(), 'citationFormat');
        if (isset($citationFormat) && array_key_exists($citationFormat, $this->_citationFormats)) {
            $this->setData('citationFormat', $citationFormat);
        }
        
        $this->setData('pubIdTypes', $this->_pubIdTypes);
        $pubIdPlugin = $plugin->getSetting($journal->getId(), 'pubIdPlugin');
        if (isset($pubIdPlugin) && array_key_exists($pubIdPlugin, $this->_pubIdTypes)) {
            $this->setData('pubIdPlugin', $pubIdPlugin);
        } 

        $this->setData('studyReleaseOptions', $this->_studyReleaseOptions);
        $studyRelease = (int) $plugin->getSetting($journal->getId(), 'studyRelease');
        if (array_key_exists($studyRelease, $this->_studyReleaseOptions)) {
            $this->setData('studyRelease', $studyRelease);
        }
    }

    /**
     * Read user input data.
     * @see Form::readInputData()
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
     * @see Form::fetch()
     */
    public function fetch($request, $template = null, $display = false) {
        if (!$request) $request = Registry::get('request');
        $journal = $request->getJournal();
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections = $sectionDao->getJournalSections($this->_journalId);
        
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('sections', $sections->toArray());
        $templateMgr->assign('citationFormats', $this->_citationFormats);
        $templateMgr->assign('pubIdTypes', $this->_pubIdTypes); 
        $templateMgr->assign('studyReleaseOptions', $this->_studyReleaseOptions);
        
        // [WIZDAM FIX] Modern Array Syntax
        $templateMgr->assign('authorGuidelinesContent', __('plugins.generic.dataverse.settings.default.authorGuidelines', ['journal' => $journal->getLocalizedTitle()]));
        $templateMgr->assign('checklistContent',        __('plugins.generic.dataverse.settings.default.checklist', ['journal' => $journal->getLocalizedTitle()]));          
        $templateMgr->assign('reviewPolicyContent',     __('plugins.generic.dataverse.settings.default.reviewPolicy'));
        $templateMgr->assign('reviewGuidelinesContent', __('plugins.generic.dataverse.settings.default.reviewGuidelines'));          
        $templateMgr->assign('copyeditInstructionsContent', __('plugins.generic.dataverse.settings.default.copyeditInstructions'));
        
        return parent::fetch($request, $template, $display);
    }      

    /**
     * Save settings.
     * @see Form::execute()
     */
    public function execute($object = null) { 
        $plugin = $this->_plugin;

        $plugin->updateSetting($this->_journalId, 'dataAvailability', (string) $this->getData('dataAvailability'), 'string');        
        $plugin->updateSetting($this->_journalId, 'fetchTermsOfUse',  (bool) $this->getData('fetchTermsOfUse'),  'bool');        
        $plugin->updateSetting($this->_journalId, 'termsOfUse',       (string) $this->getData('termsOfUse'),     'string');        
        $plugin->updateSetting($this->_journalId, 'citationFormat',   (string) $this->getData('citationFormat'), 'string');        
        $plugin->updateSetting($this->_journalId, 'pubIdPlugin',      (string) $this->getData('pubIdPlugin'),    'string');        
        $plugin->updateSetting($this->_journalId, 'requireData',      (bool) $this->getData('requireData'),      'bool');        
        $plugin->updateSetting($this->_journalId, 'studyRelease',     (int) $this->getData('studyRelease'),      'int');          
        
        if ($this->getData('dvTermsOfUse')) {
            $plugin->updateSetting($this->_journalId, 'dvTermsOfUse', (string) $this->getData('dvTermsOfUse'), 'string');
        }
    }
    
    /**
     * Validator Terms of Use: either fetch from Dataverse or use custom input 
     * @return boolean 
     */
    public function _validateTermsOfUse() {
        return $this->getData('fetchTermsOfUse') === "1" || $this->getData('termsOfUse');
    }
    
    /**
     * Validator for Dataverse Terms of Use
     * @return boolean
     */
    public function _validateDataverseTermsOfUse() {
        if ($this->getData('fetchTermsOfUse') === "0") return true;

        // [WIZDAM FIX] Inject DataverseApiClient to fetch terms of use
        $this->_plugin->import('classes.api.DataverseApiClient');
        $apiClient = new DataverseApiClient($this->_plugin);
        $dvTermsOfUse = $apiClient->getTermsOfUse($this->_journalId);
        
        if (!$dvTermsOfUse) return false;
        
        $this->setData('dvTermsOfUse', $dvTermsOfUse);
        return true;
    }
}
?>