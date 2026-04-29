<?php
declare(strict_types=1);

/**
 * @defgroup security_form
 */

/**
 * @file classes/security/form/AuthSourceSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthSourceSettingsForm
 * @ingroup security_form
 * @see AuthSource, AuthSourceDAO
 *
 * @brief Form for editing authentication source settings.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class AuthSourceSettingsForm extends Form {

    /** @var int The ID of the source being edited */
    public $authId;

    /** @var object The associated plugin */
    public $plugin;

    /**
     * Constructor.
     * @param $authId int
     */
    public function __construct($authId) {
        parent::__construct('admin/auth/sourceSettings.tpl');
        $this->addCheck(new FormValidatorPost($this));
        $this->authId = $authId;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthSourceSettingsForm($authId) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AuthSourceSettingsForm(). Please refactor to use parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct($authId);
    }

    /**
     * Display the form.
     */
    public function display() {
        // [MODERNISASI] Hapus tanda &
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('authId', $this->authId);
        $templateMgr->assign('helpTopicId', 'site.siteManagement');

        if (isset($this->plugin)) {
            $this->plugin->addLocaleData();
            $templateMgr->assign('pluginTemplate', $this->plugin->getSettingsTemplate());
        }

        parent::display();
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        // [MODERNISASI] Hapus tanda &
        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $auth = $authDao->getSource($this->authId);

        if ($auth != null) {
            $this->_data = array(
                'plugin' => $auth->getPlugin(),
                'title' => $auth->getTitle(),
                'settings' => $auth->getSettings()
            );
            // [MODERNISASI] Hapus tanda &
            $this->plugin = $auth->getPluginClass();
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('title', 'settings'));
    }

    /**
     * Save journal settings.
     */
    public function execute() {
        // [MODERNISASI] Hapus tanda &
        $authDao = DAORegistry::getDAO('AuthSourceDAO');

        $auth = $authDao->newDataObject();
        $auth->setAuthId($this->authId);
        $auth->setTitle($this->getData('title'));
        $auth->setSettings($this->getData('settings'));

        $authDao->updateObject($auth);
    }
}

?>