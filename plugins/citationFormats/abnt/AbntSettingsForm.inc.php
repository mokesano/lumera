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
    protected $_journalId;

    /** @var object */
    protected $_plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * [SHIM] Backward Compatibility
     * @param object $plugin
     * @param int $journalId
     */
    public function AbntSettingsForm($plugin, $journalId) {
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
     */
    public function initData() {
        $this->_data = [
            'location' => $this->_plugin->getSetting($this->_journalId, 'location')
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
     * Display the form.
     * @param mixed $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        $cancelUrl = $request->url(
            $journal ? $journal->getPath() : 'index', 
            'manager', 
            'plugins', 
            null, 
            ['category' => 'citationFormats', 'verb' => 'settings']
        );
        
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageUrl', $cancelUrl);
        $templateMgr->assign('cancelUrl', $cancelUrl);

        parent::display($request, $template);
    }
    
    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['location']);
    }

    /**
     * Save settings.
     * @param mixed $object
     */
    public function execute($object = null) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : $this->_journalId;
        $plugin = $this->_plugin;

        $value = $this->getData('location');
        if (is_array($value)) {
            $plugin->updateSetting($journalId, 'location', $value, 'object');
        }

        import('classes.notification.NotificationManager');
        $notificationMgr = new NotificationManager();
        $user = $request->getUser();
        
        if ($user) {
            $notificationMgr->createTrivialNotification(
                (int) $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('common.changesSaved')]
            );
        }
        
        $url = $request->url(
            $journal ? $journal->getPath() : 'index', 
            'manager', 
            'plugins', 
            null, 
            ['category' => 'citationFormats', 'verb' => 'settings']
        );
        
        $request->redirectUrl($url);
    }

}
?>