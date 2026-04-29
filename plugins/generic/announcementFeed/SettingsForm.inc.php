<?php
declare(strict_types=1);

/**
 * @file plugins/generic/announcementFeed/SettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup plugins_generic_annoucementFeed
 *
 * @brief Form for journal managers to modify announcement feed plugin settings
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

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

		parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	public function initData() {
		$journalId = $this->journalId;
		$plugin = $this->plugin;

		$this->setData('displayPage', $plugin->getSetting($journalId, 'displayPage'));
		$this->setData('limitRecentItems', $plugin->getSetting($journalId, 'limitRecentItems'));
		$this->setData('recentItems', $plugin->getSetting($journalId, 'recentItems'));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	public function readInputData() {
		$this->readUserVars(array('displayPage','limitRecentItems','recentItems'));

		// check that recent items value is a positive integer
		if ((int) $this->getData('recentItems') <= 0) $this->setData('recentItems', '');
	}

	/**
	 * Save settings. 
	 */
	public function execute($object = null) {
		$plugin = $this->plugin;
		$journalId = $this->journalId;

		$plugin->updateSetting($journalId, 'displayPage', $this->getData('displayPage'));
		$plugin->updateSetting($journalId, 'limitRecentItems', $this->getData('limitRecentItems') ? $this->getData('limitRecentItems') : 0);
		$plugin->updateSetting($journalId, 'recentItems', $this->getData('recentItems'));
	}

}

?>