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
 * @brief Form for journal managers to modify External Feed plugin settings
 * * MODERNIZED FOR PHP 7.4+ & OJS FORK
 * - Implemented __construct.
 * - Removed obsolete reference operators (&).
 * - Redirected template to 'templates/' folder.
 * - Cleaned up file upload naming logic.
 */

import('lib.pkp.classes.form.Form');

class ExternalFeedSettingsForm extends Form {

    /** @var $journalId int */
    public $journalId;

    /** @var $plugin object */
    public $plugin;

    /**
     * Constructor
     * @param $plugin object
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        // UPDATE PATH: Arahkan ke folder templates/
        parent::__construct($plugin->getTemplatePath() . 'templates/settingsForm.tpl');

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->_data = array(
            'externalFeedStyleSheet' => $plugin->getSetting($journalId, 'externalFeedStyleSheet')
        );
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('externalFeedStyleSheet'));
    }

    /**
     * Display the form.
     */
    public function display($request = NULL, $template = NULL) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign(
            'journalStyleSheet', 
            $this->plugin->getSetting($this->journalId, 'externalFeedStyleSheet')
        );
        $templateMgr->assign(
            'defaultStyleSheetUrl', 
            Request::getBaseUrl() . '/' . $this->plugin->getDefaultStyleSheetFile()
        );
    
        // FIX: Teruskan parameter ke parent
        parent::display($request, $template);
    }

    /**
     * Uploads custom stylesheet.
     */
    public function uploadStyleSheet() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;
        $settingName = 'externalFeedStyleSheet';

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();

        if ($fileManager->uploadedFileExists($settingName)) {
            $type = $fileManager->getUploadedFileType($settingName);
            if ($type != 'text/plain' && $type != 'text/css') {
                return false;
            }

            // FIX: Gunakan nama file yang sederhana. 
            // UploadJournalFile otomatis menaruhnya di public/journals/{id}/
            // Tidak perlu memasukkan plugin path ke dalam nama file upload.
            $uploadName = $settingName . '.css';
            
            if($fileManager->uploadJournalFile($journalId, $settingName, $uploadName)) {            
                $value = array(
                    'name' => $fileManager->getUploadedFileName($settingName),
                    'uploadName' => $uploadName,
                    'dateUploaded' => Core::getCurrentDate()
                );

                $plugin->updateSetting($journalId, $settingName, $value, 'object');
                return true;
            }
        }

        return false;
    }

    /**
     * Deletes a custom stylesheet.
     */
    public function deleteStyleSheet() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;
        $settingName = 'externalFeedStyleSheet';

        $setting = $plugin->getSetting($journalId, $settingName);

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();

        if ($fileManager->removeJournalFile($journalId, $setting['uploadName'])) {
            $plugin->updateSetting($journalId, $settingName, null);
            return true;
        } else {
            return false;
        }
    }
}

?>