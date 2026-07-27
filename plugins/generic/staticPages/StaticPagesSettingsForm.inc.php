<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesSettingsForm
 * @ingroup plugins_generic_staticPages
 *
 * @brief Form for journal managers to modify Static Page content and title.
 */

import('lib.pkp.classes.form.Form');

class StaticPagesSettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var GenericPlugin */
    protected $_plugin;

    /** @var string */
    public $_errors;

    /**
     * Constructor.
     * @param GenericPlugin $plugin
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
     * @param GenericPlugin $plugin
     * @param int $journalId
     */
    public function StaticPagesSettingsForm($plugin, $journalId) {
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
        /** @var StaticPagesDAO $staticPagesDao */
        $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');

        $rangeInfo = Handler::getRangeInfo('staticPages');
        $staticPages = $staticPagesDao->getStaticPagesByJournalId($this->_journalId, $rangeInfo);
        $this->setData('staticPages', $staticPages);
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['pages']);
    }

    /**
     * Save settings/changes.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        // Logic is intentionally empty in the original code. Preserved to maintain signature.
    }

}
?>