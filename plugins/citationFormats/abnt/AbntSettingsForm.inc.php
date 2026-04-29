<?php
declare(strict_types=1);

/**
 * @file AbntSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Contributed by Lepidus Tecnologia
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AbntSettingsForm
 * @ingroup plugins_citationFormats_abnt
 *
 * @brief Form for journal managers to modify ABNT Citation plugin settings
 */

import('lib.pkp.classes.form.Form');

class AbntSettingsForm extends Form {

    /** @var int */
    public int $journalId;

    /** @var object */
    public object $plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, int $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AbntSettingsForm($plugin, $journalId) {
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
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->_data = [
            'location' => $plugin->getSetting($journalId, 'location')
        ];
    }

    /**
     * Get the list of field names for which localized settings are used.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['location'];
    }

    /**
     * Display the form. (DITAMBAHKAN UNTUK MEMPERBAIKI CANCEL)
     * [WIZDAM FIX] Overrides Form::display untuk menginjeksi URL Cancel yang benar.
     */
    public function display($request = NULL, $template = NULL) {
        // [MASALAH 2: TOMBOL CANCEL TIDAK BERFUNGSI]
        // Kita harus menghitung URL Cancel/Back secara manual dan menyuntikkannya ke TemplateManager.
        $router = Request::getRouter();
        $journal = Request::getJournal();
        
        // Target kembali (Citation Format Plugins)
        $cancelUrl = Request::url(
            $journal->getPath(), 
            'manager', 
            'plugins', 
            null, 
            ['category' => 'citationFormats', 'verb' => 'settings']
        );
        
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageUrl', $cancelUrl); // Beberapa template menggunakan pageUrl
        $templateMgr->assign('cancelUrl', $cancelUrl);

        parent::display($template);
    }
    
    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['location']);
    }

    /**
     * Save settings.
     */
    public function execute($object = NULL) {
        $journal = Request::getJournal();
        $journalId = $journal ? $journal->getId() : $this->journalId;
        $plugin = $this->plugin;

        $value = $this->getData('location');
        if (is_array($value)) {
            $plugin->updateSetting($journalId, 'location', $value, 'object');
        }

        // Modern UX: Send notification confirming save
        import('classes.notification.NotificationManager');
        $notificationMgr = new NotificationManager();
        $user = Request::getUser();
        if ($user) {
            $notificationMgr->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('common.changesSaved')]
            );
        }
        
        $url = Request::url(
            $journal->getPath(), 
            'manager', 
            'plugins', 
            null, 
            ['category' => 'citationFormats', 'verb' => 'settings'] // Target halaman plugin list
        );
        
        Request::redirectUrl($url);
    }
}

?>