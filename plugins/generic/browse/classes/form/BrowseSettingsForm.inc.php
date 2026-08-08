<?php
declare(strict_types=1);

/**
 * @file plugins/generic/browse/BrowseSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BrowseSettingsForm
 * @ingroup plugins_generic_browse
 *
 * @brief Form for journal managers to setup browse plugin.
 */

import('lib.pkp.classes.form.Form');

class BrowseSettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var object */
    protected $_plugin;

    /**
     * Constructor.
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;
        
        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param object $plugin
     * @param int $journalId
     */
    public function BrowseSettingsForm($plugin, $journalId) {
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
     */
    public function initData() {
        $journalId = $this->_journalId;
        $plugin = $this->_plugin;

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO'); 
        $sectionsResultFactory = $sectionDao->getJournalSections($journalId);
        $sections = [];
        $identifyTypes = [];
        
        if ($sectionsResultFactory) {
            while ($section = $sectionsResultFactory->next()) {
                $sections[(int) $section->getId()] = (string) $section->getLocalizedTitle();
                $identifyType = (string) $section->getLocalizedIdentifyType();

                if ($identifyType !== '' && !in_array($identifyType, $identifyTypes, true)) {
                    $identifyTypes[(int) $section->getId()] = $identifyType;
                }
            }
        }
                
        asort($identifyTypes);
        
        $excludedSections = $plugin->getSetting($journalId, 'excludedSections');
        $excludedIdentifyTypes = $plugin->getSetting($journalId, 'excludedIdentifyTypes');

        $this->_data = [
            'enableBrowseBySections' => (bool) $plugin->getSetting($journalId, 'enableBrowseBySections'),
            'enableBrowseByIdentifyTypes' => (bool) $plugin->getSetting($journalId, 'enableBrowseByIdentifyTypes'),
            'excludedSections' => is_array($excludedSections) ? $excludedSections : [],
            'excludedIdentifyTypes' => is_array($excludedIdentifyTypes) ? $excludedIdentifyTypes : [],
            'sections' => $sections,
            'identifyTypes' => $identifyTypes
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'enableBrowseBySections', 
            'enableBrowseByIdentifyTypes', 
            'excludedSections', 
            'excludedIdentifyTypes'
        ]);
    }

    /**
     * Save settings.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $plugin = $this->_plugin;
        $journalId = $this->_journalId;
        
        $plugin->updateSetting($journalId, 'enableBrowseBySections', (bool) $this->getData('enableBrowseBySections'), 'bool');
        $plugin->updateSetting($journalId, 'enableBrowseByIdentifyTypes', (bool) $this->getData('enableBrowseByIdentifyTypes'), 'bool');

        $excludedSections = $this->getData('excludedSections');
        $plugin->updateSetting($journalId, 'excludedSections', is_array($excludedSections) ? $excludedSections : [], 'object');

        $excludedIdentifyTypesData = $this->getData('excludedIdentifyTypes');
        $excludedIdentifyTypesArray = is_array($excludedIdentifyTypesData) ? $excludedIdentifyTypesData : [];
        
        $excludedIdentifyTypes = [];
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO'); 
        $sectionsResultFactory = $sectionDao->getJournalSections($journalId);
        
        if ($sectionsResultFactory) {
            while ($section = $sectionsResultFactory->next()) {
                $identifyType = (string) $section->getLocalizedIdentifyType();
                if ($identifyType !== '' && in_array($identifyType, $excludedIdentifyTypesArray, true)) {
                    $excludedIdentifyTypes[] = (int) $section->getId();
                }
            }
        }
        
        $plugin->updateSetting($journalId, 'excludedIdentifyTypes', $excludedIdentifyTypes, 'object');
    }

}
?>