<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/ExternalFeedSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExternalFeedSettingsForm
 * @ingroup plugins_generic_externalFeed
 *
 * @brief Form for journal managers to modify External Feed plugin settings.
 */

import('lib.pkp.classes.form.Form');

class ExternalFeedSettingsForm extends Form {

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

        parent::__construct($plugin->getTemplatePath() . 'templates/settingsForm.tpl');

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param object $plugin
     * @param int $journalId
     */
    public function ExternalFeedSettingsForm($plugin, $journalId) {
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
        $this->_data = [
            'externalFeedStyleSheet' => $this->_plugin->getSetting($this->_journalId, 'externalFeedStyleSheet')
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['externalFeedStyleSheet']);
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('journalStyleSheet', $this->_plugin->getSetting($this->_journalId, 'externalFeedStyleSheet'));
        $templateMgr->assign('defaultStyleSheetUrl', Application::get()->getRequest()->getBaseUrl() . '/' . $this->_plugin->getDefaultStyleSheetFile());
    
        parent::display($request, $template);
    }

    /**
     * Uploads custom stylesheet.
     * @return bool
     */
    public function uploadStyleSheet() {
        $settingName = 'externalFeedStyleSheet';

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();

        if ($fileManager->uploadedFileExists($settingName)) {
            $type = $fileManager->getUploadedFileType($settingName);
            if ($type !== 'text/plain' && $type !== 'text/css') {
                return false;
            }

            $uploadName = $settingName . '.css';
            
            if ($fileManager->uploadJournalFile($this->_journalId, $settingName, $uploadName)) {            
                $value = [
                    'name' => (string) $fileManager->getUploadedFileName($settingName),
                    'uploadName' => $uploadName,
                    'dateUploaded' => Core::getCurrentDate()
                ];

                $this->_plugin->updateSetting($this->_journalId, $settingName, $value, 'object');
                return true;
            }
        }

        return false;
    }

    /**
     * Deletes a custom stylesheet.
     * @return bool
     */
    public function deleteStyleSheet() {
        $settingName = 'externalFeedStyleSheet';
        $setting = $this->_plugin->getSetting($this->_journalId, $settingName);

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();

        if (is_array($setting) && isset($setting['uploadName'])) {
            if ($fileManager->removeJournalFile($this->_journalId, (string) $setting['uploadName'])) {
                $this->_plugin->updateSetting($this->_journalId, $settingName, null);
                return true;
            }
        }
        
        return false;
    }
    
}
?>