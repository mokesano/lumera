<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sword/DepositPointForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DepositPointForm
 * @ingroup plugins_generic_sword
 *
 * @brief Form for journal managers to modify SWORD deposit points.
 */

define('SWORD_PASSWORD_SLUG', '******');

import('lib.pkp.classes.form.Form');

class DepositPointForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var int|null */
    protected $_depositPointId;

    /** @var SwordPlugin */
    protected $_plugin;

    /**
     * Constructor
     * @param SwordPlugin $plugin
     * @param int $journalId
     * @param int|null $depositPointId
     */
    public function __construct($plugin, $journalId, $depositPointId) {
        $this->_journalId = (int) $journalId;
        $this->_depositPointId = $depositPointId !== null ? (int) $depositPointId : null;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'depositPointForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param SwordPlugin $plugin
     * @param int $journalId
     * @param int|null $depositPointId
     */
    public function DepositPointForm($plugin, $journalId, $depositPointId) {
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
        $depositPoints = $this->_plugin->getSetting($this->_journalId, 'depositPoints');
        $depositPoint = null;
        
        if (is_array($depositPoints) && isset($depositPoints[$this->_depositPointId])) {
            $depositPoint = $depositPoints[$this->_depositPointId];
            // Don't echo passwords back to the user.
            $depositPoint['password'] = SWORD_PASSWORD_SLUG;
        }
        $this->setData('depositPoint', $depositPoint);
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['depositPoint']);
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
        $templateMgr->assign('depositPointId', $this->_depositPointId);
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
        $depositPoints = $this->_plugin->getSetting($this->_journalId, 'depositPoints');
        if (!is_array($depositPoints)) {
            $depositPoints = [];
        }

        $depositPointData = $this->getData('depositPoint');

        if ($this->_depositPointId !== null && isset($depositPoints[$this->_depositPointId])) {
            if ($depositPointData['password'] === SWORD_PASSWORD_SLUG) {
                // The old password was not changed; preserve it.
                $depositPointData['password'] = $depositPoints[$this->_depositPointId]['password'];
            }
            $depositPoints[$this->_depositPointId] = $depositPointData;
        } else {
            $depositPoints[] = $depositPointData;
        }

        $this->_plugin->updateSetting($this->_journalId, 'depositPoints', $depositPoints);
    }

}
?>