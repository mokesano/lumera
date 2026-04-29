<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesSettingsForm
 *
 * Form for journal managers to modify Static Page content and title
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class StaticPagesSettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var object */
    public $plugin;

    /** @var string */
    public $errors;

    /**
     * Constructor
     * @param $plugin object
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        $this->journalId = (int) $journalId;
        $this->plugin = $plugin;

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StaticPagesSettingsForm($plugin, $journalId) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::StaticPagesSettingsForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($plugin, $journalId);
    }

    /**
     * Initialize form data from current group group.
     * @see Form::initData()
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');

        $rangeInfo = Handler::getRangeInfo('staticPages');
        $staticPages = $staticPagesDao->getStaticPagesByJournalId($journalId);
        $this->setData('staticPages', $staticPages);
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(array('pages'));
    }

    /**
     * Save settings/changes
     * @see Form::execute()
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;
    }

}
?>