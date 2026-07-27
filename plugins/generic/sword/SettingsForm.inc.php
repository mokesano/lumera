<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sword/SettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup plugins_generic_sword
 *
 * @brief Form for journal managers to modify SWORD plugin settings.
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var SwordPlugin */
    protected $_plugin;

    /**
     * Constructor
     * @param SwordPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param SwordPlugin $plugin
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
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        $this->setData('allowAuthorSpecify', $this->_plugin->getSetting($this->_journalId, 'allowAuthorSpecify'));
        $this->setData('depositPoints', $this->_plugin->getSetting($this->_journalId, 'depositPoints'));
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['allowAuthorSpecify']);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param PKPRequest|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('depositPointTypes', $this->_plugin->getTypeMap());
        parent::display($request, $template);
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $this->_plugin->updateSetting($this->_journalId, 'allowAuthorSpecify', $this->getData('allowAuthorSpecify'));
    }

}
?>