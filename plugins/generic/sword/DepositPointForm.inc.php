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
 * @brief Form for journal managers to modify SWORD deposit points
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

define('SWORD_PASSWORD_SLUG', '******');

import('lib.pkp.classes.form.Form');

class DepositPointForm extends Form {

    /** @var int */
    public $journalId;

    /** @var int */
    public $depositPointId;

    /** @var object */
    public $plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     * @param int $depositPointId
     */
    public function __construct($plugin, $journalId, $depositPointId) {
        $this->journalId = $journalId;
        $this->depositPointId = $depositPointId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'depositPointForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DepositPointForm($plugin, $journalId, $depositPointId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::DepositPointForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;
        $depositPoints = $plugin->getSetting($journalId, 'depositPoints');
        $depositPoint = null;
        if (isset($depositPoints[$this->depositPointId])) {
            $depositPoint = $depositPoints[$this->depositPointId];
            // Don't echo passwords back to the user.
            $depositPoint['password'] = SWORD_PASSWORD_SLUG;
        }
        $this->setData('depositPoint', $depositPoint);
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(['depositPoint']);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param PKPRequest $request
     * @param string $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('depositPointId', $this->depositPointId);
        $templateMgr->assign('depositPointTypes', $this->plugin->getTypeMap());
        parent::display($request, $template);
    }

    /**
     * Save settings.
     * @see Form::execute()
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;
        $depositPoints = $plugin->getSetting($journalId, 'depositPoints');

        if ($this->depositPointId !== null) {
            $depositPoint = $this->getData('depositPoint');
            if ($depositPoint['password'] == SWORD_PASSWORD_SLUG && isset($depositPoints[$this->depositPointId])) {
                // The old password was not changed; preserve it
                $depositPoint['password'] = $depositPoints[$this->depositPointId]['password'];
            }
            $depositPoints[$this->depositPointId] = $depositPoint;
        }
        else $depositPoints[] = $this->getData('depositPoint');

        $plugin->updateSetting($journalId, 'depositPoints', $depositPoints);
    }
}
?>