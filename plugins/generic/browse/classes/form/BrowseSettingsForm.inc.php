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
 * @brief Form for journal managers to setup browse plugin
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('lib.pkp.classes.form.Form');

class BrowseSettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var object */
    public $plugin;

    /**
     * Constructor
     * @param $plugin object
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;
        
        // [MODERNISASI] Parent Construct
        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BrowseSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BrowseSettingsForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($plugin, $journalId);
    }
    
    /**
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $sectionDao = DAORegistry::getDAO('SectionDAO'); 
        $sectionsResultFactory = $sectionDao->getJournalSections($journalId);
        $sections = array();
        $identifyTypes = array();
        
        while ($section = $sectionsResultFactory->next()) {
            // consider all section titles
            $sections[$section->getId()] = $section->getLocalizedTitle();
            
            // several sections could have the same identify type => don't duplicate
            // and leave out the empty identify types
            $identifyType = $section->getLocalizedIdentifyType();
            if (!in_array($identifyType, $identifyTypes) && $identifyType != '') {
                // Key array menggunakan ID section, tapi value adalah Type-nya
                $identifyTypes[$section->getId()] = $identifyType;
            }
            unset($section);
        }
                
        asort($identifyTypes);
        
        $this->_data = array(
            'enableBrowseBySections' => $plugin->getSetting($journalId, 'enableBrowseBySections'),
            'enableBrowseByIdentifyTypes' => $plugin->getSetting($journalId, 'enableBrowseByIdentifyTypes'),
            'excludedSections' => $plugin->getSetting($journalId, 'excludedSections'),
            'excludedIdentifyTypes' => $plugin->getSetting($journalId, 'excludedIdentifyTypes'),
            'sections' => $sections,
            'identifyTypes' => $identifyTypes
        );
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('enableBrowseBySections', 'enableBrowseByIdentifyTypes', 'excludedSections', 'excludedIdentifyTypes'));
    }

    /**
     * Save settings.
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;
        
        $plugin->updateSetting($journalId, 'enableBrowseBySections', $this->getData('enableBrowseBySections'), 'bool');
        $plugin->updateSetting($journalId, 'enableBrowseByIdentifyTypes', $this->getData('enableBrowseByIdentifyTypes'), 'bool');
        
        // [MODERNISASI] Pastikan array valid
        $excludedSections = $this->getData('excludedSections');
        $plugin->updateSetting($journalId, 'excludedSections', is_array($excludedSections) ? $excludedSections : array(), 'object');
        
        // [FIX] Cast ke array untuk mencegah error in_array jika null
        $excludedIdentifyTypesData = (array) $this->getData('excludedIdentifyTypes');
        
        $excludedIdentifyTypes = array();
        $sectionDao = DAORegistry::getDAO('SectionDAO'); 
        $sectionsResultFactory = $sectionDao->getJournalSections($journalId);
        
        // consider all sections for exclusion with an excluded identify type 
        while ($section = $sectionsResultFactory->next()) {
            $identifyType = $section->getLocalizedIdentifyType();
            if ($identifyType != '' && in_array($identifyType, $excludedIdentifyTypesData)) {
                $excludedIdentifyTypes[] = $section->getId();
            }
        }
        
        $plugin->updateSetting($journalId, 'excludedIdentifyTypes', $excludedIdentifyTypes, 'object');
    }
}

?>